<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberTransaction extends Model
{
    protected $table = 'member_transactions';
    protected $primaryKey = 'id_transaksi_anggota';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
