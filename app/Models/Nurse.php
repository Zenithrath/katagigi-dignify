<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nurse extends Model
{
    use HasFactory;

    protected $table = 'nurses';

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nipp',
        'niptk',
        'profile_picture',
        'cover_picture',
    ];

    /**
     * Get the nurse that owns the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * Get the nurse associated with the transaction_nurse.
     */
    public function transaction_nurse(): HasMany
    {
        return $this->hasMany(TransactionNurse::class, 'id', 'nurse_id');
    }
}
