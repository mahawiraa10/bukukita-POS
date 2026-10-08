<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';
    protected $primaryKey = 'id_perusahaan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
