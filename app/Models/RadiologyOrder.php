<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyOrder extends Model
{
    use HasFactory;

    protected $table = 'radiology_orders';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'patient_id',
        'ordered_by',
        'modality',
        'body_site',
        'clinical_indication',
        'priority',
        'status',
        'performed_at',
        'result_text',
        'result_path',
        'satusehat_diagnostic_report_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
        ];
    }

    public const STATUS_ORDERED = 'ORDERED';

    public const STATUS_SCHEDULED = 'SCHEDULED';

    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_CANCELLED = 'CANCELLED';

    /** Kode modalitas DICOM yang dipakai SATUSEHAT ServiceRequest.imagingStudy. */
    public const MODALITIES = [
        'DX' => 'Radiografi Digital',
        'CR' => 'Computed Radiography',
        'DR' => 'Direct Radiography',
        'CT' => 'CT Scan',
        'MG' => 'Mammografi',
        'US' => 'Ultrasonografi',
        'MR' => 'MRI',
    ];

    /** Status yang boleh diset saat mengisi hasil (ORDERED tidak bisa dipilih balik). */
    public const RESULT_STATUSES = [
        self::STATUS_SCHEDULED => 'Terjadwal',
        self::STATUS_IN_PROGRESS => 'Diproses',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    public const STATUSES = [self::STATUS_ORDERED => 'Dipesan'] + self::RESULT_STATUSES;

    public const PRIORITIES = ['routine' => 'Rutin', 'urgent' => 'Segera', 'stat' => 'STAT'];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class, 'visit_id', 'id');
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'ordered_by', 'user_id');
    }
}
