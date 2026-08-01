<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_INVOICED = 'invoiced';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'document_type_id',
        'customer_id',
        'title',
        'reference',
        'status',
        'version',
        'parent_id',
        'json_data',
        'html_snapshot',
        'pdf_path',
    ];

    protected $casts = [
        'json_data' => 'array',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function convertedInvoice(): HasOne
    {
        return $this->hasOne(Document::class, 'parent_id');
    }

    public function canBeEdited(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canBeConverted(): bool
    {
        return $this->isQuote()
            && $this->status === self::STATUS_ACCEPTED
            && ! $this->convertedInvoice()->exists();
    }

    public function isQuote(): bool
    {
        return $this->documentType?->slug === 'quote';
    }

    public function isInvoice(): bool
    {
        return $this->documentType?->slug === 'invoice';
    }
}