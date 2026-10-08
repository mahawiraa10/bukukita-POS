<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'user', 'payment', 'event']);
        $company = \App\Models\Company::first();
        // ponytail: DOMPDF saat butuh PDF beneran; sekarang HTML printable cukup.
        return response()->view('receipt', compact('sale', 'company'))->header('Content-Type', 'text/html');
    }
}
