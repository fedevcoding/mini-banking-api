<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';

    protected $fillable = [
        'owner_name',
        'currency',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function getBalance(): float
    {
        return (float) $this->transactions()
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'deposit'    THEN amount ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN type = 'withdrawal' THEN amount ELSE 0 END), 0) AS balance
            ")
            ->value('balance');
    }
}