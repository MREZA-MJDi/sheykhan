@if(session('success'))
<div role="status" aria-live="polite" class="mb-5 rounded-2xl border border-[var(--color-success-100)] bg-[var(--color-success-50)] px-4 py-3 text-sm font-semibold text-[var(--color-success-700)]">{{ session('success') }}</div>
@endif
@if(session('error'))
<div role="alert" aria-live="assertive" class="mb-5 rounded-2xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] px-4 py-3 text-sm font-semibold text-[var(--color-danger-600)]">{{ session('error') }}</div>
@endif
@if($errors->any())
<div role="alert" aria-live="assertive" class="mb-5 rounded-2xl border border-[var(--color-danger-100)] bg-[var(--color-danger-50)] px-4 py-3 text-sm text-[var(--color-danger-600)]"><div class="font-black">عملیات انجام نشد.</div><ul class="mt-2 grid gap-1 text-xs leading-6">@foreach($errors->all() as $error)<li>• {{ $error }}</li>@endforeach</ul></div>
@endif