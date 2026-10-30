<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class ParentDashboardService
{
    public function build(User $parent): array
    {
        $parentAcademyIds = fn () => DB::table('academy_user as parent_scope')
            ->join('academies as parent_scoped_academies', 'parent_scoped_academies.id', '=', 'parent_scope.academy_id')
            ->where('parent_scope.user_id', $parent->id)
            ->where('parent_scope.role', 'parent')
            ->where('parent_scope.status', 'active')
            ->where('parent_scoped_academies.status', 'active')
            ->select('parent_scope.academy_id');

        $children = $parent->children()
            ->where('users.status', 'active')
            ->whereNull('users.deleted_at')
            ->whereHas('roles', fn ($roles) => $roles->where('roles.slug', 'student'))
            ->whereHas('academies', function ($academies) use ($parent): void {
                $academies
                    ->where('academies.status', 'active')
                    ->where('academy_user.role', 'student')
                    ->where('academy_user.status', 'active')
                    ->whereExists(function ($membership) use ($parent): void {
                        $membership->selectRaw('1')
                            ->from('academy_user as parent_membership')
                            ->whereColumn('parent_membership.academy_id', 'academies.id')
                            ->where('parent_membership.user_id', $parent->id)
                            ->where('parent_membership.role', 'parent')
                            ->where('parent_membership.status', 'active');
                    });
            })
            ->with('studentProfile')
            ->orderBy('users.name')
            ->get();

        if ($children->isEmpty()) {
            return [
                'parent' => $parent,
                'children' => collect(),
                'overallProgress' => 0,
                'upcomingLiveClasses' => collect(),
                'recentResults' => collect(),
            ];
        }

        $childIds = $children->pluck('id');

        // Parent progress must use the same denominator as student progress:
        // active enrolled courses, published lessons only, and unstarted lessons count as 0.
        $progress = DB::table('course_enrollments as enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('course_sections as sections', 'sections.course_id', '=', 'courses.id')
            ->leftJoin('lessons as lessons', function ($join): void {
                $join->on('lessons.course_section_id', '=', 'sections.id')
                    ->where('lessons.status', '=', 'published')
                    ->where(fn ($published) => $published
                        ->whereNull('lessons.published_at')
                        ->orWhere('lessons.published_at', '<=', now()));
            })
            ->leftJoin('lesson_progress as lesson_progress', function ($join): void {
                $join->on('lesson_progress.lesson_id', '=', 'lessons.id')
                    ->on('lesson_progress.user_id', '=', 'enrollments.student_id');
            })
            ->whereIn('enrollments.student_id', $childIds)
            ->whereIn('courses.academy_id', $parentAcademyIds())
            ->where('enrollments.status', 'active')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->whereExists(function ($academy): void {
                $academy->selectRaw('1')->from('academies')
                    ->whereColumn('academies.id', 'courses.academy_id')
                    ->where('academies.status', 'active')
                    ->whereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                            ->whereColumn('enrolled_student_membership.academy_id', 'courses.academy_id')
                            ->whereColumn('enrolled_student_membership.user_id', 'enrollments.student_id')
                            ->where('enrolled_student_membership.role', 'student')
                            ->where('enrolled_student_membership.status', 'active');
                    });
            })
            ->where(function ($access): void {
                $access->where('courses.access_type', 'free')
                    ->orWhere(function ($paid): void {
                        $paid->where('enrollments.payment_status', 'paid')
                            ->where('enrollments.paid_amount', '>', 0)
                            ->where(function ($amount): void {
                                $amount->whereNull('enrollments.price_amount')
                                    ->orWhereColumn('enrollments.paid_amount', '>=', 'enrollments.price_amount');
                            })
                            ->whereExists(function ($transaction): void {
                                $transaction->selectRaw('1')->from('financial_transactions')
                                    ->whereColumn('financial_transactions.enrollment_id', 'enrollments.id')
                                    ->where('financial_transactions.status', 'completed')
                                    ->where('financial_transactions.amount', '>', 0);
                            });
                    });
            })
            ->where(function ($classroom): void {
                $classroom->whereNull('enrollments.classroom_id')
                    ->orWhereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('classroom_student')
                            ->whereColumn('classroom_student.classroom_id', 'enrollments.classroom_id')
                            ->whereColumn('classroom_student.student_id', 'enrollments.student_id')
                            ->where('classroom_student.status', 'active');
                    });
            })
            ->groupBy('enrollments.student_id')
            ->select('enrollments.student_id')
            ->selectRaw(
                'CASE WHEN COUNT(lessons.id) = 0 THEN 0
                    ELSE AVG(COALESCE(lesson_progress.progress_percent, 0))
                END AS progress_average'
            )
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->student_id => (float) $row->progress_average]);

        $courseProgressRows = DB::table('course_enrollments as enrollments')
            ->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->leftJoin('classrooms', 'classrooms.id', '=', 'enrollments.classroom_id')
            ->leftJoin('course_sections as sections', 'sections.course_id', '=', 'courses.id')
            ->leftJoin('lessons as lessons', function ($join): void {
                $join->on('lessons.course_section_id', '=', 'sections.id')
                    ->where('lessons.status', '=', 'published')
                    ->where(fn ($published) => $published
                        ->whereNull('lessons.published_at')
                        ->orWhere('lessons.published_at', '<=', now()));
            })
            ->leftJoin('lesson_progress as lesson_progress', function ($join): void {
                $join->on('lesson_progress.lesson_id', '=', 'lessons.id')
                    ->on('lesson_progress.user_id', '=', 'enrollments.student_id');
            })
            ->whereIn('enrollments.student_id', $childIds)
            ->whereIn('courses.academy_id', $parentAcademyIds())
            ->where('enrollments.status', 'active')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->whereExists(function ($academy): void {
                $academy->selectRaw('1')->from('academies')
                    ->whereColumn('academies.id', 'courses.academy_id')
                    ->where('academies.status', 'active')
                    ->whereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                            ->whereColumn('enrolled_student_membership.academy_id', 'courses.academy_id')
                            ->whereColumn('enrolled_student_membership.user_id', 'enrollments.student_id')
                            ->where('enrolled_student_membership.role', 'student')
                            ->where('enrolled_student_membership.status', 'active');
                    });
            })
            ->where(function ($access): void {
                $access->where('courses.access_type', 'free')
                    ->orWhere(function ($paid): void {
                        $paid->where('enrollments.payment_status', 'paid')
                            ->where('enrollments.paid_amount', '>', 0)
                            ->where(function ($amount): void {
                                $amount->whereNull('enrollments.price_amount')
                                    ->orWhereColumn('enrollments.paid_amount', '>=', 'enrollments.price_amount');
                            })
                            ->whereExists(function ($transaction): void {
                                $transaction->selectRaw('1')->from('financial_transactions')
                                    ->whereColumn('financial_transactions.enrollment_id', 'enrollments.id')
                                    ->where('financial_transactions.status', 'completed')
                                    ->where('financial_transactions.amount', '>', 0);
                            });
                    });
            })
            ->where(function ($classroom): void {
                $classroom->whereNull('enrollments.classroom_id')
                    ->orWhereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('classroom_student')
                            ->whereColumn('classroom_student.classroom_id', 'enrollments.classroom_id')
                            ->whereColumn('classroom_student.student_id', 'enrollments.student_id')
                            ->where('classroom_student.status', 'active');
                    });
            })
            ->groupBy('enrollments.student_id', 'courses.id', 'courses.title', 'classrooms.title')
            ->select([
                'enrollments.student_id',
                'courses.id as course_id',
                'courses.title as course_title',
                'classrooms.title as classroom_title',
            ])
            ->selectRaw('COUNT(lessons.id) as total_lessons')
            ->selectRaw('SUM(CASE WHEN COALESCE(lesson_progress.progress_percent, 0) >= 100 THEN 1 ELSE 0 END) as completed_lessons')
            ->selectRaw('CASE WHEN COUNT(lessons.id) = 0 THEN 0 ELSE AVG(COALESCE(lesson_progress.progress_percent, 0)) END as progress_average')
            ->orderBy('courses.title')
            ->get()
            ->groupBy('student_id');

        $enrollmentCounts = $courseProgressRows->map(
            fn ($rows) => $rows->pluck('course_id')->unique()->count()
        );

        $pendingAssignments = DB::table('users as students')
            ->whereIn('students.id', $childIds)
            ->select('students.id')
            ->selectSub(function ($query) use ($parent): void {
                $query->from('assignments')
                    ->where('assignments.status', 'published')
                    ->whereExists(function ($parentScope) use ($parent): void {
                        $parentScope->selectRaw('1')
                            ->from('courses as parent_scope_courses')
                            ->join('academy_user as parent_scope_memberships', function ($join) use ($parent): void {
                                $join->on('parent_scope_memberships.academy_id', '=', 'parent_scope_courses.academy_id')
                                    ->where('parent_scope_memberships.user_id', '=', $parent->id)
                                    ->where('parent_scope_memberships.role', '=', 'parent')
                                    ->where('parent_scope_memberships.status', '=', 'active');
                            })
                            ->join('academies as parent_scope_academies', function ($join): void {
                                $join->on('parent_scope_academies.id', '=', 'parent_scope_courses.academy_id')
                                    ->where('parent_scope_academies.status', '=', 'active');
                            })
                            ->whereColumn('parent_scope_courses.id', 'assignments.course_id');
                    })
                    ->whereExists(function ($enrollments): void {
                        $enrollments->selectRaw('1')
                            ->from('course_enrollments')
                            ->whereColumn('course_enrollments.student_id', 'students.id')
                            ->whereColumn('course_enrollments.course_id', 'assignments.course_id')
                            ->where('course_enrollments.status', 'active')
                            ->whereExists(function ($course): void {
                                $course->selectRaw('1')
                                    ->from('courses')
                                    ->whereColumn('courses.id', 'assignments.course_id')
                                    ->where('courses.status', 'published')
                                    ->whereNotNull('courses.published_at')
                                    ->where('courses.published_at', '<=', now())
                                    ->whereExists(function ($academy): void {
                                        $academy->selectRaw('1')->from('academies')
                                            ->whereColumn('academies.id', 'courses.academy_id')
                                            ->where('academies.status', 'active')
                                            ->whereExists(function ($membership): void {
                                                $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                                                    ->whereColumn('enrolled_student_membership.academy_id', 'courses.academy_id')
                                                    ->whereColumn('enrolled_student_membership.user_id', 'students.id')
                                                    ->where('enrolled_student_membership.role', 'student')
                                                    ->where('enrolled_student_membership.status', 'active');
                                            });
                                    });
                            })
                            ->where(function ($access): void {
                                $access->whereExists(function ($free): void {
                                    $free->selectRaw('1')->from('courses')
                                        ->whereColumn('courses.id', 'course_enrollments.course_id')
                                        ->where('courses.access_type', 'free');
                                })->orWhere(function ($paid): void {
                                    $paid->where('course_enrollments.payment_status', 'paid')
                                        ->where('course_enrollments.paid_amount', '>', 0)
                                        ->where(function ($amount): void {
                                            $amount->whereNull('course_enrollments.price_amount')
                                                ->orWhereColumn('course_enrollments.paid_amount', '>=', 'course_enrollments.price_amount');
                                        })
                                        ->whereExists(function ($transaction): void {
                                            $transaction->selectRaw('1')->from('financial_transactions')
                                                ->whereColumn('financial_transactions.enrollment_id', 'course_enrollments.id')
                                                ->where('financial_transactions.status', 'completed')
                                                ->where('financial_transactions.amount', '>', 0);
                                        });
                                });
                            })
                            ->where(function ($classroom): void {
                                $classroom->whereNull('assignments.classroom_id')
                                    ->orWhere(function ($specific): void {
                                        $specific->whereColumn('course_enrollments.classroom_id', 'assignments.classroom_id')
                                            ->whereExists(function ($membership): void {
                                                $membership->selectRaw('1')->from('classroom_student')
                                                    ->whereColumn('classroom_student.classroom_id', 'assignments.classroom_id')
                                                    ->whereColumn('classroom_student.student_id', 'students.id')
                                                    ->where('classroom_student.status', 'active');
                                            });
                                    });
                            });
                    })
                    ->where(function ($classroom): void {
                        $classroom->whereNull('assignments.classroom_id')
                            ->orWhereExists(function ($membership): void {
                                $membership->selectRaw('1')
                                    ->from('classroom_student')
                                    ->whereColumn('classroom_student.classroom_id', 'assignments.classroom_id')
                                    ->whereColumn('classroom_student.student_id', 'students.id')
                                    ->where('classroom_student.status', 'active');
                            });
                    })
                    ->whereNotExists(function ($submissions): void {
                        $submissions->selectRaw('1')
                            ->from('assignment_submissions')
                            ->whereColumn('assignment_submissions.assignment_id', 'assignments.id')
                            ->whereColumn('assignment_submissions.student_id', 'students.id')
                            ->whereNotNull('assignment_submissions.submitted_at');
                    })
                    ->selectRaw('COUNT(*)');
            }, 'pending_count')
            ->pluck('pending_count', 'students.id');

        $attendanceStats = DB::table('attendances')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->join('academies', 'academies.id', '=', 'classrooms.academy_id')
            ->whereIn('attendances.student_id', $childIds)
            ->whereIn('classrooms.academy_id', $parentAcademyIds())
            ->where('classrooms.status', 'active')
            ->where('academies.status', 'active')
            ->whereExists(function ($membership): void {
                $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                    ->whereColumn('enrolled_student_membership.academy_id', 'classrooms.academy_id')
                    ->whereColumn('enrolled_student_membership.user_id', 'attendances.student_id')
                    ->where('enrolled_student_membership.role', 'student')
                    ->where('enrolled_student_membership.status', 'active');
            })
            ->groupBy('attendances.student_id')
            ->select('attendances.student_id')
            ->selectRaw('COUNT(*) as attendance_total')
            ->selectRaw("SUM(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as attendance_present")
            ->selectRaw("SUM(CASE WHEN attendances.status = 'absent' THEN 1 ELSE 0 END) as attendance_absent")
            ->get()
            ->keyBy('student_id');

        $pendingAssignmentItems = DB::table('assignments')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->join('course_enrollments as enrollments', function ($join): void {
                $join->on('enrollments.course_id', '=', 'assignments.course_id')
                    ->where('enrollments.status', '=', 'active');
            })
            ->join('users as students', 'students.id', '=', 'enrollments.student_id')
            ->whereIn('students.id', $childIds)
            ->whereIn('courses.academy_id', $parentAcademyIds())
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->whereExists(function ($academy): void {
                $academy->selectRaw('1')->from('academies')
                    ->whereColumn('academies.id', 'courses.academy_id')
                    ->where('academies.status', 'active');
            })
            ->whereExists(function ($membership): void {
                $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                    ->whereColumn('enrolled_student_membership.academy_id', 'courses.academy_id')
                    ->whereColumn('enrolled_student_membership.user_id', 'students.id')
                    ->where('enrolled_student_membership.role', 'student')
                    ->where('enrolled_student_membership.status', 'active');
            })
            ->where('assignments.status', 'published')
            ->where(function ($classroom): void {
                $classroom->whereNull('assignments.classroom_id')
                    ->orWhere(function ($specific): void {
                        $specific->whereColumn('enrollments.classroom_id', 'assignments.classroom_id')
                            ->whereExists(function ($membership): void {
                                $membership->selectRaw('1')->from('classroom_student')
                                    ->whereColumn('classroom_student.classroom_id', 'assignments.classroom_id')
                                    ->whereColumn('classroom_student.student_id', 'students.id')
                                    ->where('classroom_student.status', 'active');
                            });
                    });
            })
            ->where(function ($access): void {
                $access->where('courses.access_type', 'free')
                    ->orWhere(function ($paid): void {
                        $paid->where('enrollments.payment_status', 'paid')
                            ->where('enrollments.paid_amount', '>', 0)
                            ->where(function ($amount): void {
                                $amount->whereNull('enrollments.price_amount')
                                    ->orWhereColumn('enrollments.paid_amount', '>=', 'enrollments.price_amount');
                            })
                            ->whereExists(function ($transaction): void {
                                $transaction->selectRaw('1')->from('financial_transactions')
                                    ->whereColumn('financial_transactions.enrollment_id', 'enrollments.id')
                                    ->where('financial_transactions.status', 'completed')
                                    ->where('financial_transactions.amount', '>', 0);
                            });
                    });
            })
            ->whereNotExists(function ($submission): void {
                $submission->selectRaw('1')->from('assignment_submissions')
                    ->whereColumn('assignment_submissions.assignment_id', 'assignments.id')
                    ->whereColumn('assignment_submissions.student_id', 'students.id')
                    ->whereNotNull('assignment_submissions.submitted_at');
            })
            ->select([
                'assignments.id', 'assignments.title', 'assignments.due_at',
                'courses.title as course_title', 'students.id as student_id', 'students.name as student_name',
            ])
            ->distinct()
            ->orderByRaw('CASE WHEN assignments.due_at IS NULL THEN 1 ELSE 0 END')
            ->orderBy('assignments.due_at')
            ->limit(12)
            ->get()
            ->map(function ($item): object {
                $item->due_at = $item->due_at ? Carbon::parse($item->due_at) : null;
                return $item;
            });

        $recentAttendance = DB::table('attendances')
            ->join('users as students', 'students.id', '=', 'attendances.student_id')
            ->join('classrooms', 'classrooms.id', '=', 'attendances.classroom_id')
            ->join('academies', 'academies.id', '=', 'classrooms.academy_id')
            ->leftJoin('courses', 'courses.id', '=', 'classrooms.course_id')
            ->whereIn('attendances.student_id', $childIds)
            ->whereIn('classrooms.academy_id', $parentAcademyIds())
            ->where('classrooms.status', 'active')
            ->where('academies.status', 'active')
            ->whereExists(function ($membership): void {
                $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                    ->whereColumn('enrolled_student_membership.academy_id', 'classrooms.academy_id')
                    ->whereColumn('enrolled_student_membership.user_id', 'attendances.student_id')
                    ->where('enrolled_student_membership.role', 'student')
                    ->where('enrolled_student_membership.status', 'active');
            })
            ->orderByDesc('attendances.attendance_date')
            ->orderByDesc('attendances.id')
            ->limit(8)
            ->get([
                'attendances.id', 'attendances.student_id', 'attendances.attendance_date',
                'attendances.status', 'attendances.attended_minutes', 'attendances.scheduled_minutes',
                'students.name as student_name', 'classrooms.title as classroom_title', 'courses.title as course_title',
            ])
            ->map(function ($item): object {
                $item->attendance_date = $item->attendance_date instanceof \Carbon\CarbonInterface
                    ? $item->attendance_date
                    : Carbon::parse($item->attendance_date);
                return $item;
            });

        $children = $children->map(function (User $child) use ($progress, $enrollmentCounts, $pendingAssignments, $courseProgressRows, $attendanceStats) {
            $child->dashboard_progress = round((float) ($progress[$child->id] ?? 0));
            $child->dashboard_courses = (int) ($enrollmentCounts[$child->id] ?? 0);
            $child->dashboard_pending = (int) ($pendingAssignments[$child->id] ?? 0);
            $child->dashboard_courses_detail = collect($courseProgressRows->get($child->id, []))
                ->take(5)->values()->map(function ($course): object {
                    $course->progress_average = round((float) $course->progress_average);
                    $course->total_lessons = (int) $course->total_lessons;
                    $course->completed_lessons = (int) $course->completed_lessons;
                    return $course;
                });
            $attendance = $attendanceStats->get($child->id);
            $child->dashboard_attendance_total = (int) ($attendance->attendance_total ?? 0);
            $child->dashboard_attendance_present = (int) ($attendance->attendance_present ?? 0);
            $child->dashboard_attendance_absent = (int) ($attendance->attendance_absent ?? 0);

            return $child;
        });

        $upcomingLiveClasses = DB::table('live_classes')
            ->join('courses', 'courses.id', '=', 'live_classes.course_id')
            ->whereIn('courses.academy_id', $parentAcademyIds())
            ->whereBetween('live_classes.scheduled_at', [now(), now()->addDays(7)])
            ->where('live_classes.status', 'scheduled')
            ->where('courses.status', 'published')
            ->whereNotNull('courses.published_at')
            ->where('courses.published_at', '<=', now())
            ->whereExists(function ($academy): void {
                $academy->selectRaw('1')->from('academies')
                    ->whereColumn('academies.id', 'courses.academy_id')
                    ->where('academies.status', 'active');
            })
            ->whereExists(function ($query) use ($childIds): void {
                $query->selectRaw('1')
                    ->from('course_enrollments')
                    ->whereIn('course_enrollments.student_id', $childIds)
                    ->where('course_enrollments.status', 'active')
                    ->whereColumn('course_enrollments.course_id', 'live_classes.course_id')
                    ->where(function ($scope): void {
                        $scope->whereExists(function ($free): void {
                            $free->selectRaw('1')->from('courses as free_courses')
                                ->whereColumn('free_courses.id', 'live_classes.course_id')
                                ->where('free_courses.access_type', 'free');
                        })->orWhere(function ($paid): void {
                            $paid->where('course_enrollments.payment_status', 'paid')
                                ->where('course_enrollments.paid_amount', '>', 0)
                                ->where(function ($amount): void {
                                    $amount->whereNull('course_enrollments.price_amount')
                                        ->orWhereColumn('course_enrollments.paid_amount', '>=', 'course_enrollments.price_amount');
                                })
                                ->whereExists(function ($transaction): void {
                                    $transaction->selectRaw('1')->from('financial_transactions')
                                        ->whereColumn('financial_transactions.enrollment_id', 'course_enrollments.id')
                                        ->where('financial_transactions.status', 'completed')
                                        ->where('financial_transactions.amount', '>', 0);
                                });
                        });
                    })
                    ->whereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                            ->whereColumn('enrolled_student_membership.academy_id', 'courses.academy_id')
                            ->whereColumn('enrolled_student_membership.user_id', 'course_enrollments.student_id')
                            ->where('enrolled_student_membership.role', 'student')
                            ->where('enrolled_student_membership.status', 'active');
                    })
                    ->where(function ($classroomQuery): void {
                        $classroomQuery->whereNull('live_classes.classroom_id')
                            ->orWhere(function ($specific): void {
                                $specific->whereColumn('course_enrollments.classroom_id', 'live_classes.classroom_id')
                                    ->whereExists(function ($membership): void {
                                        $membership->selectRaw('1')->from('classroom_student')
                                            ->whereColumn('classroom_student.classroom_id', 'live_classes.classroom_id')
                                            ->whereColumn('classroom_student.student_id', 'course_enrollments.student_id')
                                            ->where('classroom_student.status', 'active');
                                    });
                            });
                    });
            })
            ->select([
                'live_classes.id',
                'live_classes.title',
                'live_classes.scheduled_at',
                'live_classes.course_id',
                'courses.title as course_title',
            ])
            ->selectSub(function ($query) use ($childIds): void {
                $query->from('course_enrollments')
                    ->whereIn('student_id', $childIds)
                    ->where('status', 'active')
                    ->whereColumn('course_id', 'live_classes.course_id')
                    ->where(function ($scope): void {
                        $scope->whereExists(function ($free): void {
                            $free->selectRaw('1')->from('courses as free_courses')
                                ->whereColumn('free_courses.id', 'live_classes.course_id')
                                ->where('free_courses.access_type', 'free');
                        })->orWhere(function ($paid): void {
                            $paid->where('course_enrollments.payment_status', 'paid')
                                ->where('course_enrollments.paid_amount', '>', 0)
                                ->where(function ($amount): void {
                                    $amount->whereNull('course_enrollments.price_amount')
                                        ->orWhereColumn('course_enrollments.paid_amount', '>=', 'course_enrollments.price_amount');
                                })
                                ->whereExists(function ($transaction): void {
                                    $transaction->selectRaw('1')->from('financial_transactions')
                                        ->whereColumn('financial_transactions.enrollment_id', 'course_enrollments.id')
                                        ->where('financial_transactions.status', 'completed')
                                        ->where('financial_transactions.amount', '>', 0);
                                });
                        });
                    })
                    ->whereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('academy_user as enrolled_student_membership')
                            ->whereColumn('enrolled_student_membership.academy_id', 'courses.academy_id')
                            ->whereColumn('enrolled_student_membership.user_id', 'course_enrollments.student_id')
                            ->where('enrolled_student_membership.role', 'student')
                            ->where('enrolled_student_membership.status', 'active');
                    })
                    ->where(function ($classroom): void {
                        $classroom->whereNull('live_classes.classroom_id')
                            ->orWhere(function ($specific): void {
                                $specific->whereColumn('course_enrollments.classroom_id', 'live_classes.classroom_id')
                                    ->whereExists(function ($membership): void {
                                        $membership->selectRaw('1')->from('classroom_student')
                                            ->whereColumn('classroom_student.classroom_id', 'live_classes.classroom_id')
                                            ->whereColumn('classroom_student.student_id', 'course_enrollments.student_id')
                                            ->where('classroom_student.status', 'active');
                                    });
                            });
                    })
                    ->orderBy('student_id')
                    ->limit(1)
                    ->select('student_id');
            }, 'student_id')
            ->orderBy('live_classes.scheduled_at')
            ->limit(8)
            ->get();


        $upcomingMeetings = DB::table('academy_meetings')
            ->join('courses', 'courses.id', '=', 'academy_meetings.course_id')
            ->where('academy_meetings.parent_id', $parent->id)
            ->whereIn('academy_meetings.student_id', $childIds)
            ->whereIn('academy_meetings.academy_id', $parentAcademyIds())
            ->whereExists(function ($activeAcademy): void {
                $activeAcademy->selectRaw('1')->from('academies')
                    ->whereColumn('academies.id', 'academy_meetings.academy_id')
                    ->where('academies.status', 'active');
            })
            ->where('academy_meetings.status', 'scheduled')
            ->whereBetween('academy_meetings.scheduled_at', [now(), now()->addDays(30)])
            ->orderBy('academy_meetings.scheduled_at')
            ->limit(8)
            ->get([
                'academy_meetings.id',
                'academy_meetings.student_id',
                'academy_meetings.title',
                'academy_meetings.message',
                'academy_meetings.scheduled_at',
                'academy_meetings.course_id',
                'courses.title as course_title',
            ])
            ->map(function ($item): object {
                $item->scheduled_at = $item->scheduled_at instanceof \Carbon\CarbonInterface
                    ? $item->scheduled_at
                    : Carbon::parse($item->scheduled_at);
                $item->kind = 'meeting';
                return $item;
            });

        $assignmentResults = DB::table('assignment_submissions as submissions')
            ->join('assignments', 'assignments.id', '=', 'submissions.assignment_id')
            ->join('courses', 'courses.id', '=', 'assignments.course_id')
            ->join('academies', 'academies.id', '=', 'courses.academy_id')
            ->join('users as students', 'students.id', '=', 'submissions.student_id')
            ->whereIn('submissions.student_id', $childIds)
            ->whereIn('courses.academy_id', $parentAcademyIds())
            ->where('academies.status', 'active')
            ->whereNotNull('submissions.graded_at')
            ->where('assignments.status', 'published')
            ->orderByDesc('submissions.graded_at')
            ->limit(8)
            ->get([
                'submissions.id', 'students.name as student_name', 'assignments.title',
                'submissions.score', 'submissions.feedback', 'submissions.graded_at as occurred_at',
            ])
            ->map(function ($item): object {
                $item->result_type = 'assignment';
                $item->status_label = 'تصحیح‌شده';
                $item->occurred_at = $item->occurred_at ? Carbon::parse($item->occurred_at) : null;
                return $item;
            });

        $examResults = DB::table('exam_attempts as attempts')
            ->join('exams', 'exams.id', '=', 'attempts.exam_id')
            ->join('courses', 'courses.id', '=', 'exams.course_id')
            ->join('academies', 'academies.id', '=', 'courses.academy_id')
            ->join('users as students', 'students.id', '=', 'attempts.student_id')
            ->whereIn('attempts.student_id', $childIds)
            ->whereIn('courses.academy_id', $parentAcademyIds())
            ->where('academies.status', 'active')
            ->whereNotNull('attempts.submitted_at')
            ->where('exams.status', 'published')
            ->whereExists(function ($enrollments): void {
                $enrollments->selectRaw('1')->from('course_enrollments as enrollments')
                    ->whereColumn('enrollments.student_id', 'attempts.student_id')
                    ->whereColumn('enrollments.course_id', 'exams.course_id')
                    ->where('enrollments.status', 'active')
                    ->where(function ($access): void {
                        $access->where('enrollments.paid_amount', '>', 0)
                            ->orWhereExists(function ($free): void {
                                $free->selectRaw('1')->from('courses as free_courses')
                                    ->whereColumn('free_courses.id', 'exams.course_id')
                                    ->where('free_courses.access_type', 'free');
                            });
                    });
            })
            ->where(function ($classroom): void {
                $classroom->whereNull('exams.classroom_id')
                    ->orWhereExists(function ($membership): void {
                        $membership->selectRaw('1')->from('classroom_student as membership')
                            ->whereColumn('membership.classroom_id', 'exams.classroom_id')
                            ->whereColumn('membership.student_id', 'attempts.student_id')
                            ->where('membership.status', 'active');
                    });
            })
            ->orderByDesc('attempts.submitted_at')
            ->limit(8)
            ->get([
                'attempts.id', 'students.name as student_name', 'exams.title',
                'attempts.score', 'attempts.status', 'attempts.submitted_at as occurred_at',
            ])
            ->map(function ($item): object {
                $item->result_type = 'exam';
                $item->feedback = null;
                if ($item->status !== 'graded') {
                    $item->score = null;
                }
                $item->status_label = $item->status === 'graded' ? 'تصحیح‌شده' : 'در انتظار بررسی';
                $item->occurred_at = $item->occurred_at ? Carbon::parse($item->occurred_at) : null;
                return $item;
            });

        $recentResults = $assignmentResults->concat($examResults)
            ->sortByDesc('occurred_at')->take(8)->values();

        $liveItems = $upcomingLiveClasses->map(function ($item): object {
            $item->scheduled_at = $item->scheduled_at instanceof \Carbon\CarbonInterface
                ? $item->scheduled_at
                : Carbon::parse($item->scheduled_at);
            $item->kind = 'live_class';
            return $item;
        });

        $upcomingItems = $liveItems->concat($upcomingMeetings)
            ->sortBy('scheduled_at')
            ->take(8)
            ->values();

        return [
            'parent' => $parent,
            'children' => $children,
            'overallProgress' => round((float) $progress->avg()),
            'upcomingLiveClasses' => $upcomingItems,
            'pendingAssignmentItems' => $pendingAssignmentItems,
            'recentAttendance' => $recentAttendance,
            'recentResults' => $recentResults,
        ];
    }
}
