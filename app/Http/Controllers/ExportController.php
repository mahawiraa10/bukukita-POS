<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ExportController extends Controller
{
    public function sales(Request $r)
    {
        $q = Sale::with(['customer', 'user'])
            ->when($r->query('from'), fn ($qq) => $qq->whereDate('sale_date', '>=', $r->query('from')))
            ->when($r->query('to'), fn ($qq) => $qq->whereDate('sale_date', '<=', $r->query('to')))
            ->whereHas('items', fn ($iq) => $iq->whereNotNull('id_produk')); // jalur v2 saja

        $csv = "invoice_code,customer,sale_date,total_amount,discount,final_amount,payment_method\n";
        foreach ($q->get() as $s) {
            $csv .= implode(',', [
                $s->invoice_code,
                '"' . ($s->customer->name ?? 'Guest') . '"',
                $s->sale_date, $s->total_amount, $s->discount,
                $s->final_amount, $s->payment_method,
            ]) . "\n";
        }

        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="sales.csv"']);
    }

    public function purchaseOrderSingle(Request $r, int $id)
    {
        $po = DB::table('purchase_orders as po')
            ->leftJoin('vendors as v', 'v.id_pemasok', '=', 'po.id_pemasok')
            ->where('po.id', $id)
            ->select('po.*', 'v.nama_pemasok')
            ->first();

        if (!$po) {
            return response()->json(['message' => 'PO tidak ditemukan'], 404);
        }

        $items = DB::table('po_items as pi')
            ->leftJoin('products as p', 'p.id_produk', '=', 'pi.id_produk')
            ->where('pi.purchase_order_id', $id)
            ->select('pi.*', 'p.nama_produk', 'p.isbn')
            ->get();

        $csv = "NO PO: {$po->po_code}\n";
        $csv .= "Tanggal Dibuat: {$po->created_at}\n";
        $csv .= "Vendor: {$po->nama_pemasok}\n";
        $csv .= "Tipe: {$po->tipe_po}\n";
        $csv .= "Metode Pembayaran: {$po->payment_method}\n";
        $csv .= "Status: {$po->status}\n\n";
        $csv .= "isbn,nama_produk,jumlah,harga_satuan,metode_pembayaran,subtotal\n";

        foreach ($items as $it) {
            $subtotal = $it->qty * $it->unit_price;
            $csv .= implode(',', [
                $it->isbn ?? '-',
                '"' . ($it->nama_produk ?? '-') . '"',
                $it->qty,
                $it->unit_price,
                $po->payment_method,
                $subtotal,
            ]) . "\n";
        }

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="PO-' . $po->po_code . '.csv"'
        ]);
    }
}