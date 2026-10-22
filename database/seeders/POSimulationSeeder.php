<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;
use App\Models\Department;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Product;
use App\Services\POService;
use App\Support\IdGen;

/**
 * Bootstrap dari NOL: hierarki -> vendor -> PO (produk baru lahir di PO)
 * -> approve -> receive partial -> receive sisa. Verifikasi traceability.
 */
class POSimulationSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('=== SIMULASI DARI NOL (PRODUK LAHIR DARI PO) ===');

        $admin = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->first();
        $supervisor = User::whereHas('role', fn($q) => $q->where('name', 'supervisor'))->first();

        if (!$admin || !$supervisor) {
            $this->command->error('User admin/supervisor belum ada.');
            return;
        }

        // 1. Hierarki
        $division = Division::firstOrCreate(
            ['nama_divisi' => 'BUKU'],
            ['id_divisi' => IdGen::next(Division::class, 'id_divisi', 'DIV')]
        );
        $department = Department::firstOrCreate(
            ['nama_departemen' => 'Buku Umum', 'id_divisi' => $division->id_divisi],
            ['id_departemen' => IdGen::next(Department::class, 'id_departemen', 'DEP')]
        );
        $this->command->info("Hierarki: {$division->id_divisi} > {$department->id_departemen}");

        // 2. Vendor (wajib id_divisi)
        $vendor = Vendor::firstOrCreate(
            ['email_pemasok' => 'vendor.simulasi@bukukita.test'],
            [
                'id_pemasok'            => IdGen::next(Vendor::class, 'id_pemasok', 'VND'),
                'nama_pemasok'          => 'Penerbit Simulasi Jaya',
                'alamat_lengkap'        => 'Jl. Margonda Raya No. 88, Depok',
                'nomor_telepon_pemasok' => '02177889900',
                'diskon_pemasok'        => 15,
                'id_divisi'             => $division->id_divisi,
                'is_active'             => true,
            ]
        );
        $this->command->info("Vendor: {$vendor->nama_pemasok} ({$vendor->id_pemasok})");

        // 3. Admin buat PO dengan PRODUK BARU (bootstrap, tanpa produk existing)
        $poService = app(POService::class);
        $po = $poService->createPo([
            'id_pemasok' => $vendor->id_pemasok,
            'tipe_po'    => 'reguler',
            'items' => [
                [
                    'qty'         => 20,
                    'unit_price'  => 90000,
                    'new_product' => [
                        'nama_produk'   => 'Buku Panduan Laravel 12',
                        'id_departemen' => $department->id_departemen,
                        'penerbit'      => 'Simulasi Jaya',
                        'harga_jual'    => 125000,
                    ],
                ],
            ],
        ], $admin->id);

        $produkBaru = Product::latest('created_at')->first();
        $this->command->info("1. PO {$po->po_code} dibuat — status: {$po->status}");
        $this->command->info("   Produk lahir: {$produkBaru->nama_produk} ({$produkBaru->id_produk}) stok: {$produkBaru->stok}");

        // 4. Supervisor approve
        $poService->approve($po->id);
        $this->command->info("2. Approved — status: {$po->fresh()->status}");

        // 5. Receive partial
        $item = $po->items()->first();
        $poService->receive($po->id, [['id' => $item->id, 'received_qty' => 12]]);
        $this->command->info("3. Receive 12 pcs — status: {$po->fresh()->status}, stok: {$produkBaru->fresh()->stok}");

        // 6. Receive sisa
        $poService->receive($po->id, [['id' => $item->id, 'received_qty' => 8]]);
        $this->command->info("4. Receive 8 pcs — status: {$po->fresh()->status}, stok: {$produkBaru->fresh()->stok}");

        $this->command->info('=== SELESAI: produk ada karena PO, bukan input manual ===');
    }
}
