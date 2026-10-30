<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SpotPlayerLicense extends Model
{
    protected $fillable = [
        'course_id',
        'provider_course_id',
        'provider_mode',
        'student_id',
        'external_license_id',
        'license_key',
        'license_url',
        'status',
        'issued_at',
    ];

    protected function casts(): array
    {
        return [
            'license_key' => 'encrypted',
            'issued_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
