<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id_pemasok';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_pemasok', 'nama_pemasok', 'alamat_lengkap', 'nomor_telepon_pemasok',
        'email_pemasok', 'diskon_pemasok', 'nomor_rekening', 'is_active', 'id_divisi'
    ];
}
