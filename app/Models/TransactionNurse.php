<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionNurse extends Model
{
    use HasFactory;

    protected $table = 'transaction_nurses';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'nurse_id',
        'nurse_name',
        'transaction_id',
        'price',
    ];

    /**
     * Get the transaction_nurse that owns the transaction.
     */
    public function nurse(): BelongsTo
    {
        return $this->belongsTo(Nurse::class, 'nurse_id', 'user_id');
    }

    /**
     * Get the transaction_nurse that owns the transaction.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }
}
