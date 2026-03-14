<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnInvoiceItem extends Model
{
    protected $fillable = [
        'return_invoice_id',
        'medicine_id',
        'batch_id',
        'invoice_item_id',
        'quantity',
        'unit_price',
        'total_amount',
        'unit',
        'reason',
    ];

    public function returnInvoice(): BelongsTo
    {
        return $this->belongsTo(ReturnInvoice::class);
    }
    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }
    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}