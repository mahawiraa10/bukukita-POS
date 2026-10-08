<?php

namespace App\Http\Controllers\Operational;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 12 laporan sesuai spec "KP - Database.csv" (sheet Query/Report).
 * Tiap method return [metadata, rekap(list of [label,value]), header, rows(iterable)].
 * Semua endpoint mendukung ?export=csv -> native stream php://output (tanpa lib).
 */
class ReportController extends Controller
{
    private function period(Request $r): array
    {
        return [
            $r->query('dari', now()->startOfMonth()->toDateString()),
            $r->query('sampai', now()->toDateString()),
        ];
    }

    public function index()
    {
        return response()->json(['laporan' => [
            'vendor-receiving', 'internal-receiving', 'vendor-return',
            'receipts-recap', 'receipts-detail', 'omzet-produk', 'omzet-departemen',
            'omzet-kasir', 'member-baru', 'member-riwayat', 'till-closing', 'event-closing',
        ]]);
    }

    // ===== 1. Penerimaan Barang dari Pemasok =====
    public function vendorReceiving(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('receiving_goods')
            ->join('vendors', 'vendors.id_pemasok', '=', 'receiving_goods.id_pemasok')
            ->whereBetween('receiving_goods.tanggal_penerimaan', [$dari, $sampai])
            ->when($r->query('id_pemasok'), fn ($w, $v) => $w->where('receiving_goods.id_pemasok', $v));
        $rows = $q->get()->map(fn ($x) => [
            $x->id_penerimaan, $x->nomor_surat_jalan, $x->nomor_faktur, $x->id_pemasok,
            $x->nama_pemasok, $x->penerima, (string) $x->tanggal_penerimaan, $x->status,
        ]);
        $rekap = [
            ['Total Dokumen', (string) $q->count()],
            ['Selesai', (string) $q->clone()->where('status', 'SELESAI')->count()],
            ['Sebagian', (string) $q->clone()->where('status', 'SEBAGIAN')->count()],
        ];
        return $this->respond($r, 'LAPORAN PENERIMAAN BARANG DARI PEMASOK', $dari, $sampai, $rekap,
            ['No Penerimaan', 'No Surat Jalan', 'No Faktur', 'ID Pemasok', 'Nama Pemasok', 'Penerima', 'Tanggal', 'Status'],
            $rows);
    }

    // ===== 2. Penerimaan Internal / Load Barang (Event) =====
    public function internalReceiving(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('product_loads')
            ->leftJoin('events', 'events.id_acara', '=', 'product_loads.id_acara')
            ->whereBetween('product_loads.tanggal_load', [$dari, $sampai])
            ->when($r->query('id_acara'), fn ($w, $v) => $w->where('product_loads.id_acara', $v));
        $rows = $q->get()->map(fn ($x) => [
            $x->id_load, $x->id_acara ?? '-', $x->nama_acara ?? 'REGULER', $x->sumber_data,
            $x->id_produk, (string) $x->jumlah_produk, (string) $x->tanggal_load, $x->status_load,
        ]);
        $rekap = [
            ['Total Baris Load', (string) $q->count()],
            ['Total Qty Masuk', (string) $q->clone()->sum('product_loads.jumlah_produk')],
        ];
        return $this->respond($r, 'LAPORAN PENERIMAAN INTERNAL / LOAD BARANG', $dari, $sampai, $rekap,
            ['No Load', 'ID Acara', 'Nama Acara', 'Sumber Data', 'ID Produk', 'Qty', 'Tanggal', 'Status'],
            $rows);
    }

