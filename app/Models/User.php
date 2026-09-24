<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['email_verified_at' => 'datetime', 'password' => 'hashed'];
    }

    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class); }

    public function academies(): BelongsToMany
    {
        return $this->belongsToMany(Academy::class)
            ->withPivot(['role', 'status', 'joined_at'])->withTimestamps();
    }

    public function ownedAcademy(): HasOne
    {
        return $this->hasOne(Academy::class, 'owner_id');
    }

    public function teacherProfile(): HasOne { return $this->hasOne(TeacherProfile::class); }
    public function studentProfile(): HasOne { return $this->hasOne(StudentProfile::class); }
    public function parentProfile(): HasOne { return $this->hasOne(ParentProfile::class); }
    public function createdCourses(): HasMany { return $this->hasMany(Course::class, 'created_by'); }

    public function taughtCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_teacher', 'teacher_id', 'course_id')
            ->withPivot('is_primary')->withTimestamps();
    }

    public function enrollments(): HasMany { return $this->hasMany(CourseEnrollment::class, 'student_id'); }
    public function financialTransactions(): HasMany { return $this->hasMany(FinancialTransaction::class); }
    public function recordedFinancialTransactions(): HasMany { return $this->hasMany(FinancialTransaction::class, 'recorded_by'); }

    public function classroomsAsTeacher(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_teacher', 'teacher_id', 'classroom_id')->withTimestamps();
    }

    public function classroomsAsStudent(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'classroom_student', 'student_id', 'classroom_id')
            ->withPivot(['status', 'enrolled_at', 'completed_at'])->withTimestamps();
    }

    public function lessonProgress(): HasMany { return $this->hasMany(LessonProgress::class); }
    public function assignmentSubmissions(): HasMany { return $this->hasMany(AssignmentSubmission::class, 'student_id'); }
    public function examAttempts(): HasMany { return $this->hasMany(ExamAttempt::class, 'student_id'); }
    public function attendance(): HasMany { return $this->hasMany(Attendance::class, 'student_id'); }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')->withPivot('relation')->withTimestamps();
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')->withPivot('relation')->withTimestamps();
    }

    public function hasRole(string $role): bool { return $this->roles()->where('slug', $role)->exists(); }
    public function hasAnyRole(array $roles): bool { return $this->roles()->whereIn('slug', $roles)->exists(); }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()->whereHas('permissions', fn ($query) => $query->where('name', $permission))->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        return $this->roles()->whereHas('permissions', fn ($query) => $query->whereIn('name', $permissions))->exists();
    }
}