<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\StockMutation;
use Illuminate\Http\Request;

class StockMutationController extends Controller
{
    public function index(Request $request)
    {
        return StockMutation::query()
            ->when($request->id_produk, fn($q, $v) => $q->where('id_produk', $v))
            ->when($request->jenis, fn($q, $v) => $q->where('jenis_perubahan', $v))
            ->when($request->from, fn($q, $f) => $q->whereDate('tanggal_perubahan', '>=', $f))
            ->when($request->to, fn($q, $t) => $q->whereDate('tanggal_perubahan', '<=', $t))
            ->orderByDesc('id_perubahan_stok')
            ->paginate($request->input('per_page', 50));
    }
}
