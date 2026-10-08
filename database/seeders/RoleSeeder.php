<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'owner',      'description' => 'Akses penuh ke seluruh sistem, laporan eksekutif, & konfigurasi'],
            ['name' => 'supervisor', 'description' => 'Supervisi operasional, approve PO/Retur, void transaksi, & rekap closing'],
            ['name' => 'admin',      'description' => 'Input & kelola master barang (produk, vendor, hierarki), PO & receiving (dari vendor / gudang internal)'],
            ['name' => 'kasir',      'description' => 'Operasional POS kasir, input transaksi, & cetak struk'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $permissions = [
            ['name' => 'view_dashboard',   'description' => 'Lihat dashboard eksekutif'],
            ['name' => 'create_sale',      'description' => 'Input transaksi POS'],
            ['name' => 'manage_customers', 'description' => 'Kelola customer'],
            ['name' => 'manage_books',     'description' => 'Kelola katalog produk/barang'],
            ['name' => 'manage_vendors',   'description' => 'Kelola vendor'],
            ['name' => 'manage_po',        'description' => 'Kelola purchase order & receiving (vendor/gudang)'],
            ['name' => 'approve_po',       'description' => 'Approve purchase order'],
            ['name' => 'manage_users',     'description' => 'Kelola user & role'],
            ['name' => 'view_reports',     'description' => 'Akses laporan operasional'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission['name']], $permission);
        }

        // Owner: semua permission
        $owner = Role::where('name', 'owner')->first();
        $owner->permissions()->sync(Permission::all());

        // Supervisor: Dashboard, approve PO, view reports, manage customers
        $supervisor = Role::where('name', 'supervisor')->first();
        $supervisor->permissions()->sync(
            Permission::whereIn('name', ['view_dashboard', 'approve_po', 'manage_customers', 'view_reports'])->get()
        );

        // Admin: Input barang, vendor, PO/receiving (dari vendor/gudang), kelola customer
        $admin = Role::where('name', 'admin')->first();
        $admin->permissions()->sync(
            Permission::whereIn('name', ['manage_books', 'manage_vendors', 'manage_po', 'manage_customers'])->get()
        );

        // Kasir: transaksi + kelola customer
        $kasir = Role::where('name', 'kasir')->first();
        $kasir->permissions()->sync(
            Permission::whereIn('name', ['create_sale', 'manage_customers'])->get()
        );
    }
}
