<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'employee_id',
    'department',
    'position',
    'phone',
    'office',
    'date_hired',
    'photo_path',
])]
class EmployeeProfile extends Model
{
    protected function casts(): array
    {
        return [
            'date_hired' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
