<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorFeeRule extends Model
{
    use HasFactory;

    protected $table = 'doctor_fee_rules';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'doctor_id',
        'percentage',
        'xray_percentage',
        'shift_allowance',
    ];

    protected function casts(): array
    {
        return [
            'percentage' => 'float',
            'xray_percentage' => 'float',
            'shift_allowance' => 'float',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id', 'user_id');
    }
}
