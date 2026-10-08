<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id_acara';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
