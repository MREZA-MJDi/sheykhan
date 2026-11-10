    <main class="main main-content" id="main" tabindex="-1">
        @if(session('success') || session('error') || $errors->any())
            <section class="owner-dashboard-feedback c12" aria-live="polite">
                @if(session('success'))<div class="alert is-success" role="status">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert is-error" role="alert">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert is-error" role="alert"><strong>لطفاً موارد زیر را بررسی و اصلاح کن:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            </section>
        @endif
        @yield('content')
    </main>
    <dialog class="owner-floating-modal" data-owner-record-modal aria-labelledby="owner-record-modal-title">
        <header class="owner-floating-modal__header">
            <div class="min-w-0">
                <span class="owner-record-modal-kicker">مدیریت اطلاعات</span>
                <h2 id="owner-record-modal-title" data-owner-record-modal-title>ویرایش / ثبت اطلاعات</h2>
            </div>
            <button type="button" class="owner-modal-close" data-owner-modal-close aria-label="بستن پنجره">×</button>
        </header>
        <div class="owner-record-modal-body" data-owner-record-modal-body>
            <p class="owner-modal-loading">فرم آماده بارگذاری است.</p>
        </div>
    </dialog>