    // ===== 3. Pengembalian Barang ke Pemasok =====
    public function vendorReturn(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('vendor_returns')
            ->leftJoin('vendors', 'vendors.id_pemasok', '=', 'vendor_returns.id_pemasok')
            ->whereBetween('vendor_returns.tanggal_pengembalian', [$dari, $sampai]);
        $rows = $q->get()->map(fn ($x) => [
            $x->id_pengembalian, $x->id_pemasok, $x->nama_pemasok ?? '-', $x->id_penerimaan ?? '-',
            $x->id_produk, $x->nama_produk, $x->alasan, (string) $x->jumlah_dikembalikan,
            (string) $x->tanggal_pengembalian,
        ]);
        $rekap = [
            ['Total Baris Retur', (string) $q->count()],
            ['Total Qty Retur', (string) $q->clone()->sum('vendor_returns.jumlah_dikembalikan')],
        ];
        return $this->respond($r, 'LAPORAN PENGEMBALIAN BARANG KE PEMASOK', $dari, $sampai, $rekap,
            ['No Retur', 'ID Pemasok', 'Nama Pemasok', 'Ref Penerimaan', 'ID Produk', 'Nama Produk', 'Alasan', 'Qty', 'Tanggal'],
            $rows);
    }

    // ===== 4. Rekap Struk per Kasir =====
    public function receiptsRecap(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $rowsRaw = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween(DB::raw('DATE(sales.sale_date)'), [$dari, $sampai])
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.name, COUNT(*) nota, SUM(sales.final_amount) omzet')
            ->orderByDesc('omzet')->get();
        $rows = $rowsRaw->map(fn ($x) => [
            $x->name, (string) $x->nota, number_format((float) $x->omzet, 0, '', ''),
        ]);
        $rekap = [
            ['Total Nota', (string) DB::table('sales')->whereBetween(DB::raw('DATE(sale_date)'), [$dari, $sampai])->count()],
            ['Omzet Bersih', number_format((float) DB::table('sales')->whereBetween(DB::raw('DATE(sale_date)'), [$dari, $sampai])->where('payment_status', 'paid')->sum('final_amount'), 0, '', '')],
        ];
        return $this->respond($r, 'REKAP STRUK PER KASIR', $dari, $sampai, $rekap,
            ['Kasir', 'Jumlah Nota', 'Omzet Bersih'], $rows);
    }

    // ===== 5. Detail Struk per Kasir =====
    public function receiptsDetail(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween(DB::raw('DATE(sales.sale_date)'), [$dari, $sampai])
            ->when($r->query('user_id'), fn ($w, $v) => $w->where('sales.user_id', $v));
        $rows = $q->get()->map(fn ($x) => [
            $x->invoice_code, (string) $x->sale_date, $x->name,
            (string) ($x->total_amount ?? 0), (string) ($x->discount ?? 0),
            (string) $x->final_amount, $this->mapMethod($x->payment_method), strtoupper($x->payment_status),
        ]);
        $rekap = [['Total Transaksi', (string) $q->count()]];
        return $this->respond($r, 'DETAIL STRUK PER KASIR', $dari, $sampai, $rekap,
            ['No Invoice', 'Tanggal', 'Kasir', 'Subtotal', 'Diskon', 'Total Akhir', 'Metode', 'Status'], $rows);
    }

