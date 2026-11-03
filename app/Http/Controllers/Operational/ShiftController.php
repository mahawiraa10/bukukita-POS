<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use App\Models\CashierShift;
use App\Models\DailyClosing;
use App\Models\Sale;
use App\Support\IdGen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    private function assertHardStart(): void
    {
        // Hard-start 07:00 WIB (Function 9); jam server = source of truth
        if (now()->format('H:i') < '07:00') {
            abort(422, 'Operasional toko belum buka (jam mulai 07:00 WIB)');
        }
    }

    public function current()
    {
        return CashierShift::where('id_pengguna', (string) auth()->id())
            ->where('status_sesi', 'BUKA')->latest('waktu_buka')->first()
            ?? response()->json(['data' => null]);
    }

    public function open(Request $request)
    {
        $this->assertHardStart();
        $data = $request->validate([
            'nomor_kasir' => 'required|string|max:10',
            'modal_awal' => 'required|numeric|min:0',
        ]);
        $dup = CashierShift::where('id_pengguna', (string) auth()->id())
            ->whereDate('waktu_buka', today())->where('status_sesi', 'BUKA')->exists();
        if ($dup) {
            abort(422, 'Kasir sudah punya sesi aktif hari ini (1 sesi/hari)');
        }
        // Batas 2 shift per hari operasional (Shift 1 & Shift 2).
        if (CashierShift::whereDate('waktu_buka', today())->count() >= 2) {
            abort(422, 'Batas 2 shift per hari sudah tercapai (Shift 1 & Shift 2). Tutup kasir dulu.');
        }
        $s = CashierShift::create([
            'id_sesi_kasir' => IdGen::next(CashierShift::class, 'id_sesi_kasir', 'SFT', 2), // SFT-01, SFT-02
            'id_pengguna' => (string) auth()->id(),
            'nomor_kasir' => $data['nomor_kasir'],
            'waktu_buka' => now(), // jam asli -> audit lateness
            'modal_awal' => $data['modal_awal'],
            'status_sesi' => 'BUKA',
        ]);
        return response()->json($s, 201);
    }

    public function close(Request $request)
    {
        $data = $request->validate(['uang_fisik' => 'required|numeric|min:0']);
        $s = CashierShift::where('id_pengguna', (string) auth()->id())
            ->where('status_sesi', 'BUKA')->latest('waktu_buka')->firstOrFail();
        // Net cash: modal + tunai masuk - refund tunai (isolasi per shift)
        $salesCash = Sale::where('id_sesi_kasir', $s->id_sesi_kasir)
            ->where('payment_method', 'cash')->where('payment_status', 'paid')
            ->sum('final_amount');
        $refCash = DB::table('refunds')
            ->join('sales', 'sales.invoice_code', '=', 'refunds.id_transaksi_referensi')
            ->where('sales.id_sesi_kasir', $s->id_sesi_kasir)
            ->where('sales.payment_method', 'cash')
            ->sum('refunds.jumlah_pengembalian'); // refund tunai hari ini di shift ini
        $expected = $s->modal_awal + $salesCash - $refCash;
        $selisih = round($data['uang_fisik'] - $expected, 2);
        foreach (['TUNAI', 'DEBIT'] as $m) {
            $tot = Sale::where('id_sesi_kasir', $s->id_sesi_kasir)
                ->where('payment_status', 'paid')
                ->where('payment_method', $m === 'TUNAI' ? 'cash' : 'debit')
                ->sum('final_amount');
            if ($tot > 0 || $m === 'TUNAI') {
                DailyClosing::create([
                    'id_penutupan' => IdGen::next(DailyClosing::class, 'id_penutupan', 'CLS'),
                    'id_sesi_kasir' => $s->id_sesi_kasir,
                    'modal_awal' => $s->modal_awal,
                    'metode_pembayaran' => $m,
                    'hasil_penjualan' => $tot,
                    'jumlah_pengembalian' => $m === 'TUNAI' ? $refCash : 0,
                    'uang_fisik' => $m === 'TUNAI' ? $data['uang_fisik'] : null,
                    'selisih' => $m === 'TUNAI' ? $selisih : null,
                    'tanggal_penutupan' => today(),
                ]);
            }
        }
        $s->update(['status_sesi' => 'TUTUP', 'waktu_tutup' => now()]);
        return response()->json(['shift' => $s, 'expected_cash' => $expected, 'selisih' => $selisih]);
    }
}
