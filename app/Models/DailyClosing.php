<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    protected $table = 'daily_closings';
    protected $primaryKey = 'id_penutupan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
