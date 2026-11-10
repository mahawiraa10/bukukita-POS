<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_code', 'customer_id', 'user_id', 'total_amount',
        'discount', 'final_amount', 'payment_method',
        'payment_status', 'sale_date', 'notes',
        'id_sesi_kasir', 'id_acara', 'business_date',
        'original_sale_id'
    ];

    protected $casts = [
        'sale_date' => 'datetime',
        'business_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'id_acara', 'id_acara');
    }

    public function originalSale()
    {
        return $this->belongsTo(Sale::class, 'original_sale_id');
    }
}
