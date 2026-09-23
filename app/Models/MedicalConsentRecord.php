<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalConsentRecord extends Model
{
    use HasFactory;

    protected $table = 'medical_consent_records';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'visit_id',
        'patient_id',
        'doctor_id',
        'consent_type',
        'consent_text',
        'granted',
        'granted_by_name',
        'granted_by_relation',
        'granted_at',
        'signature_path',
        'notes',
    ];

    public const TYPES = [
        'treatment' => 'Tindakan Kedokteran Gigi',
        'surgery' => 'Tindakan Bedah / Ekstraksi',
        'anesthesia' => 'Anestesi',
        'radiology' => 'Pemeriksaan Radiologi',
        'data_release' => 'Pelepasan Data Medis',
        'general' => 'Persetujuan Umum',
    ];

    public const RELATIONS = [
        'self' => 'Pasien Sendiri',
        'parent' => 'Orang Tua',
        'spouse' => 'Suami/Istri',
        'child' => 'Anak',
        'guardian' => 'Wali',
        'other' => 'Lainnya',
    ];

    /** Teks baku informed consent; dokter boleh menyunting sebelum dicatat. */
    public const DEFAULT_TEXT = 'Saya menyatakan telah menerima penjelasan yang jelas mengenai diagnosis, rencana tindakan kedokteran gigi, manfaat, risiko, komplikasi yang mungkin terjadi, serta alternatif tindakan. Saya memahami bahwa hasil tindakan tidak dapat dijamin dan saya diberi kesempatan untuk bertanya. Dengan ini saya memberikan persetujuan secara sadar dan tanpa paksaan.';

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
            'granted_at' => 'datetime',
        ];
    }

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
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }
}
