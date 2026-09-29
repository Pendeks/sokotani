<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use App\Models\Product;
use App\Notifications\OfferActivityNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OfferController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $offers = Offer::query()
            ->with(['product.farmer', 'buyer', 'negotiations.user'])
            ->when($user->role === 'farmer', fn ($query) => $query->whereHas('product', fn ($products) => $products->where('farmer_id', $user->id)))
            ->when($user->role === 'buyer', fn ($query) => $query->where('buyer_id', $user->id))
            ->latest('updated_at')
            ->paginate(15);

        return view('offers.index', compact('offers'));
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'unit_price' => ['required', 'integer', 'min:1', 'max:1000000000000'],
            'fulfillment_method' => ['required', 'in:pickup,delivery'],
            'fulfillment_notes' => ['nullable', 'string', 'max:1000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $offer = DB::transaction(function () use ($validated, $product) {
            $lockedProduct = Product::query()->lockForUpdate()->findOrFail($product->id);

            abort_unless($lockedProduct->is_active && (float) $lockedProduct->available_quantity > 0, 422, 'Produk sudah tidak tersedia.');
            abort_if($lockedProduct->farmer_id === auth()->id(), 403, 'Petani tidak dapat mengajukan penawaran untuk produknya sendiri.');

            if ((float) $validated['quantity'] < (float) $lockedProduct->minimum_quantity) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah harus memenuhi minimum pembelian grosir.']);
            }
            if ((float) $validated['quantity'] > (float) $lockedProduct->available_quantity) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi stok yang tersedia.']);
            }

            $offer = Offer::create([
                'product_id' => $lockedProduct->id,
                'buyer_id' => auth()->id(),
                'quantity' => $validated['quantity'],
                'current_unit_price' => $validated['unit_price'],
                'status' => 'pending',
                'fulfillment_method' => $validated['fulfillment_method'],
                'fulfillment_notes' => $validated['fulfillment_notes'] ?? null,
            ]);

            $offer->negotiations()->create([
                'user_id' => auth()->id(),
                'action' => 'buyer_offer',
                'unit_price' => $validated['unit_price'],
                'message' => $validated['message'] ?? null,
            ]);

            $lockedProduct->farmer->notify(new OfferActivityNotification(
                'Penawaran baru',
                auth()->user()->name.' mengajukan pembelian '.rtrim(rtrim($offer->quantity, '0'), '.').' '.$lockedProduct->unit.'.',
                $offer->id,
                $lockedProduct->name
            ));

            return $offer;
        });

        return redirect()->route('offers.index')->with('status', 'Penawaran terkirim. Petani dapat menerima, menolak, atau mengajukan harga balasan.');
    }

    public function farmerRespond(Request $request, Offer $offer): RedirectResponse
    {
        $this->ensureFarmerOwnsOffer($offer);
        $validated = $request->validate([
            'action' => ['required', 'in:accept,reject,counter'],
            'unit_price' => ['required_if:action,counter', 'nullable', 'integer', 'min:1', 'max:1000000000000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($offer, $validated) {
            $lockedOffer = Offer::query()->lockForUpdate()->findOrFail($offer->id);
            if ($lockedOffer->status !== 'pending') {
                throw ValidationException::withMessages(['action' => 'Penawaran ini sudah ditanggapi atau tidak lagi aktif.']);
            }

            if ($validated['action'] === 'counter') {
                $lockedOffer->update(['current_unit_price' => $validated['unit_price'], 'status' => 'countered']);
                $this->recordNegotiation($lockedOffer, 'farmer_counter', $validated['unit_price'], $validated['message'] ?? null);
                $lockedOffer->buyer->notify(new OfferActivityNotification(
                    'Petani mengajukan harga balasan',
                    $lockedOffer->product->farmer->name.' mengajukan Rp'.number_format($validated['unit_price'], 0, ',', '.').' per '.$lockedOffer->product->unit.'.',
                    $lockedOffer->id,
                    $lockedOffer->product->name
                ));
                return;
            }

            if ($validated['action'] === 'reject') {
                $lockedOffer->update(['status' => 'rejected']);
                $this->recordNegotiation($lockedOffer, 'farmer_rejected', null, $validated['message'] ?? null);
                $lockedOffer->buyer->notify(new OfferActivityNotification(
                    'Penawaran ditolak',
                    $lockedOffer->product->farmer->name.' menolak penawaran untuk '.$lockedOffer->product->name.'.',
                    $lockedOffer->id,
                    $lockedOffer->product->name
                ));
                return;
            }

            $this->acceptOffer($lockedOffer, $validated['message'] ?? null);
        });

        return back()->with('status', 'Tanggapan Anda berhasil disimpan.');
    }

    public function buyerRespond(Request $request, Offer $offer): RedirectResponse
    {
        abort_unless($offer->buyer_id === auth()->id(), 403);
        $validated = $request->validate([
            'action' => ['required', 'in:accept,decline,counter'],
            'unit_price' => ['required_if:action,counter', 'nullable', 'integer', 'min:1', 'max:1000000000000'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($offer, $validated) {
            $lockedOffer = Offer::query()->lockForUpdate()->findOrFail($offer->id);

            if ($validated['action'] === 'decline') {
                if (! in_array($lockedOffer->status, ['pending', 'countered'], true)) {
                    throw ValidationException::withMessages(['action' => 'Penawaran ini tidak dapat dibatalkan lagi.']);
                }
                $lockedOffer->update(['status' => 'cancelled']);
                $this->recordNegotiation($lockedOffer, 'buyer_declined', null, $validated['message'] ?? null);
                $lockedOffer->product->farmer->notify(new OfferActivityNotification(
                    'Penawaran dibatalkan',
                    $lockedOffer->buyer->name.' membatalkan penawaran untuk '.$lockedOffer->product->name.'.',
                    $lockedOffer->id,
                    $lockedOffer->product->name
                ));
                return;
            }

            if ($lockedOffer->status !== 'countered') {
                throw ValidationException::withMessages(['action' => 'Respons ini hanya dapat dilakukan setelah petani memberikan harga balasan.']);
            }

            if ($validated['action'] === 'counter') {
                $lockedOffer->update(['current_unit_price' => $validated['unit_price'], 'status' => 'pending']);
                $this->recordNegotiation($lockedOffer, 'buyer_counter', $validated['unit_price'], $validated['message'] ?? null);
                $lockedOffer->product->farmer->notify(new OfferActivityNotification(
                    'Pembeli mengajukan harga baru',
                    $lockedOffer->buyer->name.' mengajukan Rp'.number_format($validated['unit_price'], 0, ',', '.').' per '.$lockedOffer->product->unit.'.',
                    $lockedOffer->id,
                    $lockedOffer->product->name
                ));
                return;
            }

            $this->acceptOffer($lockedOffer, $validated['message'] ?? null);
        });

        return back()->with('status', 'Respons Anda berhasil disimpan.');
    }

    public function complete(Offer $offer): RedirectResponse
    {
        $this->ensureFarmerOwnsOffer($offer);

        DB::transaction(function () use ($offer) {
            $lockedOffer = Offer::query()->lockForUpdate()->findOrFail($offer->id);
            if ($lockedOffer->status !== 'accepted') {
                throw ValidationException::withMessages(['status' => 'Hanya pesanan yang disepakati yang dapat diselesaikan.']);
            }

            $lockedOffer->update(['status' => 'completed']);
            $this->recordNegotiation($lockedOffer, 'completed', $lockedOffer->agreed_unit_price, null);
            $lockedOffer->buyer->notify(new OfferActivityNotification(
                'Pesanan ditandai selesai',
                $lockedOffer->product->farmer->name.' menandai pesanan '.$lockedOffer->product->name.' selesai.',
                $lockedOffer->id,
                $lockedOffer->product->name
            ));
        });

        return back()->with('status', 'Pesanan ditandai selesai.');
    }

    private function acceptOffer(Offer $offer, ?string $message): void
    {
        $product = Product::query()->lockForUpdate()->findOrFail($offer->product_id);
        $requestedHundredths = (int) round((float) $offer->quantity * 100);
        $availableHundredths = (int) round((float) $product->available_quantity * 100);

        if (! $product->is_active || $requestedHundredths > $availableHundredths) {
            throw ValidationException::withMessages(['quantity' => 'Stok sudah berubah dan tidak mencukupi untuk menerima pesanan ini.']);
        }

        $remaining = ($availableHundredths - $requestedHundredths) / 100;
        $product->update([
            'available_quantity' => number_format($remaining, 2, '.', ''),
            'is_active' => $remaining > 0,
        ]);
        $offer->update([
            'status' => 'accepted',
            'agreed_unit_price' => $offer->current_unit_price,
        ]);
        $this->recordNegotiation($offer, 'accepted', $offer->current_unit_price, $message);

        $recipient = auth()->id() === $offer->buyer_id ? $product->farmer : $offer->buyer;
        $recipient->notify(new OfferActivityNotification(
            'Harga disepakati',
            'Harga Rp'.number_format($offer->current_unit_price, 0, ',', '.').' per '.$product->unit.' disepakati untuk '.$product->name.'.',
            $offer->id,
            $product->name
        ));
    }

    private function recordNegotiation(Offer $offer, string $action, ?int $unitPrice, ?string $message): void
    {
        $offer->negotiations()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'unit_price' => $unitPrice,
            'message' => $message,
        ]);
    }

    private function ensureFarmerOwnsOffer(Offer $offer): void
    {
        abort_unless($offer->product()->where('farmer_id', auth()->id())->exists(), 403);
    }
}
