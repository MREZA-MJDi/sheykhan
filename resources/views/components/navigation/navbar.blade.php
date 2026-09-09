<!DOCTYPE html>
<html lang="fa" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فرزین | آموزش تخصصی</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
</head>

<body class="bg-gray-50">

<nav x-data="{ isOpen: false }"
     class="relative bg-white shadow-sm border-b border-gray-100">

    <div class="container mx-auto px-6 py-4 md:flex md:justify-between md:items-center">

        <!-- Logo + Mobile Button -->
        <div class="flex items-center justify-between">

            <!-- Logo -->
            <a href="#"
               class="flex items-center gap-3">

                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gray-900 text-white font-bold text-lg">
                    ف
                </div>

                <div class="leading-tight">
                        <span class="block text-lg font-bold text-gray-900">
                            فرزین
                        </span>

                    <span class="block text-xs text-gray-500">
                            مرکز آموزش تخصصی
                        </span>
                </div>

            </a>

            <!-- Mobile Menu Button -->
            <div class="flex lg:hidden">

                <button
                    x-cloak
                    @click="isOpen = !isOpen"
                    type="button"
                    class="text-gray-600 hover:text-gray-900 focus:outline-none"
                    aria-label="باز کردن منو">

                    <!-- Open -->
                    <svg
                        x-show="!isOpen"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M4 8h16M4 16h16" />
                    </svg>

                    <!-- Close -->
                    <svg
                        x-show="isOpen"
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-6 h-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>

                </button>

            </div>

        </div>


        <!-- Navigation -->
        <div
            x-cloak
            :class="[isOpen ? 'translate-x-0 opacity-100' : 'opacity-0 -translate-x-full']"
            class="absolute inset-x-0 z-20 w-full px-6 py-5
                       transition-all duration-300 ease-in-out
                       bg-white border-b border-gray-100
                       md:mt-0 md:p-0 md:relative md:border-0
                       md:bg-transparent md:w-auto md:opacity-100
                       md:translate-x-0 md:flex md:items-center">

            <!-- Links -->
            <div class="flex flex-col md:flex-row md:items-center md:mx-6">

                <a
                    href="#"
                    class="my-2 text-gray-700 transition-colors duration-300
                               hover:text-gray-950 md:mx-4 md:my-0">

                    خانه
                </a>

                <a
                    href="#"
                    class="my-2 text-gray-700 transition-colors duration-300
                               hover:text-gray-950 md:mx-4 md:my-0">

                    دوره‌ها
                </a>

                <a
                    href="#"
                    class="my-2 text-gray-700 transition-colors duration-300
                               hover:text-gray-950 md:mx-4 md:my-0">

                    اساتید
                </a>

                <a
                    href="#"
                    class="my-2 text-gray-700 transition-colors duration-300
                               hover:text-gray-950 md:mx-4 md:my-0">

                    مقالات
                </a>

                <a
                    href="#"
                    class="my-2 text-gray-700 transition-colors duration-300
                               hover:text-gray-950 md:mx-4 md:my-0">

                    درباره فرزین
                </a>

                <a
                    href="#"
                    class="my-2 text-gray-700 transition-colors duration-300
                               hover:text-gray-950 md:mx-4 md:my-0">

                    تماس با ما
                </a>

            </div>


            <!-- Account -->
            <div class="flex items-center justify-center md:mr-4 md:block">

                <a
                    href="#"
                    class="flex items-center justify-center gap-2
                               px-4 py-2.5
                               rounded-xl
                               bg-gray-900
                               text-white
                               text-sm font-medium
                               transition-all duration-300
                               hover:bg-gray-800">

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-5 h-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8">

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0" />

                    </svg>

                    ورود / ثبت‌نام

                </a>

            </div>

        </div>

    </div>

</nav>

</body>

</html>
