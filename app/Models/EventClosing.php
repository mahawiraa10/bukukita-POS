<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventClosing extends Model
{
    protected $table = 'event_closings';
    protected $primaryKey = 'id_penutupan_acara';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
