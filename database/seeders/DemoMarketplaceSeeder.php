<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoMarketplaceSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'SokoTaniDemo!2026';

    public function run(): void
    {
        if (app()->isProduction()) {
            throw new RuntimeException('Demo accounts must not be seeded into production.');
        }

        DB::transaction(function (): void {
            $farmers = [
                [
                    'name' => 'Budi Santoso',
                    'email' => 'farmer.budi@sokotani.test',
                    'farm_location' => 'Desa Sumberarum, Magelang',
                    'farm_description' => 'Petani padi dan palawija lokal. Mengutamakan panen segar dan pasokan grosir yang konsisten.',
                    'products' => [
                        [
                            'name' => 'Beras Organik Panen Baru',
                            'category' => 'Beras',
                            'description' => 'Beras organik pulen dari panen lokal. Tersedia untuk kebutuhan toko dan usaha kuliner.',
                            'unit' => 'kg',
                            'available_quantity' => 500,
                            'minimum_quantity' => 25,
                            'asking_price_per_unit' => 16000,
                            'location' => 'Desa Sumberarum, Magelang',
                        ],
                        [
                            'name' => 'Jagung Pipil Kering',
                            'category' => 'Palawija',
                            'description' => 'Jagung pipil kering siap untuk pakan atau bahan pangan. Harga dapat dibicarakan untuk pembelian besar.',
                            'unit' => 'kg',
                            'available_quantity' => 800,
                            'minimum_quantity' => 50,
                            'asking_price_per_unit' => 6500,
                            'location' => 'Desa Sumberarum, Magelang',
                        ],
                    ],
                ],
                [
                    'name' => 'Sari Wulandari',
                    'email' => 'farmer.sari@sokotani.test',
                    'farm_location' => 'Desa Sumberarum, Magelang',
                    'farm_description' => 'Mengelola kebun sayur keluarga dengan tomat dan cabai segar untuk pasar lokal.',
                    'products' => [
                        [
                            'name' => 'Tomat Merah Segar',
                            'category' => 'Sayuran',
                            'description' => 'Tomat segar pilihan dari kebun lokal, dipanen sesuai pesanan.',
                            'unit' => 'kg',
                            'available_quantity' => 300,
                            'minimum_quantity' => 20,
                            'asking_price_per_unit' => 12000,
                            'location' => 'Desa Sumberarum, Magelang',
                        ],
                        [
                            'name' => 'Cabai Merah Keriting',
                            'category' => 'Sayuran',
                            'description' => 'Cabai merah keriting segar untuk pasar, warung makan, dan usaha pengolahan.',
                            'unit' => 'kg',
                            'available_quantity' => 120,
                            'minimum_quantity' => 10,
                            'asking_price_per_unit' => 48000,
                            'location' => 'Desa Sumberarum, Magelang',
                        ],
                    ],
                ],
                [
                    'name' => 'Joko Prasetyo',
                    'email' => 'farmer.joko@sokotani.test',
                    'farm_location' => 'Desa Sumberarum, Magelang',
                    'farm_description' => 'Menyediakan kentang dan buah lokal dengan pilihan jumlah untuk kebutuhan usaha.',
                    'products' => [
                        [
                            'name' => 'Kentang Granola',
                            'category' => 'Sayuran',
                            'description' => 'Kentang granola bersih dan disortir, cocok untuk restoran dan pedagang sayur.',
                            'unit' => 'kg',
                            'available_quantity' => 450,
                            'minimum_quantity' => 25,
                            'asking_price_per_unit' => 18000,
                            'location' => 'Desa Sumberarum, Magelang',
                        ],
                        [
                            'name' => 'Pisang Kepok Tandan',
                            'category' => 'Buah',
                            'description' => 'Pisang kepok segar tersedia per sisir maupun dalam jumlah grosir.',
                            'unit' => 'kg',
                            'available_quantity' => 200,
                            'minimum_quantity' => 15,
                            'asking_price_per_unit' => 14000,
                            'location' => 'Desa Sumberarum, Magelang',
                        ],
                    ],
                ],
            ];

            foreach ($farmers as $farmerData) {
                $products = $farmerData['products'];
                unset($farmerData['products']);

                $farmer = User::query()->updateOrCreate(
                    ['email' => $farmerData['email']],
                    [
                        'name' => $farmerData['name'],
                        'role' => 'farmer',
                        'password' => Hash::make(self::DEMO_PASSWORD),
                        'farm_location' => $farmerData['farm_location'],
                        'farm_description' => $farmerData['farm_description'],
                    ]
                );

                foreach ($products as $productData) {
                    $farmer->products()->firstOrCreate(
                        ['name' => $productData['name']],
                        $productData + ['is_active' => true]
                    );
                }
            }

            $buyers = [
                ['name' => 'Maya - Toko Segar', 'email' => 'buyer.maya@sokotani.test'],
                ['name' => 'Andi - Warung Rasa', 'email' => 'buyer.andi@sokotani.test'],
                ['name' => 'Rina - Pasar Pagi', 'email' => 'buyer.rina@sokotani.test'],
            ];

            foreach ($buyers as $buyerData) {
                User::query()->updateOrCreate(
                    ['email' => $buyerData['email']],
                    [
                        'name' => $buyerData['name'],
                        'role' => 'buyer',
                        'password' => Hash::make(self::DEMO_PASSWORD),
                    ]
                );
            }

            User::query()->updateOrCreate(
                ['email' => 'admin@sokotani.test'],
                [
                    'name' => 'Admin SokoTani',
                    'role' => 'admin',
                    'password' => Hash::make(self::DEMO_PASSWORD),
                ]
            );
        });
    }
}
