<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} – Receipt Photobooth</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            font-family: 'DM Sans', system-ui, sans-serif;
        }
    </style>
</head>

<body class="antialiased bg-stone-50 text-stone-900 min-h-screen">
    {{-- Header --}}
    <header class="border-b border-stone-200/80 bg-white/80 backdrop-blur-sm sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="h-10 w-auto">
            <nav class="flex items-center gap-4">
                <a href="{{ route('gallery') }}" class="text-sm font-medium text-stone-600 hover:text-blue-600">Gallery</a>
                @auth
                <a href="{{ url('/admin') }}" class="text-sm font-medium text-stone-600 hover:text-blue-600">Dashboard</a>
                <a href="{{ url('/admin') }}" class="rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stblue-600">Masuk Admin</a>
                @else
                <a href="{{ route('login') }}" class="text-sm font-medium text-stone-600 hover:text-blue-600">Masuk</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6">
        {{-- Hero --}}
        <section class="relative flex justify-center items-center mb-20 h-[80vh] md:h-[90vh]">
            <div class="text-center">
                <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }}" class="mx-auto w-24 md:w-36 -mb-4">
                <h1 class="text-4xl sm:text-5xl font-bold text-blue-700 tracking-tight mb-4">
                    Turn Your Moments Into Receipt Memories
                </h1>
                <p class="text-sm sm:text-base text-stone-600 max-w-2xl mx-auto mb-8">
                    Hadirkan pengalaman foto unik dan aesthetic kini hadir di Dabo Singkep!
                </p>
                @auth
                <a href="{{ url('/admin/projects') }}" class="inline-flex items-center gap-2 rounded-xl bg-stone-900 px-6 py-3.5 text-base font-semibold text-white hover:bg-stone-800 transition">
                    Kelola Project
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                @else
                <button onclick="openModal()" class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-6 py-2.5 text-base font-semibold text-white hover:bg-blue-600 transition">
                    Beli Voucher
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </button>
                @endauth

                {{-- Beli Voucher Modal --}}
                @include('beli-voucher')
            </div>
        </section>

        {{-- Features --}}
        <section class="mb-40">
            <div>
                <div class="md:flex md:justify-between md:gap-16 max-w-7xl px-4">
                    <div class="flex-2 mx-auto max-w-2xl mb-16">
                        <h2 class="text-3xl font-semibold tracking-tight text-pretty text-blue-700 sm:text-4xl lg:text-balance">
                            Why Choose {{ config('app.name') }}?
                        </h2>
                    </div>
                    <div class="mx-auto max-w-2xl lg:max-w-4xl">
                        <dl class="grid max-w-xl grid-cols-1 gap-x-4 gap-y-5 lg:max-w-none lg:grid-cols-2 lg:gap-x-4">
                            <div class="relative pl-16">
                                <dt class="text-base/7 font-semibold text-blue-700">
                                    <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-600">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-white">
                                            <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" />
                                        </svg>
                                    </div>
                                    Instant Results
                                </dt>
                                <dd class="mt-2 text-base/7 text-stone-00">
                                    Proses cetak hanya hitungan detik. Tanpa tinta, tanpa menunggu kertas kering. Begitu difoto, langsung keluar!
                                </dd>
                            </div>
                            <div class="relative pl-16">
                                <dt class="text-base/7 font-semibold text-blue-700">
                                    <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-600">
                                        <svg viewBox="0 0 24 24" fill="none" class="w-6 h-6">
                                            <path d="M9 9H15M9 12H15M9 15H15M5 3V21L8 19L10 21L12 19L14 21L16 19L19 21V3L16 5L14 3L12 5L10 3L8 5L5 3Z" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    Aesthetic Templates
                                </dt>
                                <dd class="mt-2 text-base/7 text-stone-00">Tersedia berbagai pilihan template dengan gaya estetik seperti 90s, grunge, minimalis, hingga modern.</dd>
                            </div>
                            <div class="relative pl-16">
                                <dt class="text-base/7 font-semibold text-blue-700">
                                    <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-600">
                                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </div>
                                    QR Code & Soft Copy
                                </dt>
                                <dd class="mt-2 text-base/7 text-stone-00">Selain cetak fisik, tamu bisa langsung download foto via QR Code untuk dibagikan ke media sosial.</dd>
                            </div>
                            <div class="relative pl-16">
                                <dt class="text-base/7 font-semibold text-blue-700">
                                    <div class="absolute top-0 left-0 flex size-10 items-center justify-center rounded-lg bg-blue-600">
                                        <svg viewBox="0 0 24 24" fill="none" class="w-6 h-6">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.29298 3.29289C2.90246 3.68342 2.90246 4.31658 3.29298 4.70711L4.29298 5.70711C4.68351 6.09763 5.31667 6.09763 5.7072 5.70711C6.09772 5.31658 6.09772 4.68342 5.7072 4.29289L4.7072 3.29289C4.31667 2.90237 3.68351 2.90237 3.29298 3.29289ZM19.293 3.29289C19.6835 2.90237 20.3167 2.90237 20.7072 3.29289C21.0977 3.68342 21.0977 4.31658 20.7072 4.70711L19.7072 5.70711C19.3167 6.09763 18.6835 6.09763 18.293 5.70711C17.9025 5.31658 17.9025 4.68342 18.293 4.29289L19.293 3.29289ZM19.7072 14.2929L20.7072 15.2929C21.0977 15.6834 21.0977 16.3166 20.7072 16.7071C20.3167 17.0976 19.6835 17.0976 19.293 16.7071L18.293 15.7071C17.9025 15.3166 17.9025 14.6834 18.293 14.2929C18.6835 13.9024 19.3167 13.9024 19.7072 14.2929ZM3.29298 16.7071C2.90246 16.3166 2.90246 15.6834 3.29298 15.2929L4.29298 14.2929C4.68351 13.9024 5.31667 13.9024 5.7072 14.2929C6.09772 14.6834 6.09772 15.3166 5.7072 15.7071L4.7072 16.7071C4.31667 17.0976 3.68351 17.0976 3.29298 16.7071ZM20.4142 11C20.9665 11.0001 21.4142 10.5524 21.4142 10.0001C21.4143 9.44781 20.9666 9.00007 20.4143 9.00003L20.0001 9C19.4478 8.99996 19.0001 9.44765 19 9.99993C19 10.5522 19.4477 11 20 11L20.4142 11ZM2 10.2072C2.00004 10.7595 2.44779 11.2072 3.00007 11.2071L3.41428 11.2071C3.96657 11.2071 4.41425 10.7593 4.41421 10.207C4.41417 9.65474 3.96643 9.20705 3.41414 9.20709L2.99993 9.20712C2.44765 9.20716 1.99996 9.65491 2 10.2072ZM15.4366 14.9189C16.9865 13.8341 18 12.0354 18 10C18 6.68629 15.3137 4 12 4C8.68629 4 6 6.68629 6 10C6 12.0354 7.0135 13.8341 8.56337 14.9189C8.21123 15.3497 8 15.9002 8 16.5C8 17.7099 8.85949 18.7191 10.0012 18.9502C10.0004 18.9667 10 18.9833 10 19C10 19.5523 10.4477 20 11 20H13C13.5523 20 14 19.5523 14 19C14 18.9833 13.9996 18.9667 13.9988 18.9502C15.1405 18.7191 16 17.7099 16 16.5C16 15.9002 15.7888 15.3497 15.4366 14.9189ZM12 14C14.2091 14 16 12.2091 16 10C16 7.79086 14.2091 6 12 6C9.79086 6 8 7.79086 8 10C8 12.2091 9.79086 14 12 14ZM12 16H11.9146H10.5C10.2239 16 10 16.2239 10 16.5C10 16.7761 10.2239 17 10.5 17H11.9146H12.0854H13.5C13.7761 17 14 16.7761 14 16.5C14 16.2239 13.7761 16 13.5 16H12.0854H12Z" fill="#ffffff"></path>
                                            </g>
                                        </svg>
                                    </div>
                                    Unique Concept
                                </dt>
                                <dd class="mt-2 text-base/7 text-stone-00">Menghadirkan konsep photobooth berbentuk struk yang unik dan berbeda, memberikan pengalaman baru dalam mengabadikan momen.</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>
        </section>

        {{-- Flow --}}
        <section class="mb-40">
            <h2 class="text-3xl text-center font-semibold tracking-tight text-pretty text-blue-700 sm:text-4xl lg:text-balance mb-10">
                How It Works?
            </h2>
            <div class="mx-auto max-w-2xl md:max-w-4xl px-10">
                <dl class="grid max-w-xl grid-cols-1 gap-x-4 gap-y-5 md:max-w-none md:grid-cols-3 md:gap-x-4">
                    <div class="relative pl-16 bg-blue-600 rounded-lg p-6 shadow-2xl">
                        <dt class="text-base/7 font-semibold text-white">
                            <div class="absolute top-7 left-5 flex size-8 items-center justify-center rounded-md bg-white">
                                <h3 class="font-semibold text-2xl text-blue-600">1</h3>
                            </div>
                            Pilih Template
                        </dt>
                        <dd class="mt-2 text-sm text-slate-300">
                            Pilih template sesuai gaya dan mood kamu.
                        </dd>
                    </div>
                    <div class="relative pl-16 bg-blue-600 rounded-lg p-6 shadow-2xl">
                        <dt class="text-base/7 font-semibold text-white">
                            <div class="absolute top-7 left-5 flex size-8 items-center justify-center rounded-md bg-white">
                                <h3 class="font-semibold text-2xl text-blue-600">2</h3>
                            </div>
                            Ambil & Print Foto
                        </dt>
                        <dd class="mt-2 text-sm text-slate-300">
                            Ambil foto, lalu langsung diproses dan dicetak dalam format struk.
                        </dd>
                    </div>
                    <div class="relative pl-16 bg-blue-600 rounded-lg p-6 shadow-2xl">
                        <dt class="text-base/7 font-semibold text-white">
                            <div class="absolute top-7 left-5 flex size-8 items-center justify-center rounded-md bg-white">
                                <h3 class="font-semibold text-2xl text-blue-600">3</h3>
                            </div>
                            Scan QR & Download
                        </dt>
                        <dd class="mt-2 text-sm text-slate-300">
                            Scan QR untuk download dan share hasil fotomu.
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

    </main>

    <footer class="border-t border-stone-200/80 py-6 text-center text-sm text-stone-500">
        <div>
            <span class="text-blue-600">
                &copy; {{ date('Y') }}
                {{ config('app.name') }}
            </span>
            <span> | </span>
            <span class="hover:text-blue-600"><a href="https://www.instagram.com/farid.pahlevi" target="_blank">Instagram</a></span>
        </div>
    </footer>
</body>

</html>