<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'divisions';
    protected $primaryKey = 'id_divisi';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function departments()
    {
        return $this->hasMany(Department::class, 'id_divisi', 'id_divisi');
    }
}
