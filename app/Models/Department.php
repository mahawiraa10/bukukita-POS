<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id_departemen';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function classes()
    {
        return $this->hasMany(ClassLevel::class, 'id_departemen', 'id_departemen');
    }

    public function division()
    {
        return $this->belongsTo(Division::class, 'id_divisi', 'id_divisi');
    }
}
