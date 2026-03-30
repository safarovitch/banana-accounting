<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierOrder extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'order_date' => 'date',
        'received_date' => 'date',
    ];

    public function payments()
    {
        return $this->hasMany(SupplierPayment::class);
    }
}
