<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'spare_part_id',
        'description',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    // Detail adalah bagian dari satu Invoice
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Detail menunjuk ke satu SparePart (bisa null)
    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }
}