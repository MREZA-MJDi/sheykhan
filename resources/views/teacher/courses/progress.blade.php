@extends('layouts.teacher')
@section('title','پیشرفت دانش‌آموزان | شیخان')
@section('header-title','پیشرفت دانش‌آموزان')
@section('content')
<div class="grid gap-5">
    <div class="dashboard-panel p-5 sm:p-7"><span class="text-xs font-black text-[var(--panel-primary)]">مانیتور یادگیری</span><h2 class="mt-1 text-2xl font-black">{{ $course->title }}</h2><p class="mt-2 text-sm text-slate-500">دقیقاً ببین هر دانش‌آموز کدام درس را دیده، چند درصد جلو رفته و آخرین مشاهده چه زمانی بوده است.</p></div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[860px] w-full text-right">
                <thead class="bg-slate-50 text-[10px] font-black text-slate-500">
                    <tr><th class="px-4 py-4">دانش‌آموز</th>@foreach($lessons as $lesson)<th class="px-3 py-4 text-center">{{ $loop->iteration }}</th>@endforeach<th class="px-4 py-4">میانگین</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse($students as $student)
                    <tr>
                        <td class="px-4 py-4"><strong class="text-xs">{{ $student->name }}</strong></td>
                        @foreach($lessons as $lesson)
                            @php($row = $progress[$student->id . ':' . $lesson->id] ?? null)
                            <td class="px-3 py-4 text-center">
                                <div class="mx-auto flex w-14 flex-col items-center gap-1">
                                    <span class="text-[10px] font-black">{{ round((float)($row->progress_percent ?? 0)) }}٪</span>
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-[var(--panel-primary)]" style="width:{{ min(100,max(0,(float)($row->progress_percent ?? 0))) }}%"></span></div>
                                </div>
                            </td>
                        @endforeach
                        @php($summary = $studentSummary->firstWhere('student.id',$student->id))
                        <td class="px-4 py-4 text-center"><strong class="text-xs">{{ $summary['progress'] ?? 0 }}٪</strong><div class="mt-1 text-[9px] text-slate-500">{{ $summary['completed_lessons'] ?? 0 }} درس کامل</div></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $lessons->count()+2 }}" class="px-5 py-12 text-center text-sm text-slate-500">دانش‌آموزی برای این دوره ثبت نشده است.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection