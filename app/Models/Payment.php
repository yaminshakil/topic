<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['employee_id', 'period', 'amount', 'paid_on', 'note'];

    protected function casts(): array
    {
        return [
            'amount'  => 'decimal:2',
            'paid_on' => 'date',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
