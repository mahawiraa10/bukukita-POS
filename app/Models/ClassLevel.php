<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassLevel extends Model
{
    protected $table = 'classes';
    protected $primaryKey = 'id_kelas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function subclasses()
    {
        return $this->hasMany(Subclass::class, 'id_kelas', 'id_kelas');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'id_departemen', 'id_departemen');
    }
}
