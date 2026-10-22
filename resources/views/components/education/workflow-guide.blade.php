@props(['role' => 'student', 'expanded' => false])

@php($isTeacher = $role === 'teacher')

<details class="education-workflow-guide {{ $isTeacher ? 'is-teacher' : 'is-student' }}" @if($expanded) open @endif>
    <summary>
        <span class="education-workflow-guide-mark" aria-hidden="true">?</span>
        <span class="education-workflow-guide-title">
            <strong>راهنمای سریع شیخان</strong>
            <small>فرق دوره، کلاس، جلسه، ضبط، تکلیف و آزمون</small>
        </span>
        <span class="education-workflow-guide-toggle" aria-hidden="true">⌄</span>
    </summary>
    <div class="education-workflow-guide-body">
        <div class="education-workflow-definitions">
            <article><span>۱</span><div><strong>دوره یعنی «چه چیزی یاد می‌گیرم؟»</strong><p>مسیر آموزشی شامل سرفصل‌ها و درس‌هاست؛ منابع، تمرین‌ها و آزمون‌های مرتبط هم به آن وصل می‌شوند.</p></div></article>
            <article><span>۲</span><div><strong>کلاس یعنی «با چه گروهی و کدام مدرس؟»</strong><p>گروهی از دانش‌آموزان که برای یک دوره مشخص، مدرس، ظرفیت و برنامه دارد. کلاس جای دوره را نمی‌گیرد؛ به یک دوره متصل می‌شود.</p></div></article>
            <article><span>۳</span><div><strong>جلسه آنلاین یعنی یک نوبت مشخص</strong><p>جلسه تاریخ و ساعت و لینک ورود دارد. برنامه هفتگی زمان تکرارشونده را نشان می‌دهد؛ جلسه آنلاین یک رویداد مشخص است.</p></div></article>
            <article><span>۴</span><div><strong>ضبط جلسه یعنی بازپخش بعد از انتشار</strong><p>فقط وقتی فایل ضبط معتبر و زمان انتشارش رسیده باشد نمایش داده می‌شود؛ لینک اتاق زنده و ضبط یکی نیستند.</p></div></article>
        </div>
        @if($isTeacher)
            <div class="education-workflow-guide-next"><strong>ترتیب کار مدرس</strong><ol>
                <li>از «دوره‌های من» دوره اختصاص‌یافته را انتخاب کن.</li>
                <li>سرفصل و درس را تکمیل کن و محتوای معتبر اضافه کن.</li>
                <li>در «کلاس‌های من» گروه را به همان دوره وصل کن؛ همه خریداران دوره لزوماً عضو این گروه نیستند.</li>
                <li>برنامه هفتگی را تنظیم و برای هر نوبت مشخص، جلسه آنلاین جداگانه بساز.</li>
                <li>تکلیف یا آزمون را به دوره و در صورت نیاز کلاس وصل کن؛ سپس پاسخ‌های ارسال‌شده را تصحیح و بازخورد را ثبت کن.</li>
            </ol></div>
        @else
            <div class="education-workflow-guide-next"><strong>ترتیب کار دانش‌آموز</strong><ol>
                <li>از «خانه یادگیری» کار امروز، موعد تکلیف و جلسه بعدی را ببین.</li>
                <li>از «دوره‌های من» وارد سرفصل و درس شو؛ محتوای دوره پولی فقط با ثبت‌نام و پرداخت معتبر قابل مشاهده است.</li>
                <li>از «کلاس‌های من» برنامه و جلسه تاریخ‌دار را ببین؛ ورود فقط در بازه مجاز فعال می‌شود.</li>
                <li>در «تکالیف» پاسخ را ثبت یا پیوست کن و وضعیت ارسال و تصحیح را پیگیری کن.</li>
                <li>در «آزمون‌ها» دستورالعمل را بخوان، پاسخ بده و تحویل بده؛ نتیجه نهایی بعد از تصحیح در کارنامه نمایش داده می‌شود.</li>
            </ol></div>
        @endif
    </div>
</details>
<style>
.education-workflow-guide{margin:0 0 18px;border:1px solid #e3e8f0;border-radius:18px;background:#fff;box-shadow:0 7px 22px rgba(20,34,63,.045);overflow:hidden;color:#182033}
.education-workflow-guide.is-student{--guide-accent:#0d8fca;--guide-soft:#e9f8ff}.education-workflow-guide.is-teacher{--guide-accent:#5b5ce8;--guide-soft:#eef0ff}
.education-workflow-guide>summary{list-style:none;cursor:pointer;display:flex;align-items:center;gap:12px;padding:14px 16px}.education-workflow-guide>summary::-webkit-details-marker{display:none}
.education-workflow-guide-mark{display:grid;place-items:center;flex:0 0 34px;height:34px;border-radius:11px;background:var(--guide-soft);color:var(--guide-accent);font-size:17px;font-weight:900}
.education-workflow-guide-title{display:grid;gap:3px;flex:1;min-width:0}.education-workflow-guide-title strong{font-size:13px;font-weight:900}.education-workflow-guide-title small{font-size:11px;color:#687386;line-height:1.8}
.education-workflow-guide-toggle{color:#677389;font-size:18px;transition:transform .18s}.education-workflow-guide[open] .education-workflow-guide-toggle{transform:rotate(180deg)}
.education-workflow-guide-body{padding:0 16px 17px;display:grid;gap:14px}.education-workflow-definitions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.education-workflow-definitions article{min-width:0;display:flex;gap:10px;padding:13px;border:1px solid #edf0f5;border-radius:13px;background:#fbfcfe}
.education-workflow-definitions article>span{flex:0 0 25px;height:25px;display:grid;place-items:center;border-radius:8px;background:var(--guide-soft);color:var(--guide-accent);font-size:11px;font-weight:900}
.education-workflow-definitions strong,.education-workflow-guide-next>strong{display:block;font-size:11px;line-height:1.9;font-weight:900}.education-workflow-definitions p{margin:4px 0 0;color:#586579;font-size:10px;line-height:2}
.education-workflow-guide-next{border-radius:13px;padding:14px;background:#f8fafc}.education-workflow-guide-next>strong{color:var(--guide-accent);font-size:12px}
.education-workflow-guide-next ol{margin:8px 0 0;padding:0 18px 0 0;display:grid;gap:6px}.education-workflow-guide-next li{padding-right:2px;color:#455167;font-size:10px;line-height:2}
@media(max-width:700px){.education-workflow-definitions{grid-template-columns:1fr}.education-workflow-guide>summary{padding:12px}.education-workflow-guide-body{padding:0 12px 13px}.education-workflow-definitions article{padding:11px}.education-workflow-guide-next li{font-size:11px}}
</style>