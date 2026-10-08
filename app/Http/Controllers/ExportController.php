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

    public function purchaseOrders(Request $r)
    {
        // ponytail: CSV langsung dari query builder, hindari eager-load relasi legacy.
        $rows = DB::table('purchase_orders as po')
            ->leftJoin('vendors as v', 'v.id_pemasok', '=', 'po.id_pemasok')
            ->when($r->query('status'), fn ($q, $v) => $q->where('po.status', $v))
            ->select('po.po_code', 'v.nama_pemasok', 'po.status', 'po.total_amount', 'po.expected_date', 'po.received_date')
            ->get();

        $csv = "po_code,vendor,status,total_amount,expected_date,received_date\n";
        foreach ($rows as $po) {
            $csv .= implode(',', [
                $po->po_code, '"' . ($po->nama_pemasok ?? '-') . '"', $po->status,
                $po->total_amount, $po->expected_date ?? '', $po->received_date ?? '',
            ]) . "\n";
        }

        return Response::make($csv, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="purchase_orders.csv"']);
    }
}