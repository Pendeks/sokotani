<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request): View
    {
        $featuredProducts = Product::query()
            ->with('farmer')
            ->where('is_active', true)
            ->where('available_quantity', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->string('q')->toString());
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->filled('location'), fn ($query) => $query->where('location', 'like', '%'.$request->string('location')->toString().'%'))
            ->latest()
            ->limit(5)
            ->get();

        $products = Product::query()
            ->with('farmer')
            ->where('is_active', true)
            ->where('available_quantity', '>', 0)
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->string('q')->toString());
                $query->where(function ($query) use ($term) {
                    $query->where('name', 'like', "%{$term}%")
                        ->orWhere('category', 'like', "%{$term}%")
                        ->orWhere('location', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->when($request->filled('location'), fn ($query) => $query->where('location', 'like', '%'.$request->string('location')->toString().'%'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Product::query()->where('is_active', true)->distinct()->orderBy('category')->pluck('category');

        return view('marketplace.index', compact('products', 'categories', 'featuredProducts'));
    }

    public function show(Product $product): View
    {
        $hasExistingOffer = auth()->check()
            && auth()->user()->offers()->where('product_id', $product->id)->exists();
        $isAvailable = $product->is_active && (float) $product->available_quantity > 0;
        abort_unless($isAvailable || auth()->id() === $product->farmer_id || $hasExistingOffer, 404);

        return view('marketplace.show', ['product' => $product->load('farmer')]);
    }

    public function myListings(): View
    {
        $products = auth()->user()->products()->withCount('offers')->latest()->paginate(12);

        return view('farmer.listings.index', compact('products'));
    }

    public function dashboard(): RedirectResponse
    {
        return match (auth()->user()->role) {
            'farmer' => redirect()->route('farmer.listings.index'),
            'admin' => redirect()->route('admin.farmers.index'),
            default => redirect()->route('offers.index'),
        };
    }

    public function create(): View
    {
        return view('farmer.listings.form', ['product' => new Product()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);
        $validated['image_path'] = $request->file('image')?->store('product-images', 'public');
        unset($validated['image']);
        $validated['is_active'] = true;

        auth()->user()->products()->create($validated);

        return redirect()->route('farmer.listings.index')->with('status', 'Produk berhasil dipasang. Pembeli dapat mengajukan harga untuk pembelian grosir.');
    }

    public function edit(Product $product): View
    {
        $this->authorizeFarmer($product);

        return view('farmer.listings.form', compact('product'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorizeFarmer($product);
        $validated = $this->validateProduct($request);
        unset($validated['image']);

        if ($request->hasFile('image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('product-images', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active');
        $product->update($validated);

        return redirect()->route('farmer.listings.index')->with('status', 'Informasi produk berhasil diperbarui.');
    }

    public function deactivate(Product $product): RedirectResponse
    {
        $this->authorizeFarmer($product);
        $product->update(['is_active' => false]);

        return redirect()->route('farmer.listings.index')->with('status', 'Produk disembunyikan dari katalog.');
    }

    private function validateProduct(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:5000'],
            'unit' => ['required', 'string', 'max:40'],
            'available_quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'minimum_quantity' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999.99'],
            'asking_price_per_unit' => ['required', 'integer', 'min:1', 'max:1000000000000'],
            'location' => ['required', 'string', 'max:160'],
            'harvest_date' => ['nullable', 'date', 'after_or_equal:today'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if ((float) $validated['minimum_quantity'] > (float) $validated['available_quantity']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'minimum_quantity' => 'Jumlah minimum tidak boleh melebihi stok tersedia.',
            ]);
        }

        return $validated;
    }

    private function authorizeFarmer(Product $product): void
    {
        abort_unless($product->farmer_id === auth()->id(), 403);
    }
}
