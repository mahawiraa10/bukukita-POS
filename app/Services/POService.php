<?php

namespace App\Services;

use App\Models\PoItem;
use App\Models\Department;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Subclass;
use App\Enums\POStatus;
use App\Support\IdGen;
use Illuminate\Support\Facades\DB;

class POService
{
    public function createPo(array $data, ?int $userId = null): PurchaseOrder
    {
        return DB::transaction(function () use ($data, $userId) {
            // Handle if new_product is provided in items
            $items = $data['items'] ?? [];
            foreach ($items as &$item) {
                if (isset($item['new_product']) && !isset($item['id_produk'])) {
                    $prodData = $item['new_product'];
                    $product = Product::create([
                        'id_produk'     => IdGen::next(Product::class, 'id_produk', 'PRD'),
                        'nama_produk'   => $prodData['nama_produk'],
                        'id_departemen' => $prodData['id_departemen'] ?? null,
                        'id_subkelas'   => $prodData['id_subkelas'] ?? null,
                        'id_pemasok'    => $data['id_pemasok'],
                        'penerbit'      => $prodData['penerbit'] ?? 'Penerbit',
                        'harga_jual'    => $prodData['harga_jual'] ?? 0,
                        'harga_beli'    => $prodData['harga_beli'] ?? 0,
                        'harga_pokok'   => $prodData['harga_pokok'] ?? 0,
                        'sku'           => IdGen::next(Product::class, 'sku', 'SKU'),
                        'stok'          => 0,
                    ]);
                    $item['id_produk'] = $product->id_produk;
                }
            }
            unset($item);

            $po = PurchaseOrder::create([
                'po_code'       => $this->generatePOCode(),
                'id_pemasok'    => $data['id_pemasok'],
                'id_acara'      => $data['id_acara'] ?? null,
                'tipe_po'       => $data['tipe_po'] ?? 'reguler',
                'user_id'       => $userId ?? auth()->id(),
                'status'        => POStatus::DRAFT->value,
                'total_amount'  => 0,
                'payment_method' => $data['payment_method'] ?? 'CASH',
                'notes'         => $data['notes'] ?? null,
                'expected_date' => $data['expected_date'] ?? null,
            ]);

            $total = 0;
            foreach ($items as $item) {
                $subtotal = $item['qty'] * $item['unit_price'];
                $total += $subtotal;
                PoItem::create([
                    'purchase_order_id' => $po->id,
                    'id_produk'         => $item['id_produk'],
                    'qty'               => $item['qty'],
                    'unit_price'        => $item['unit_price'],
                    'subtotal'          => $subtotal,
                    'received_qty'      => 0,
                ]);
            }

            $po->update(['total_amount' => $total]);

            return $po->fresh(['items', 'vendor']);
        });
    }

    public function approve(int $id): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);

        if ($po->status !== POStatus::DRAFT->value) {
            throw new \DomainException('Hanya PO status draft yang bisa di-approve.');
        }

        $po->update(['status' => POStatus::APPROVED->value]);

        return $po->fresh(['items', 'vendor']);
    }

    /**
     * Terima barang PO. Stok masuk lewat StockService (append-only ledger),
     * bukan increment kolom langsung.
     */
    public function receive(int $id, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($id, $items) {
            $po = PurchaseOrder::with('items')->findOrFail($id);

            if (!in_array($po->status, [POStatus::APPROVED->value, 'partial'])) {
                throw new \DomainException('PO harus status approved atau partial sebelum receive.');
            }

            $allCompleted = true;

            foreach ($items as $row) {
                $item = $po->items->firstWhere('id', $row['id']);
                if (!$item) {
                    throw new \DomainException('Item PO tidak ditemukan.');
                }
                
                $newReceivedTotal = $item->received_qty + $row['received_qty'];

                if ($newReceivedTotal > $item->qty) {
                    throw new \DomainException("Total qty diterima ({$newReceivedTotal}) melebihi pesanan (maks: {$item->qty}).");
                }

                $item->update(['received_qty' => $newReceivedTotal]);
                
                $product = Product::findOrFail($item->id_produk);
                StockService::record($product, 'PENERIMAAN', (int) $row['received_qty'], 'purchase_order', $po->po_code, (string) auth()->id());

                if ($newReceivedTotal < $item->qty) {
                    $allCompleted = false;
                }
            }

            $newStatus = $allCompleted ? POStatus::RECEIVED->value : 'partial';

            $po->update([
                'status'        => $newStatus,
                'received_date' => now()->toDateString(),
            ]);

            return $po->fresh(['items', 'vendor']);
        });
    }

    public function cancel(int $id): PurchaseOrder
    {
        $po = PurchaseOrder::findOrFail($id);

        if (!in_array($po->status, [POStatus::DRAFT->value, POStatus::APPROVED->value])) {
            throw new \DomainException('Hanya PO draft/approved yang bisa dibatalkan.');
        }

        $po->update(['status' => POStatus::CANCELLED->value]);

        return $po->fresh(['items', 'vendor']);
    }

    protected function generatePOCode(): string
    {
        $prefix = 'PO-' . date('Ym');
        $n = PurchaseOrder::where('po_code', 'like', "{$prefix}-%")->count() + 1;
        return $prefix . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}