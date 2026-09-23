<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NurseAttendance extends Model
{
    use HasFactory;

    protected $table = 'nurse_attendances';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'scheduled_end',
        'note',
        'input_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'user_id', 'user_id');
    }
}
