<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_code', 'id_pemasok', 'id_acara', 'tipe_po', 'user_id', 'status',
        'total_amount', 'notes', 'expected_date', 'received_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'id_pemasok', 'id_pemasok');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_acara', 'id_acara');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(PoItem::class);
    }
}