<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorReturn extends Model
{
    protected $table = 'vendor_returns';
    protected $primaryKey = 'id_pengembalian';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
