<?php

namespace App\Console\Commands;

use App\Models\Academy;
use App\Models\Role;
use App\Services\LegacyWordPressPassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class ImportLegacyStudents extends Command
{
    protected $signature = 'sheykhan:import-legacy-students
        {source : Path to the privately stored original WordPress SQL export}
        {--academy-id= : Existing active Academy to attach the imported identities to}
        {--apply : Apply the import; without this flag the command is dry-run only}
        {--block-unlisted : Also block other WordPress-source accounts outside this verified set}';

    protected $description = 'Safely import the 55 verified WordPress students without executing destructive SQL from the source dump';

    private const EXPECTED_COUNT = 55;

    /**
     * Approved identities from the verified legacy export allow-list.
     * A different set of 55 rows must never be silently imported.
     */
    private const EXPECTED_LEGACY_IDS = [
        8, 58, 101, 109, 110, 112, 113, 114, 115, 116, 117, 118, 119, 120,
        121, 122, 123, 124, 125, 126, 127, 128, 129, 130, 131, 132, 133, 134,
        135, 136, 137, 139, 140, 141, 142, 143, 144, 145, 146, 147, 148, 149,
        150, 151, 152, 153, 154, 155, 156, 157, 158, 159, 160, 161, 162,
    ];

    private const USER_COLUMNS = [
        'name', 'email', 'password', 'mobile', 'status',
        'legacy_source', 'legacy_id', 'created_at', 'updated_at',
    ];

    public function handle(LegacyWordPressPassword $legacyPasswords): int
    {
        $path = (string) $this->argument('source');

        if (! is_file($path) || ! is_readable($path)) {
            $this->error('فایل منبع وجود ندارد یا قابل خواندن نیست.');
            return self::FAILURE;
        }

        $missing = $this->requiredSchemaGaps();
        if ($missing !== []) {
            $this->error('اسکیما آماده نیست: ' . implode(', ', $missing));
            $this->line('ابتدا Migrationهای پروژه را اجرا کن.');
            return self::FAILURE;
        }

        try {
            $rows = $this->extractVerifiedStudents((string) file_get_contents($path), $legacyPasswords);
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $academyId = filter_var($this->option('academy-id'), FILTER_VALIDATE_INT);
        $academy = $academyId
            ? Academy::query()->whereKey($academyId)->where('status', 'active')->first()
            : null;

        if (! $academy) {
            $this->error('An existing active Academy is required. Pass --academy-id; no academy will be guessed or created.');
            return self::FAILURE;
        }

        $conflicts = $this->emailConflicts($rows);
        if ($conflicts !== []) {
            $this->error('ایمیل بعضی رکوردها به حسابی با هویت دیگری تعلق دارد؛ برای جلوگیری از ادغام ناخواسته، هیچ داده‌ای تغییر نکرد.');
            $this->line('شناسه‌های legacy متعارض: ' . implode(', ', $conflicts));
            $this->line('این موارد باید دستی بررسی و به هویت درست نگاشت شوند؛ دستور حساب موجود را تصاحب نمی‌کند.');
            return self::FAILURE;
        }

        $existingCount = 0;
        foreach ($rows as $row) {
            if ($this->findByLegacyIdentity($row['legacy_id'])) {
                $existingCount++;
            }
        }

        foreach ($rows as $row) {
            $existing = $this->findByLegacyIdentity((int) $row['legacy_id']);
            if ($existing) {
                $membership = DB::table('academy_user')
                    ->where('academy_id', $academy->id)
                    ->where('user_id', $existing->id)
                    ->first(['role']);
                if ($membership && $membership->role !== 'student') {
                    $this->error('An existing identity has a non-student membership in the target academy. No data was changed.');
                    return self::FAILURE;
                }
            }
        }

        $newCount = count($rows) - $existingCount;
        $unlistedCount = $this->unlistedWordPressUsersQuery(array_column($rows, 'legacy_id'))->count();
        $this->info('Target Academy: ' . $academy->name . ' (#' . $academy->id . ').');
        $this->info('Other WordPress-source rows outside the verified set: ' . $unlistedCount . '.');
        $this->info('منبع معتبر: ' . count($rows) . ' دانش‌آموز یکتا.');
        $this->line('حساب جدید: ' . $newCount);
        $this->line('هویت legacy موجود: ' . $existingCount);
        $this->line('حساب‌های دارای رمز موقت و اجبار تغییر در اولین ورود: ' . collect($rows)->where('password_kind', 'wp-bcrypt')->count());
        $this->line('حساب‌های phpass که رمز قبلی خود را حفظ می‌کنند: ' . collect($rows)->where('password_kind', 'phpass')->count());
        $this->line($this->option('block-unlisted')
            ? 'Optional cleanup enabled: other WordPress-source users will lose the student role, have student memberships archived and be blocked; their profiles will be preserved.'
            : 'No DELETE or account blocking is enabled by default.');

        if (! $this->option('apply')) {
            $this->comment('Dry-run است؛ دیتابیس تغییر نکرد. برای اعمال صریحاً گزینه --apply را اضافه کن.');
            return self::SUCCESS;
        }

        $temporaryPassword = config('legacy_students.temporary_password');
        $forcedPasswordCount = collect($rows)->where('password_kind', 'wp-bcrypt')->count();
        if ($forcedPasswordCount > 0 && (! is_string($temporaryPassword) || strlen($temporaryPassword) < 8)) {
            $this->error('برای اعمال انتقال، مقدار SHEYKHAN_LEGACY_STUDENT_TEMP_PASSWORD باید در فایل خصوصی .env تنظیم شده و حداقل ۸ کاراکتر داشته باشد.');
            $this->line('برای محیط واقعی از رمز موقت قوی و محدود به این مهاجرت استفاده کن؛ این مقدار را در Git ثبت نکن.');
            return self::FAILURE;
        }

        $result = [
            'source' => 'wordpress',
            'source_count' => count($rows),
            'created_users' => 0,
            'existing_users' => 0,
            'created_profiles' => 0,
            'student_role_links' => 0,
            'blocked_users' => 0,
            'forced_password_changes' => 0,
        ];

        DB::transaction(function () use ($rows, &$result, $academy, $temporaryPassword): void {
            $now = now();
            $studentRoleId = DB::table('roles')->where('slug', 'student')->value('id');

            if (! $studentRoleId) {
                $studentRoleId = DB::table('roles')->insertGetId([
                    'name' => 'دانش‌آموز',
                    'slug' => 'student',
                    'description' => 'دانش‌آموز واردشده از سامانه قبلی',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($rows as $row) {
                $user = $this->findByLegacyIdentity($row['legacy_id']);

                if ($user) {
                    // The verified source set is the authoritative active-student
                    // allow-list. Reactivate those 55 identities, but preserve a
                    // password already upgraded and contact details corrected later.
                    $userUpdate = ['status' => 'active', 'updated_at' => $now];
                    if (Schema::hasColumn('users', 'deleted_at')) {
                        $userUpdate['deleted_at'] = null;
                    }
                    DB::table('users')->where('id', $user->id)->update($userUpdate);

                    $result['existing_users']++;
                    $userId = (int) $user->id;
                } else {
                    $userId = (int) DB::table('users')->insertGetId([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        // The 54 verified WordPress bcrypt accounts receive the
                        // private, configured temporary password and are gated to
                        // the mandatory change screen after first login. The single
                        // phpass account keeps its legacy hash and existing login path.
                        'password' => $row['password_kind'] === 'wp-bcrypt'
                            ? Hash::make((string) $temporaryPassword)
                            : $row['password'],
                        'mobile' => $row['mobile'],
                        'status' => 'active',
                        'must_change_password' => $row['password_kind'] === 'wp-bcrypt',
                        'legacy_source' => 'wordpress',
                        'legacy_id' => $row['legacy_id'],
                        'email_verified_at' => null,
                        'created_at' => $row['created_at'] ?: $now,
                        'updated_at' => $now,
                    ]);
                    $result['created_users']++;
                    if ($row['password_kind'] === 'wp-bcrypt') {
                        $result['forced_password_changes']++;
                    }
                }

                $result['student_role_links'] += DB::table('role_user')->insertOrIgnore([
                    'role_id' => $studentRoleId,
                    'user_id' => $userId,
                ]);

                $profileExists = DB::table('student_profiles')->where('user_id', $userId)->exists();
                if (! $profileExists) {
                    DB::table('student_profiles')->insert([
                        'user_id' => $userId,
                        'student_number' => null,
                        'birth_date' => null,
                        'grade' => null,
                        'school_name' => null,
                        'bio' => null,
                        'registration_source' => 'legacy_import',
                        'registered_by' => null,
                        'onboarded_at' => null,
                        'status' => 'active',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $result['created_profiles']++;
                } else {
                    DB::table('student_profiles')->where('user_id', $userId)->update([
                        'status' => 'active',
                        'updated_at' => $now,
                    ]);
                }

                $membership = DB::table('academy_user')
                    ->where('academy_id', $academy->id)
                    ->where('user_id', $userId)
                    ->first(['role']);

                if ($membership) {
                    // Role was preflighted above; preserve the original joined_at.
                    DB::table('academy_user')
                        ->where('academy_id', $academy->id)
                        ->where('user_id', $userId)
                        ->update(['status' => 'active', 'updated_at' => $now]);
                } else {
                    DB::table('academy_user')->insert([
                        'academy_id' => $academy->id,
                        'user_id' => $userId,
                        'role' => 'student',
                        'status' => 'active',
                        'joined_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }

            if ($this->option('block-unlisted')) {
                $unlistedIds = $this->unlistedWordPressUsersQuery(array_column($rows, 'legacy_id'))
                    ->pluck('id')
                    ->all();

                if ($unlistedIds !== []) {
                    DB::table('role_user')
                        ->where('role_id', $studentRoleId)
                        ->whereIn('user_id', $unlistedIds)
                        ->delete();

                    DB::table('academy_user')
                        ->whereIn('user_id', $unlistedIds)
                        ->where('role', 'student')
                        ->update(['status' => 'archived', 'updated_at' => $now]);

                    $result['blocked_users'] = DB::table('users')
                        ->whereIn('id', $unlistedIds)
                        ->update(['status' => 'blocked', 'updated_at' => $now]);
                }
            }

            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->insert([
                    'actor_id' => auth()->id(),
                    'action' => 'legacy_students.imported',
                    'subject_type' => null,
                    'subject_id' => null,
                    'old_values' => null,
                    'new_values' => json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'ip_address' => null,
                    'user_agent' => 'ImportLegacyStudents',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }, 3);

        $this->newLine();
        $this->info('Import انجام شد.');
        $this->table(['Created users', 'Existing users', 'Created profiles', 'New student-role links', 'Forced password changes'], [[
            $result['created_users'],
            $result['existing_users'],
            $result['created_profiles'],
            $result['student_role_links'],
            $result['forced_password_changes'],
        ]]);
        $this->warn('This SQL contains identities only. Course enrollment, purchase/payment evidence, course/teacher assignment, classroom roster and weekly schedules were not inferred or created.');

        return self::SUCCESS;
    }

    private function requiredSchemaGaps(): array
    {
        $required = [
            'users' => ['name', 'email', 'password', 'must_change_password', 'mobile', 'status', 'legacy_source', 'legacy_id', 'created_at', 'updated_at'],
            'roles' => ['name', 'slug', 'description', 'created_at', 'updated_at'],
            'role_user' => ['role_id', 'user_id'],
            'student_profiles' => [
                'user_id', 'student_number', 'birth_date', 'grade', 'school_name', 'bio',
                'registration_source', 'registered_by', 'onboarded_at', 'status', 'created_at', 'updated_at',
            ],
            'academy_user' => ['academy_id', 'user_id', 'role', 'status', 'joined_at', 'created_at', 'updated_at'],
        ];

        $missing = [];
        foreach ($required as $table => $columns) {
            if (! Schema::hasTable($table)) {
                $missing[] = $table . ' (table)';
                continue;
            }

            foreach ($columns as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    $missing[] = $table . '.' . $column;
                }
            }
        }

        return $missing;
    }

    /**
     * Extract only the 55 verified users from the SQL source. All other SQL
     * statements are deliberately ignored, including DELETE, UPDATE, COMMIT,
     * role/profile mutations, and the source's broad cleanup statements.
     */
    private function extractVerifiedStudents(string $sql, LegacyWordPressPassword $legacyPasswords): array
    {
        // Locate each known INSERT prefix, then scan to its closing parenthesis
        // while respecting quoted strings. A name such as "فاطمه(لیانا)" must
        // not truncate the tuple at the parenthesis inside the name.
        $pattern = '/INSERT\\s+INTO\\s+users\\s*\\(name,\\s*email,\\s*password,\\s*mobile,\\s*status,\\s*legacy_source,\\s*legacy_id,\\s*created_at,\\s*updated_at\\)\\s*VALUES\\s*\\(/si';

        if (!preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
            throw new RuntimeException('فایل SQL شامل INSERTهای کاربر با قالب مورد انتظار نیست.');
        }

        $rows = [];
        foreach ($matches[0] as [$prefix, $prefixOffset]) {
            $tupleStart = $prefixOffset + strlen($prefix);
            [$valuesText, $afterTuple] = $this->readSqlTuple($sql, $tupleStart);

            if (!preg_match('/^\\s*ON\\s+DUPLICATE\\s+KEY\\s+UPDATE/i', substr($sql, $afterTuple))) {
                throw new RuntimeException('ساختار INSERT کاربر با قالب مورد انتظار مطابقت ندارد.');
            }

            $values = $this->splitSqlValues($valuesText);
            if (count($values) !== count(self::USER_COLUMNS)) {
                throw new RuntimeException('یکی از ردیف‌های کاربر تعداد ستون غیرمنتظره دارد.');
            }

            $data = array_combine(
                self::USER_COLUMNS,
                array_map(fn ($value) => $this->decodeSqlValue($value), $values)
            );

            if (($data['legacy_source'] ?? null) !== 'wordpress' || !is_numeric($data['legacy_id'] ?? null)) {
                throw new RuntimeException('منبع یا شناسه legacy یکی از رکوردها معتبر نیست.');
            }

            if (($data['status'] ?? null) !== 'active' || blank($data['name'] ?? null) || blank($data['password'] ?? null)) {
                throw new RuntimeException('نام، وضعیت یا هش رمز یکی از رکوردها معتبر نیست.');
            }

            $hash = (string) $data['password'];
            $rawLaravelBcrypt = str_starts_with($hash, '$2y$') && strlen($hash) === 60;
            if (!$legacyPasswords->supportsHashFormat($hash) && !$rawLaravelBcrypt) {
                throw new RuntimeException('فرمت یا طول هش رمز یکی از رکوردها پشتیبانی نمی‌شود؛ Import متوقف شد.');
            }

            if (str_starts_with($hash, '$wp$2y$')) {
                $kind = 'wp-bcrypt';
                $normalized = substr($hash, 3);
                if (strlen($normalized) !== 60) {
                    throw new RuntimeException('یکی از هش‌های WordPress bcrypt طول غیرمعتبر دارد.');
                }
            } elseif ((str_starts_with($hash, '$P$') || str_starts_with($hash, '$H$')) && strlen($hash) === 34) {
                $kind = 'phpass';
                $normalized = $hash;
            } elseif (str_starts_with($hash, '$2y$') && strlen($hash) === 60) {
                $kind = 'bcrypt';
                $normalized = $hash;
            } else {
                throw new RuntimeException('فرمت هش رمز یکی از رکوردها پشتیبانی نمی‌شود؛ Import متوقف شد.');
            }

            $data['legacy_id'] = (int) $data['legacy_id'];
            $data['password_kind'] = $kind;
            $data['normalized_password'] = $normalized;
            $rows[] = $data;
        }

        $ids = collect($rows)->pluck('legacy_id')->map(static fn ($id): int => (int) $id)->all();
        $expectedIds = self::EXPECTED_LEGACY_IDS;
        sort($ids);
        sort($expectedIds);

        if (
            count($rows) !== self::EXPECTED_COUNT
            || count(array_unique($ids)) !== self::EXPECTED_COUNT
            || $ids !== $expectedIds
        ) {
            throw new RuntimeException('تعداد یا شناسه‌های legacy با فهرست دقیق ۵۵ دانش‌آموز تأییدشده مطابقت ندارد.');
        }

        return $rows;
    }

    /**
     * Read the values in one SQL tuple, ignoring any closing parenthesis that
     * occurs inside a single-quoted string. Returns the values and the absolute
     * offset immediately after the tuple's closing parenthesis.
     *
     * @return array{0: string, 1: int}
     */
    private function readSqlTuple(string $sql, int $start): array
    {
        $quoted = false;
        $length = strlen($sql);

        for ($index = $start; $index < $length; $index++) {
            $char = $sql[$index];

            if ($quoted) {
                if ($char === '\\' && $index + 1 < $length) {
                    $index++;
                    continue;
                }

                if ($char === "'") {
                    if ($index + 1 < $length && $sql[$index + 1] === "'") {
                        $index++;
                    } else {
                        $quoted = false;
                    }
                }

                continue;
            }

            if ($char === "'") {
                $quoted = true;
                continue;
            }

            if ($char === ')') {
                return [substr($sql, $start, $index - $start), $index + 1];
            }
        }

        throw new RuntimeException('پرانتز انتهایی یکی از INSERTهای کاربر پیدا نشد.');
    }

    private function splitSqlValues(string $sql): array
    {
        $values = [];
        $current = '';
        $quoted = false;
        $length = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($quoted) {
                $current .= $char;

                if ($char === '\\' && $i + 1 < $length) {
                    $current .= $sql[++$i];
                    continue;
                }

                if ($char === "'") {
                    if ($i + 1 < $length && $sql[$i + 1] === "'") {
                        $current .= $sql[++$i];
                    } else {
                        $quoted = false;
                    }
                }

                continue;
            }

            if ($char === "'") {
                $quoted = true;
                $current .= $char;
                continue;
            }

            if ($char === ',') {
                $values[] = trim($current);
                $current = '';
                continue;
            }

            $current .= $char;
        }

        if ($quoted) {
            throw new RuntimeException('رشتهٔ SQL بسته نشده است.');
        }

        $values[] = trim($current);

        return $values;
    }

    private function decodeSqlValue(string $value): mixed
    {
        $value = trim($value);
        if (strtoupper($value) === 'NULL') {
            return null;
        }

        if (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            $input = substr($value, 1, -1);
            $output = '';
            $length = strlen($input);

            for ($i = 0; $i < $length; $i++) {
                if ($input[$i] === "'" && $i + 1 < $length && $input[$i + 1] === "'") {
                    $output .= "'";
                    $i++;
                    continue;
                }

                if ($input[$i] === '\\' && $i + 1 < $length) {
                    $next = $input[++$i];
                    $output .= match ($next) {
                        '0' => "\0",
                        'n' => "\n",
                        'r' => "\r",
                        'Z' => chr(26),
                        default => $next,
                    };
                    continue;
                }

                $output .= $input[$i];
            }

            return $output;
        }

        if (is_numeric($value)) {
            return $value;
        }

        throw new RuntimeException('مقدار SQL در قالب پشتیبانی‌شده نیست.');
    }

    private function emailConflicts(array $rows): array
    {
        $conflicts = [];
        foreach ($rows as $row) {
            if (blank($row['email'])) {
                continue;
            }

            $existingIdentity = $this->findByLegacyIdentity($row['legacy_id']);
            $emailUser = DB::table('users')->where('email', $row['email'])->first();

            if ($emailUser && (! $existingIdentity || (int) $emailUser->id !== (int) $existingIdentity->id)) {
                $conflicts[] = (int) $row['legacy_id'];
            }
        }

        return $conflicts;
    }

    /**
     * @param array<int, int> $importedIds
     */
    private function unlistedWordPressUsersQuery(array $importedIds)
    {
        return DB::table('users')
            ->where('legacy_source', 'wordpress')
            ->where(function ($query) use ($importedIds): void {
                $query->whereNull('legacy_id')->orWhereNotIn('legacy_id', $importedIds);
            });
    }

    private function findByLegacyIdentity(int $legacyId): ?object
    {
        return DB::table('users')
            ->where('legacy_source', 'wordpress')
            ->where('legacy_id', $legacyId)
            ->first();
    }
}
