@php
    $teacherMessageStatuses = ['sent' => 'در انتظار پاسخ', 'responded' => 'پاسخ داده شد'];
@endphp
<section class="mt-6 space-y-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-7" aria-labelledby="teacher-direct-inbox-title">
    <div>
        <span class="text-xs font-black text-[var(--panel-primary)]">ارتباط آموزشی</span>
        <h2 id="teacher-direct-inbox-title" class="mt-1 text-lg font-black text-slate-900">{{ $recipientType === 'student' ? 'پیام‌های دریافتی از مدرس' : 'پیام مستقیم مدرس‌ها' }}</h2>
        <p class="mt-1 text-sm leading-7 text-slate-500">{{ $recipientType === 'student' ? 'پیام‌های مدرس و پاسخ‌هایی که برای او فرستاده‌ای در همین کارتابل باقی می‌مانند.' : 'پیام‌های مستقیمی که مدرس برایت فرستاده و پاسخ‌هایی که ثبت کرده‌ای در همین کارتابل می‌مانند.' }}</p>
    </div>
    @forelse($messages as $message)
        <article class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div class="min-w-0">
                    <h3 class="font-black">{{ $message->subject }}</h3>
                    <p class="mt-1 text-xs text-slate-500">مدرس: {{ $message->teacher?->name }} · {{ $message->course?->title }} · {{ $message->classroom?->title }} · {{ \App\Support\PersianUi::date($message->created_at) }}</p>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold">{{ $teacherMessageStatuses[$message->status] ?? $message->status }}</span>
            </div>
            <p class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-700">{{ $message->body }}</p>
            @if($message->response)
                <div class="mt-3 rounded-xl border border-emerald-100 bg-emerald-50 p-4">
                    <p class="text-xs font-black text-emerald-800">پاسخ شما · {{ $message->responded_at ? \App\Support\PersianUi::date($message->responded_at) : '' }}</p>
                    <p class="mt-2 whitespace-pre-line break-words text-sm leading-7">{{ $message->response }}</p>
                </div>
            @elseif($message->status === 'sent')
                <form method="POST" action="{{ route($recipientType . '.teacher-messages.reply', $message->id) }}" class="mt-4 grid gap-3 border-t border-slate-100 pt-4">
                    @csrf @method('PATCH')
                    <label class="grid gap-2 text-sm font-bold">پاسخ به مدرس<textarea name="response" rows="3" minlength="2" maxlength="5000" required class="rounded-xl border border-slate-300 px-3 py-3 text-sm leading-7" placeholder="پاسخ خود را بنویس…">{{ old('response') }}</textarea></label>
                    <button class="min-h-10 justify-self-start rounded-xl bg-[var(--panel-primary)] px-5 text-sm font-black text-white">ارسال پاسخ</button>
                </form>
            @endif
        </article>
    @empty
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-6 text-center text-sm text-slate-500">پیام مستقیمی از طرف مدرس برای نمایش وجود ندارد.</div>
    @endforelse
    @if($messages->hasPages())<div>{{ $messages->links() }}</div>@endif
</section>
