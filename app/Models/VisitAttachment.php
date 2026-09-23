<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitAttachment extends Model
{
    use HasFactory;

    protected $table = 'visit_attachments';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPES = [
        'INTRAORAL' => 'Intraoral',
        'EXTRAORAL' => 'Ekstraoral',
        'XRAY' => 'Rontgen',
        'DOCUMENT' => 'Dokumen',
        'OTHER' => 'Lainnya',
    ];

    protected $fillable = [
        'id',
        'visit_id',
        'type',
        'path',
        'description',
        'uploaded_by',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }
}
