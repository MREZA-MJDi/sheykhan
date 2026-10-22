<?php

return [
    // Real legacy identities are opt-in. Normal db:seed never imports people
    // unless an operator explicitly enables this setting for the intended DB.
    'import_on_db_seed' => (bool) env('SHEYKHAN_IMPORT_LEGACY_STUDENTS', false),

    // Keep the source outside public/ and outside Git. The original SQL contains
    // personal data and password hashes and must never be committed to the repo.
    'source_path' => env(
        'SHEYKHAN_LEGACY_STUDENTS_SQL_PATH',
        storage_path('app/private/legacy-students.sql'),
    ),

    'academy_id' => env('SHEYKHAN_LEGACY_ACADEMY_ID'),

    // Temporary password for the 54 verified WordPress bcrypt accounts.
    // Configure only in the private .env and force a change immediately after login.
    'temporary_password' => env('SHEYKHAN_LEGACY_STUDENT_TEMP_PASSWORD'),
];
