<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    protected $table = 'cashier_shifts';
    protected $primaryKey = 'id_sesi_kasir';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
