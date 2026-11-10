<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id_produk';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_produk', 'sku', 'id_pemasok', 'id_divisi', 'id_departemen', 'id_kelas', 'id_subkelas',
        'nama_produk', 'deskripsi_produk', 'penerbit', 'isbn', 'foto_produk', 'harga_jual',
        'harga_beli', 'harga_pokok', 'diskon_base', 'stok', 'min_stock', 'is_active'
    ];
    protected $appends = ['kategori'];

    public function subkelas()
    {
        return $this->belongsTo(Subclass::class, 'id_subkelas', 'id_subkelas');
    }

    public function kelas()
    {
        return $this->belongsTo(ClassLevel::class, 'id_kelas', 'id_kelas');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_departemen', 'id_departemen');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_divisi', 'id_divisi');
    }

    // ponytail: kategori = rantai 4 level, dipisah " / ". Null di tengah di-skip
    // biar "Fiksi / / Romance / Teen" gak muncul. Kasir pakai ini buat sortir.
    public function getKategoriAttribute(): string
    {
        return collect([
            $this->division?->nama_divisi,
            $this->department?->nama_departemen,
            $this->kelas?->nama_kelas,
            $this->subkelas?->nama_subkelas,
        ])->filter()->values()->join(' / ');
    }
}
