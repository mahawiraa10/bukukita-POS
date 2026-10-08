<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductLoad extends Model
{
    protected $table = 'product_loads';
    protected $primaryKey = 'id_load';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_produk', 'id_produk');
    }
}
