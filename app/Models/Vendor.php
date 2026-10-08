<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $table = 'vendors';
    protected $primaryKey = 'id_pemasok';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
