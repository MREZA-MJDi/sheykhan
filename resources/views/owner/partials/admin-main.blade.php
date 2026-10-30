    <main class="main main-content" id="main" tabindex="-1">
        @if(session('success') || session('error') || $errors->any())
            <section class="owner-dashboard-feedback c12" aria-live="polite">
                @if(session('success'))<div class="alert is-success" role="status">{{ session('success') }}</div>@endif
                @if(session('error'))<div class="alert is-error" role="alert">{{ session('error') }}</div>@endif
                @if($errors->any())<div class="alert is-error" role="alert"><strong>اطلاعات فرم نیاز به اصلاح دارد.</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            </section>
        @endif
        @yield('content')
    </main>