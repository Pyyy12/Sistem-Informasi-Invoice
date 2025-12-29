<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id', 'vehicle_id', 'invoice_number', 'invoice_date', 
        'total_amount', 'status', 'notes',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}