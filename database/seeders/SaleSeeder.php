<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Payment;
use App\Models\Book;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $customers = Customer::all();
        $books = Book::all();

        for ($i = 1; $i <= 300; $i++) {
            $user = $users->random();
            $customer = $customers->random();
            $itemCount = rand(1, 3);

            $total = 0;
            $items = [];

            for ($j = 0; $j < $itemCount; $j++) {
                $book = $books->random();
                $qty = rand(1, 3);

                if ($book->stock < $qty) {
                    continue; // skip kalau stok kurang
                }

                $subtotal = $qty * $book->price;
                $total += $subtotal;

                $items[] = [
                    'book_id' => $book->id,
                    'qty' => $qty,
                    'price' => $book->price,
                    'subtotal' => $subtotal,
                ];

                $book->decrement('stock', $qty);
            }

            if (empty($items)) {
                continue; // skip kalau nggak ada item yang valid
            }

            $discount = rand(0, 10) > 8 ? rand(1000, 10000) : 0;
            $finalAmount = max(0, $total - $discount);

            $sale = Sale::create([
                'invoice_code' => 'INV-' . date('Ym') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'user_id' => $user->id,
                'total_amount' => $total,
                'discount' => $discount,
                'final_amount' => $finalAmount,
                'payment_method' => ['cash', 'qris', 'transfer'][rand(0, 2)],
                'payment_status' => 'paid',
                'sale_date' => now()->subDays(rand(0, 180)), // 6 bulan terakhir
            ]);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'book_id' => $item['book_id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            Payment::create([
                'sale_id' => $sale->id,
                'amount' => $finalAmount,
                'payment_method' => $sale->payment_method,
                'status' => 'success',
                'paid_at' => $sale->sale_date,
            ]);

            // Update customer LTV
            $customer->increment('total_spent', $finalAmount);
            $customer->increment('points', (int) ($finalAmount / 1000));
        }
    }
}
