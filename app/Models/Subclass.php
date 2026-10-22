<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subclass extends Model
{
    use SoftDeletes;
    
    protected $table = 'subclasses';
    protected $primaryKey = 'id_subkelas';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function kelas()
    {
        return $this->belongsTo(ClassLevel::class, 'id_kelas', 'id_kelas');
    }
}
