<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'phone', 'email', 'date_of_birth', 'gender',
        'address', 'city', 'postal_code', 'total_spent', 'total_orders',
        'points', 'member_since', 'is_member', 'tier', 'id_anggota'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'member_since' => 'date',
        'total_spent' => 'decimal:2',
        'is_member' => 'boolean',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function loyaltyPoints()
    {
        return $this->hasMany(LoyaltyPoint::class);
    }
}
