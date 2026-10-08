<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $table = 'stock_mutations';
    protected $primaryKey = 'id_perubahan_stok';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
