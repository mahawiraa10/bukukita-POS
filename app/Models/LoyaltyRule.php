<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyRule extends Model
{
    protected $table = 'loyalty_rules';
    protected $primaryKey = 'id_poin';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
}
