@extends('layouts.auth')

@section('title', 'تأیید شماره موبایل')

@section('content')

    <div
        x-data="{
            code: ['', '', '', '', '', ''],
            get otp() {
                return this.code.join('')
            },
            focusNext(index) {
                if (this.code[index] && index < this.code.length - 1) {
                    this.$refs[`otp${index + 1}`].focus()
                }
            },
            focusPrevious(index, event) {
                if (event.key === 'Backspace' && !this.code[index] && index > 0) {
                    this.$refs[`otp${index - 1}`].focus()
                }
            },
            handlePaste(event) {
                event.preventDefault()

                const pasted = event.clipboardData
                    .getData('text')
                    .replace(/\D/g, '')
                    .slice(0, 6)

                pasted.split('').forEach((digit, index) => {
                    this.code[index] = digit
                })

                const nextIndex = Math.min(pasted.length, 5)

                this.$refs[`otp${nextIndex}`].focus()
            }
        }"
        class="rounded-3xl border border-[var(--color-border)] bg-[var(--color-surface)] p-6 shadow-[var(--shadow-sm)] sm:p-8"
        dir="rtl"
    >

        {{-- Header --}}
        <div class="text-center">

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[var(--color-brand-50)] text-[var(--color-brand-700)]">
                <svg
                    class="h-7 w-7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                    aria-hidden="true"
                >
                    <path d="M7.5 10V8a4.5 4.5 0 0 1 9 0v2" />
                    <rect x="4.5" y="10" width="15" height="10.5" rx="2" />
                    <path d="M12 14v2.5" />
                </svg>
            </div>

            <h1 class="mt-5 text-2xl font-black text-[var(--color-text-primary)]">
                کد تأیید را وارد کن
            </h1>

            <p class="mt-2 text-sm leading-7 text-[var(--color-text-secondary)]">
                کد ۶ رقمی ارسال‌شده به شماره
                <span class="font-bold text-[var(--color-text-primary)]">
                    ۰۹۱۲۱۲۳۴۵۶۷
                </span>
                را وارد کن.
            </p>

        </div>

        {{-- OTP Form --}}
        <form
            method="POST"
            action="#"
            class="mt-8"
        >
            @csrf

            <input
                type="hidden"
                name="otp"
                :value="otp"
            >

            <fieldset>
                <legend class="sr-only">
                    کد تأیید ۶ رقمی
                </legend>

                <div
                    class="flex justify-center gap-2 sm:gap-3"
                    dir="ltr"
                    @paste="handlePaste($event)"
                >
                    @for($i = 0; $i < 6; $i++)
                        <input
                            x-ref="otp{{ $i }}"
                            x-model="code[{{ $i }}]"
                            type="text"
                            inputmode="numeric"
                            autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                            maxlength="1"
                            pattern="[0-9]*"
                            aria-label="رقم {{ $i + 1 }} کد تأیید"
                            class="
                                h-12
                                w-11
                                rounded-xl
                                border
                                border-[var(--color-border)]
                                bg-[var(--color-surface)]
                                text-center
                                text-lg
                                font-extrabold
                                text-[var(--color-text-primary)]
                                outline-none
                                transition-all
                                duration-150
                                focus:border-[var(--color-brand-500)]
                                focus:ring-4
                                focus:ring-[color-mix(in_srgb,var(--color-brand-200)_70%,transparent)]
                                sm:h-14
                                sm:w-12
                            "
                            @input="code[{{ $i }}] = $event.target.value.replace(/\D/g, '').slice(-1); focusNext({{ $i }})"
                            @keydown="focusPrevious({{ $i }}, $event)"
                        >
                    @endfor
                </div>
            </fieldset>

            {{-- Submit --}}
            <div class="mt-7">
                <x-ui.button
                    type="submit"
                    size="lg"
                    full-width
                >
                    تأیید و ادامه

                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </x-ui.button>
            </div>

        </form>

        {{-- Resend --}}
        <div class="mt-6 text-center">

            <p class="text-sm text-[var(--color-text-secondary)]">
                کد را دریافت نکردی؟
            </p>

            <button
                type="button"
                class="mt-2 text-sm font-bold text-[var(--color-brand-600)] transition-colors hover:text-[var(--color-brand-700)]"
            >
                ارسال مجدد کد
            </button>

        </div>

        {{-- Change phone --}}
        <div class="mt-6 border-t border-[var(--color-border)] pt-6 text-center">

            <a
                href="#"
                class="inline-flex items-center gap-2 text-sm font-semibold text-[var(--color-text-secondary)] transition-colors hover:text-[var(--color-brand-600)]"
            >
                <svg
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    aria-hidden="true"
                >
                    <path d="m15 18-6-6 6-6" />
                </svg>

                تغییر شماره موبایل
            </a>

        </div>

    </div>

@endsection
