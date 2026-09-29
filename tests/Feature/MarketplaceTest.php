<?php

namespace Tests\Feature;

use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_catalog_lists_active_products_and_filters_search(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $this->createProduct($farmer, ['name' => 'Beras organik', 'category' => 'Beras']);
        $this->createProduct($farmer, ['name' => 'Tomat merah', 'category' => 'Sayuran']);
        $this->createProduct($farmer, ['name' => 'Beras habis', 'is_active' => false]);

        $response = $this->get('/?q=organik');

        $response->assertOk();
        $response->assertSee('Hasil panen pilihan');
        $response->assertSee('Lihat &amp; tawar', false);
        $response->assertSee('Beras organik');
        $response->assertDontSee('Tomat merah');
        $response->assertDontSee('Beras habis');
    }

    public function test_farmer_can_register_and_publish_a_bulk_listing(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Petani Sari',
            'email' => 'sari@example.test',
            'role' => 'farmer',
            'password' => 'strong-password',
            'password_confirmation' => 'strong-password',
        ])->assertRedirect(route('dashboard'));

        $farmer = User::query()->where('email', 'sari@example.test')->firstOrFail();
        $this->assertSame('farmer', $farmer->role);
        $this->assertAuthenticatedAs($farmer);
        $this->get(route('dashboard'))->assertRedirect(route('farmer.listings.index'));

        $this->post(route('farmer.listings.store'), [
            'name' => 'Beras panen baru',
            'category' => 'Beras',
            'description' => 'Beras lokal panen minggu ini.',
            'unit' => 'karung 25 kg',
            'available_quantity' => 40,
            'minimum_quantity' => 5,
            'asking_price_per_unit' => 275000,
            'location' => 'Desa Sumberarum, Magelang',
        ])->assertRedirect(route('farmer.listings.index'));

        $this->assertDatabaseHas('products', [
            'farmer_id' => $farmer->id,
            'name' => 'Beras panen baru',
            'asking_price_per_unit' => 275000,
        ]);
        $this->get(route('home'))->assertOk()->assertSee('Beras panen baru');
    }

    public function test_farmer_profile_is_public_and_admin_controls_verification(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($farmer)->put(route('farmer.profile.update'), [
            'name' => 'Petani Lestari',
            'phone' => '081234567890',
            'farm_location' => 'Desa Sumberarum, Magelang',
            'farm_description' => 'Menanam sayuran segar untuk pasar lokal.',
        ])->assertRedirect(route('farmer.profile.edit'));

        $this->get(route('farmers.show', $farmer))
            ->assertOk()
            ->assertSee('Petani Lestari')
            ->assertSee('Desa Sumberarum, Magelang')
            ->assertDontSee('081234567890')
            ->assertDontSee('Terverifikasi');

        $this->actingAs($buyer)->patch(route('admin.farmers.verification', $farmer), ['is_verified' => true])->assertForbidden();

        $this->actingAs($admin)->patch(route('admin.farmers.verification', $farmer), ['is_verified' => true])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->get(route('farmers.show', $farmer))->assertSee('✓ Terverifikasi');

        $this->actingAs($admin)->get(route('admin.farmers.index'))
            ->assertOk()
            ->assertSee('Petani Lestari')
            ->assertSee('Cabut verifikasi');

        $this->actingAs($admin)->patch(route('admin.farmers.verification', $farmer), ['is_verified' => false])
            ->assertRedirect();
        $this->assertFalse($farmer->fresh()->is_verified);
    }

    public function test_product_detail_explains_how_to_negotiate_for_each_account_state(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->createProduct($farmer);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Masuk sebagai pembeli untuk mengajukan harga.')
            ->assertSee(route('login'));

        $this->actingAs($otherFarmer)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Untuk mengajukan harga, gunakan akun pembeli.')
            ->assertDontSee('name="unit_price"', false);

        $this->actingAs($buyer)->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Ajukan penawaran grosir')
            ->assertSee('href="#offer-form"', false)
            ->assertSee('Petani menanggapi atau menawar balik');
    }

    public function test_buyer_and_farmer_can_negotiate_and_accept_a_bulk_order(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->createProduct($farmer, [
            'available_quantity' => 20,
            'minimum_quantity' => 10,
            'asking_price_per_unit' => 10000,
        ]);

        $this->actingAs($buyer)->post(route('offers.store', $product), [
            'quantity' => 20,
            'unit_price' => 8000,
            'fulfillment_method' => 'pickup',
            'fulfillment_notes' => 'Ambil hari Jumat pagi',
            'message' => 'Saya membeli untuk toko.',
        ])->assertRedirect(route('offers.index'));

        $offer = Offer::query()->firstOrFail();
        $this->assertSame('pending', $offer->status);
        $this->assertSame(8000, $offer->current_unit_price);
        $this->assertDatabaseHas('offer_negotiations', ['offer_id' => $offer->id, 'action' => 'buyer_offer']);
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $farmer->id,
        ]);

        $this->actingAs($farmer)->post(route('farmer.offers.respond', $offer), [
            'action' => 'counter',
            'unit_price' => 9000,
            'message' => 'Harga terbaik untuk pembelian ini.',
        ])->assertRedirect();

        $this->actingAs($buyer)->post(route('offers.buyer.respond', $offer), [
            'action' => 'accept',
        ])->assertRedirect();

        $offer->refresh();
        $product->refresh();
        $this->assertSame('accepted', $offer->status);
        $this->assertSame(9000, $offer->agreed_unit_price);
        $this->assertSame('0.00', $product->available_quantity);
        $this->assertDatabaseHas('offer_negotiations', ['offer_id' => $offer->id, 'action' => 'farmer_counter']);
        $this->assertDatabaseHas('offer_negotiations', ['offer_id' => $offer->id, 'action' => 'accepted']);
        $this->actingAs($buyer)->get(route('notifications.index'))->assertOk()->assertSee('Petani mengajukan harga balasan');
        $notification = $buyer->notifications()->firstOrFail();
        $this->actingAs($farmer)->post(route('notifications.read', $notification->id))->assertNotFound();
        $this->actingAs($buyer)->get(route('home'))->assertSee('notification-count');
        $this->actingAs($buyer)->post(route('notifications.read', $notification->id))->assertRedirect(route('offers.index'));
        $this->assertNotNull($notification->fresh()->read_at);
        $this->actingAs($buyer)->get(route('products.show', $product))->assertOk();

        $this->actingAs($farmer)->post(route('farmer.offers.complete', $offer))->assertRedirect();
        $this->assertSame('completed', $offer->refresh()->status);
    }

    public function test_a_buyer_cannot_offer_on_their_own_listing_or_exceed_stock(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = $this->createProduct($farmer, ['available_quantity' => 20, 'minimum_quantity' => 5]);

        $this->actingAs($farmer)->post(route('offers.store', $product), [
            'quantity' => 5,
            'unit_price' => 5000,
            'fulfillment_method' => 'pickup',
        ])->assertForbidden();

        $this->actingAs($buyer)->from(route('products.show', $product))->post(route('offers.store', $product), [
            'quantity' => 21,
            'unit_price' => 5000,
            'fulfillment_method' => 'pickup',
        ])->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('offers', 0);
    }

    public function test_only_the_listing_farmer_can_change_the_listing(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $otherFarmer = User::factory()->create(['role' => 'farmer']);
        $product = $this->createProduct($farmer);

        $this->actingAs($otherFarmer)->get(route('farmer.listings.edit', $product))->assertForbidden();
    }

    private function createProduct(User $farmer, array $attributes = []): Product
    {
        return $farmer->products()->create(array_merge([
            'name' => 'Wortel organik',
            'category' => 'Sayuran',
            'description' => 'Wortel segar dari panen lokal.',
            'unit' => 'kg',
            'available_quantity' => 50,
            'minimum_quantity' => 10,
            'asking_price_per_unit' => 12000,
            'location' => 'Desa Sumberarum, Magelang',
            'is_active' => true,
        ], $attributes));
    }
}
