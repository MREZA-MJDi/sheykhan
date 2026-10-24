<?php

namespace App\Services;

use App\Models\Classroom;
use Illuminate\Support\Str;
use RuntimeException;

final class ClassroomCodeGenerator
{
    /**
     * Produce a short human-readable code without asking the teacher/owner to invent one.
     * Global uniqueness is stricter than the existing academy-scoped rule and avoids ambiguity
     * when students or support staff search for a code without the academy context.
     */
    public function generate(): string
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = 'SHK-' . strtoupper(Str::random(8));

            if (! Classroom::query()->where('code', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new RuntimeException('Unable to generate a unique classroom code after repeated attempts.');
    }

    /**
     * Keep compatibility with old/manual clients, but always recover from missing or
     * colliding values on the server before the classroom write happens.
     */
    public function resolve(?string $candidate = null): string
    {
        $candidate = strtoupper(trim((string) $candidate));

        if ($candidate !== '' && ! Classroom::query()->where('code', $candidate)->exists()) {
            return $candidate;
        }

        return $this->generate();
    }
}
