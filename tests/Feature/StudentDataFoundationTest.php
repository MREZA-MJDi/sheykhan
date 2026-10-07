<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentDataFoundationTest extends TestCase
{
    use RefreshDatabase;

    public function test_identity_student_and_audit_schema_is_import_ready(): void
    {
        $this->assertTrue(Schema::hasColumns('users', [
            'mobile',
            'status',
            'deleted_at',
            'legacy_source',
            'legacy_id',
        ]));

        $this->assertTrue(Schema::hasColumns('student_profiles', [
            'user_id',
            'student_number',
            'grade_id',
            'national_id_lookup',
            'national_id_encrypted',
            'registration_source',
            'registered_by',
            'onboarded_at',
            'status',
        ]));

        $this->assertTrue(Schema::hasColumns('audit_logs', [
            'actor_id',
            'action',
            'subject_type',
            'subject_id',
            'old_values',
            'new_values',
        ]));
    }
}