    // ===== 6. Omzet POS per Judul Produk =====
    public function omzetProduk(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $rowsRaw = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id_produk', '=', 'sale_items.id_produk')
            ->whereBetween(DB::raw('DATE(sales.sale_date)'), [$dari, $sampai])
            ->where('sales.payment_status', 'paid')
            ->groupBy('sale_items.id_produk', 'products.nama_produk', 'products.harga_pokok')
            ->selectRaw('sale_items.id_produk, products.nama_produk, products.harga_pokok,
                SUM(sale_items.qty) qty, SUM(sale_items.subtotal) omzet,
                SUM(sale_items.subtotal - products.harga_pokok * sale_items.qty) margin')
            ->orderByDesc('omzet')->get();
        $rows = $rowsRaw->map(fn ($x) => [
            $x->id_produk, $x->nama_produk, (string) $x->qty,
            number_format((float) $x->omzet, 0, '', ''), number_format((float) $x->margin, 0, '', ''),
        ]);
        $rekap = [
            ['Total Qty Terjual', (string) $rowsRaw->sum('qty')],
            ['Omzet Kotor', number_format((float) $rowsRaw->sum('omzet'), 0, '', '')],
            ['Total Margin (omzet - HPP)', number_format((float) $rowsRaw->sum('margin'), 0, '', '')],
        ];
        return $this->respond($r, 'OMSET POS PER JUDUL PRODUK', $dari, $sampai, $rekap,
            ['ID Produk', 'Nama Produk', 'Qty', 'Omzet', 'Margin'], $rows);
    }

    // ===== 7. Omzet per Departemen (hierarki) =====
    public function omzetDepartemen(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $rowsRaw = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id_produk', '=', 'sale_items.id_produk')
            ->join('departments', 'departments.id_departemen', '=', 'products.id_departemen')
            ->join('divisions', 'divisions.id_divisi', '=', 'departments.id_divisi')
            ->whereBetween(DB::raw('DATE(sales.sale_date)'), [$dari, $sampai])
            ->where('sales.payment_status', 'paid')
            ->groupBy('divisions.id_divisi', 'divisions.nama_divisi', 'departments.id_departemen', 'departments.nama_departemen')
            ->selectRaw('divisions.id_divisi, divisions.nama_divisi, departments.nama_departemen,
                SUM(sale_items.qty) qty, SUM(sale_items.subtotal) omzet')
            ->orderByDesc('omzet')->get();
        $rows = $rowsRaw->map(fn ($x) => [
            $x->id_divisi, $x->nama_divisi, $x->nama_departemen, (string) $x->qty,
            number_format((float) $x->omzet, 0, '', ''),
        ]);
        $rekap = [['Total Dept Terjual', (string) count($rowsRaw)]];
        return $this->respond($r, 'OMSET POS PER DEPARTEMEN', $dari, $sampai, $rekap,
            ['Kode Divisi', 'Divisi', 'Departemen', 'Qty', 'Omzet'], $rows);
    }

    // ===== 8. Omzet per Kasir =====
    public function omzetKasir(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $rowsRaw = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween(DB::raw('DATE(sales.sale_date)'), [$dari, $sampai])
            ->where('sales.payment_status', 'paid')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.name, COUNT(*) nota, SUM(sales.final_amount) omzet, SUM(sales.discount) diskon')
            ->orderByDesc('omzet')->get();
        $rows = $rowsRaw->map(fn ($x) => [
            $x->name, (string) $x->nota,
            number_format((float) $x->omzet, 0, '', ''), number_format((float) $x->diskon, 0, '', ''),
        ]);
        $rekap = [
            ['Total Kasir Aktif', (string) count($rowsRaw)],
            ['Omzet Bersih', number_format((float) $rowsRaw->sum('omzet'), 0, '', '')],
        ];
        return $this->respond($r, 'OMSET PER KASIR', $dari, $sampai, $rekap,
            ['Kasir', 'Jumlah Nota', 'Omzet Bersih', 'Total Diskon'], $rows);
    }

    // ===== 9. New Loyalty Member =====
    public function memberBaru(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('customers')->where('is_member', 1)
            ->whereBetween(DB::raw('DATE(member_since)'), [$dari, $sampai]);
        $rows = $q->get()->map(fn ($x) => [
            $x->id_anggota ?? ('MBR-' . $x->id), $x->name, $x->phone, $x->city ?? '-',
            strtoupper($x->gender ?? '-'), strtoupper($x->tier), (string) $x->points,
            number_format((float) $x->total_spent, 0, '', ''), (string) $x->member_since,
        ]);
        $rekap = [
            ['Member Baru Periode', (string) $q->count()],
            ['Gold', (string) $q->clone()->where('tier', 'gold')->count()],
            ['Silver', (string) $q->clone()->where('tier', 'silver')->count()],
        ];
        return $this->respond($r, 'NEW LOYALTY MEMBER', $dari, $sampai, $rekap,
            ['ID Anggota', 'Nama', 'No HP', 'Kota', 'Gender', 'Tier', 'Poin', 'Akumulasi Belanja', 'Sejak'], $rows);
    }

    // ===== 10. Riwayat Transaksi Member (Top Spender) =====
    public function memberRiwayat(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('customers')->where('is_member', 1)->orderByDesc('total_spent');
        $rows = $q->get()->map(fn ($x) => [
            $x->id_anggota ?? ('MBR-' . $x->id), $x->name, $x->phone, $x->city ?? '-',
            strtoupper($x->tier), (string) $x->points, (string) $x->total_orders,
            number_format((float) $x->total_spent, 0, '', ''),
        ]);
        $rekap = [
            ['Total Member', (string) $q->count()],
            ['Total Akumulasi Belanja', number_format((float) $q->clone()->sum('total_spent'), 0, '', '')],
        ];
        return $this->respond($r, 'RIWAYAT & TOP SPENDER MEMBER', $dari, $sampai, $rekap,
            ['ID Anggota', 'Nama', 'No HP', 'Kota', 'Tier', 'Poin', 'Jumlah Transaksi', 'Akumulasi Belanja'], $rows);
    }

    // ===== 11. Rekap Penutupan Kasir / TILL (Agregat Harian) =====
    public function tillClosing(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        
        // Ambil data shift & penutupan laci
        $shifts = DB::table('cashier_shifts')
            ->join('users', 'users.id', '=', 'cashier_shifts.id_pengguna')
            ->leftJoin('daily_closings', 'daily_closings.id_sesi_kasir', '=', 'cashier_shifts.id_sesi_kasir')
            ->whereBetween(DB::raw('DATE(cashier_shifts.waktu_buka)'), [$dari, $sampai])
            ->select('cashier_shifts.id_sesi_kasir', 'users.name as kasir', 'cashier_shifts.nomor_kasir', 
                     'cashier_shifts.waktu_buka', 'cashier_shifts.waktu_tutup',
                     'daily_closings.modal_awal', 'daily_closings.uang_fisik', 'daily_closings.selisih')
            ->orderBy('cashier_shifts.waktu_buka')
            ->get();

        $rows = [];
        $totalOmzetHarian = 0;
        $totalMetode = ['TUNAI' => 0, 'DEBIT' => 0, 'KARTU KREDIT' => 0, 'LAINNYA' => 0];

        foreach ($shifts as $s) {
            // Ambil breakdown transaksi per shift
            $sales = DB::table('sales')
                ->where('id_sesi_kasir', $s->id_sesi_kasir)
                ->where('payment_status', 'paid')
                ->selectRaw('payment_method, SUM(final_amount) total')
                ->groupBy('payment_method')
                ->get();

            $shiftOmzet = 0;
            foreach ($sales as $sl) {
                $m = $this->mapMethod($sl->payment_method);
                $shiftOmzet += $sl->total;
                $totalMetode[$m] = ($totalMetode[$m] ?? 0) + $sl->total;
            }

            $totalOmzetHarian += $shiftOmzet;

            // Baris Shift
            $rows[] = [
                $s->id_sesi_kasir,
                $s->waktu_buka . ' - ' . ($s->waktu_tutup ?? 'AKTIF'),
                $s->kasir,
                $s->nomor_kasir,
                'TOTAL SHIFT',
                number_format((float) ($s->modal_awal ?? 0), 0, '', ''),
                number_format((float) $shiftOmzet, 0, '', ''),
                number_format((float) ($s->uang_fisik ?? 0), 0, '', ''),
                number_format((float) ($s->selisih ?? 0), 0, '', ''),
            ];

            // Baris Detail Metode per Shift
            foreach ($sales as $sl) {
                $rows[] = [
                    '', '', '', '', 
                    '↳ ' . $this->mapMethod($sl->payment_method),
                    '',
                    number_format((float) $sl->total, 0, '', ''),
                    '', ''
                ];
            }
        }

        $rekap = [
            ['Total Omzet Harian', number_format((float) $totalOmzetHarian, 0, '', '')],
            ['Total Tunai', number_format((float) $totalMetode['TUNAI'], 0, '', '')],
            ['Total Debit/QRIS', number_format((float) $totalMetode['DEBIT'], 0, '', '')],
            ['Total Kartu Kredit', number_format((float) $totalMetode['KARTU KREDIT'], 0, '', '')],
        ];

        return $this->respond($r, 'REKAP TILL & SHIFT HARIAN (AGREGAT)', $dari, $sampai, $rekap,
            ['Sesi', 'Waktu', 'Kasir', 'Laci', 'Keterangan/Metode', 'Modal', 'Omzet', 'Fisik', 'Selisih'], $rows);
    }

    // ===== 12. Penutupan Acara / Bon Pengembalian =====
    public function eventClosing(Request $r)
    {
        [$dari, $sampai] = $this->period($r);
        $q = DB::table('event_closings')
            ->join('events', 'events.id_acara', '=', 'event_closings.id_acara')
            ->whereBetween('event_closings.tanggal_selesai', [$dari, $sampai]);
        $rows = $q->get()->map(fn ($x) => [
            $x->id_penutupan_acara, $x->id_acara, $x->nama_acara, $x->id_produk ?? 'ALL',
            (string) $x->jumlah_sisa_produk, $x->nomor_bon_pengembalian ?? '-',
            (string) ($x->tanggal_pengembalian ?? '-'), (string) $x->tanggal_selesai,
            strtoupper($x->status_acara),
        ]);
        $rekap = [
            ['Total Bon Pengembalian', (string) $q->count()],
            ['Total Sisa Produk Balik Gudang', (string) $q->clone()->sum('event_closings.jumlah_sisa_produk')],
        ];
        return $this->respond($r, 'PENUTUPAN ACARA / BON PENGEMBALIAN', $dari, $sampai, $rekap,
            ['No Penutupan', 'ID Acara', 'Nama Acara', 'ID Produk', 'Qty Sisa', 'No Bon', 'Tgl Bon', 'Tgl Selesai', 'Status'], $rows);
    }

    // ===== helper =====
    private function mapMethod(string $m): string
    {
        return match ($m) {
            'cash' => 'TUNAI',
            'qris' => 'DEBIT',
            'transfer' => 'KARTU KREDIT',
            default => strtoupper($m),
        };
    }

    /**
     * Bungkusan 3-zone (metadata -> rekap -> detail).
     * ?export=csv -> stream php://output per baris, angka murni.
     */
    private function respond(Request $r, string $judul, string $dari, string $sampai, array $rekap, array $header, $rows)
    {
        if ($r->query('export') === 'csv') {
            $namaFile = strtolower(str_replace(' ', '-', $judul)) . '-' . $dari . '-' . $sampai . '.csv';
            $stream = function () use ($judul, $dari, $sampai, $rekap, $header, $rows) {
                $h = fopen('php://output', 'w');
                fwrite($h, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8 buat Excel
                fputcsv($h, ['PT. BUKU KITA', $judul, "Periode: $dari s/d $sampai", 'Dicetak: ' . now()->format('d/m/Y H:i')]);
                fputcsv($h, []);
                fputcsv($h, ['REKAPITULASI']);
                foreach ($rekap as [$label, $val]) {
                    fputcsv($h, [$label, $val]);
                }
                fputcsv($h, []);
                fputcsv($h, ['DETAIL']);
                fputcsv($h, $header);
                foreach ($rows as $row) {
                    fputcsv($h, $row);
                }
                fclose($h);
            };
            return response()->stream($stream, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $namaFile . '"',
            ]);
        }
        return response()->json([
            'metadata' => ['judul' => $judul, 'dari' => $dari, 'sampai' => $sampai, 'dicetak' => now()->toDateTimeString()],
            'rekapitulasi' => array_map(fn ($x) => ['label' => $x[0], 'value' => $x[1]], $rekap),
            'header' => $header,
            'rows' => $rows,
        ]);
    }
}
