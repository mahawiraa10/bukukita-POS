<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    protected $table = 'refunds';
    protected $primaryKey = 'id_pengembalian';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'id_pengembalian', 'id_produk', 'id_transaksi_referensi', 'tanggal_pengembalian',
        'jumlah_pengembalian', 'sumber_pengembalian', 'jumlah_dikembalikan', 'jumlah_nilai'
    ];
}
