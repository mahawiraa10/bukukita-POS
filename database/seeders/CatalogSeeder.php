<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Novel', 'description' => 'Fiksi & sastra populer'],
            ['name' => 'Self Development', 'description' => 'Pengembangan diri'],
            ['name' => 'Filsafat', 'description' => 'Pemikiran & filosofi'],
            ['name' => 'Bisnis', 'description' => 'Ekonomi, manajemen, wirausaha'],
            ['name' => 'Komik', 'description' => 'Manga & komik'],
            ['name' => 'Anak', 'description' => 'Buku anak & pendidikan'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $publishers = [
            ['name' => 'Gramedia Pustaka Utama', 'phone' => '021-53650110', 'email' => 'cs@gramedia.com'],
            ['name' => 'Mizan Pustaka', 'phone' => '022-2512234', 'email' => 'cs@mizan.com'],
            ['name' => 'Bentang Pustaka', 'phone' => '0274-543005', 'email' => 'cs@bentang.com'],
            ['name' => 'Falcon Publishing', 'phone' => '021-78888590', 'email' => 'cs@falcon.com'],
            ['name' => 'Grasindo', 'phone' => '021-6291661', 'email' => 'cs@grasindo.com'],
            ['name' => 'Kepustakaan Populer', 'phone' => '021-8611351', 'email' => 'cs@kkp.com'],
        ];

        foreach ($publishers as $publisher) {
            Publisher::create($publisher);
        }

        $titles = [
            // Novel
            ['Laut Bercerita', 'Leila S. Chudori', 'Novel', 99000],
            ['Bumi Manusia', 'Pramoedya Ananta Toer', 'Novel', 110000],
            ['Gadis Kretek', 'Ratih Kumalasari', 'Novel', 95000],
            ['Cantik Itu Luka', 'Eka Kurniawan', 'Novel', 105000],
            ['Rumah Lebah', 'Husna Hasim', 'Novel', 89000],
            ['Atomic Habit', 'James Clear', 'Self Development', 128000],
            ['Filosofi Teras', 'Henry Manampiring', 'Self Development', 98000],
            ['Mindset', 'Carol S. Dweck', 'Self Development', 115000],
            ['Rich Dad Poor Dad', 'Robert T. Kiyosaki', 'Bisnis', 92000],
            ['The Psychology of Money', 'Morgan Housel', 'Bisnis', 108000],
            // ... dkk, cukup contoh 10, sisanya bisa ditambah nanti
        ];

        foreach ($titles as $title) {
            Book::create([
                'title' => $title[0],
                'author' => $title[1],
                'isbn' => '978-' . rand(1000000000, 9999999999),
                'publisher_id' => Publisher::inRandomOrder()->first()->id,
                'category_id' => Category::where('name', $title[2])->first()->id,
                'price' => $title[3],
                'purchase_price' => $title[3] * 0.7, // margin 30%
                'stock' => rand(300, 500), // cukup buat 300 transaksi seeder + sisa buat testing
                'min_stock' => 5,
                'description' => "Buku {$title[0]} karya {$title[1]}.",
                'is_active' => true,
            ]);
        }
    }
}
