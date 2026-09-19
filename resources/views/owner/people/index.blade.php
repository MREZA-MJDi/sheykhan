@extends('layouts.owner')
@section('title','اعضای آموزشگاه | شیخان')
@section('header-title','اعضای آموزشگاه')
@section('content')
<div class="grid gap-5">
    <div class="dashboard-panel p-5 sm:p-7"><p class="text-xs font-black text-[var(--panel-primary)]">People</p><h2 class="mt-1 text-2xl font-black">{{ $academy->name }}</h2><p class="mt-2 text-sm text-slate-500">اعضای فعال آموزشگاه و اتصال مدرس‌ها به دوره‌ها.</p></div>

    <section class="dashboard-panel p-5 sm:p-7">
        <div><h3 class="text-base font-black">اختصاص مدرس به دوره</h3><p class="mt-1 text-xs text-slate-500">مدرس فقط بعد از این اتصال می‌تواند محتوای دوره را مدیریت کند.</p></div>
        <form method="POST" action="{{ route('owner.people.assign-teacher',$academy) }}" class="mt-5 grid gap-4 sm:grid-cols-3">@csrf
            <select name="teacher_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select>
            <select name="course_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select>
            <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white">اختصاص دوره</button>
        </form>
    </section>

    <section class="dashboard-panel p-5 sm:p-7">
        <div><h3 class="text-base font-black">ثبت‌نام دانش‌آموز</h3><p class="mt-1 text-xs text-slate-500">دانش‌آموز فعال آموزشگاه را به دوره و در صورت نیاز به کلاس متصل کن.</p></div>
        <form method="POST" action="{{ route('owner.people.enroll-student',$academy) }}" class="mt-5 grid gap-4 sm:grid-cols-4">
            @csrf
            <select name="student_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($students as $student)<option value="{{ $student->id }}">{{ $student->name }}</option>@endforeach</select>
            <select name="course_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">@foreach($courses as $course)<option value="{{ $course->id }}">{{ $course->title }}</option>@endforeach</select>
            <select name="classroom_id" class="rounded-xl border border-slate-200 px-3 py-3 text-sm"><option value="">بدون کلاس</option>@foreach($courses as $course)@foreach($course->classrooms as $classroom)<option value="{{ $classroom->id }}">{{ $classroom->title }}</option>@endforeach @endforeach</select>
            <input type="number" step="0.01" min="0" name="paid_amount" placeholder="مبلغ پرداختی" class="rounded-xl border border-slate-200 px-3 py-3 text-sm">
            <button class="rounded-xl bg-[var(--panel-primary)] px-4 py-3 text-xs font-black text-white sm:col-span-4">ثبت‌نام دانش‌آموز</button>
        </form>
    </section>
    <div class="grid gap-4 xl:grid-cols-3">
        @foreach([['مدرس‌ها',$teachers],['دانش‌آموزان',$students],['والدین',$parents]] as [$title,$items])
            <section class="dashboard-panel p-5"><h3 class="font-black">{{ $title }}</h3><div class="mt-4 grid gap-3">@forelse($items as $person)<div class="rounded-xl bg-slate-50 p-3"><strong class="text-xs">{{ $person->name }}</strong><span class="mt-1 block text-[9px] text-slate-500">{{ $person->email }}</span></div>@empty<div class="rounded-xl bg-slate-50 p-4 text-center text-xs text-slate-500">عضوی نیست</div>@endforelse</div></section>
        @endforeach
    </div>
</div>
@endsection