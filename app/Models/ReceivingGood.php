<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivingGood extends Model
{
    protected $table = 'receiving_goods';
    protected $primaryKey = 'id_penerimaan';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(ReceivingItem::class, 'id_penerimaan', 'id_penerimaan');
    }
}
