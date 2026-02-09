<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? __('messages.Leaders Institute') }}</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Site CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">

    <!-- Breeze / Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }
    </style>
</head>

<body>

<header class="navbar">
    <div class="container">
        <div class="logo">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Leaders Logo">
            </a>
            <span>{{ __('messages.Leaders Institute') }}</span>
        </div>

        <div class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </div>

        <nav id="nav-menu">
            <a href="{{ route('home') }}">{{ __('messages.Home') }}</a>

            {{-- الكليات --}}
            <div class="dropdown">
                <a href="#" class="dropdown-toggle">
                    {{ __('messages.University Faculties') }} <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    @forelse($allColleges ?? [] as $college)
                        <a href="{{ route('colleges.show', $college->slug) }}">{{ $college->title }}</a>
                    @empty
                        <a href="#">{{ __('messages.No colleges available') }}</a>
                    @endforelse
                </div>
            </div>

            {{-- البرامج --}}
            <div class="dropdown">
                <a href="#" class="dropdown-toggle">
                    {{ __('messages.Programs') }} <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    @forelse($allPrograms ?? [] as $program)
                        <a href="{{ route('programs.show', $program->slug) }}">{{ $program->title }}</a>
                    @empty
                        <a href="#">{{ __('messages.No programs available') }}</a>
                    @endforelse
                </div>
            </div>

            {{-- البرامج التدريبية --}}
            <div class="dropdown">
                <a href="#" class="dropdown-toggle">
                    {{ __('messages.Training Programs') }} <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    @forelse($allTrainingPrograms ?? [] as $tp)
                        <a href="{{ route('training.show', $tp->slug) }}">{{ $tp->title }}</a>
                    @empty
                        <a href="#">{{ __('messages.No programs available') }}</a>
                    @endforelse
                </div>
            </div>

            {{-- منصة الطالب --}}
            <div class="dropdown">
                <a href="#" class="dropdown-toggle">
                    {{ __('messages.Student Platform') }} <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-menu">
                    @forelse($allStudentPlatforms ?? [] as $sp)
                        <a href="{{ route('student-platform.show', $sp->slug) }}">{{ $sp->title }}</a>
                    @empty
                        <a href="#">{{ __('messages.No items available') }}</a>
                    @endforelse
                </div>
            </div>

            <a href="{{ route('accreditations.index') }}">{{ __('messages.Accreditations') }}</a>
            <a href="{{ route('student.login') }}">{{ __('messages.Virtual university') }}</a>
            <a href="{{ route('contact') }}">{{ __('messages.Contact') }}</a>

            {{-- اللغة --}}
            @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="lang-btn">EN</a>
            @else
                <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn">AR</a>
            @endif
        </nav>
    </div>
</header>

<main class="site-content">
    @yield('content')
</main>

<footer class="footer">
    <div class="container">
        <p>© {{ date('Y') }} {{ __('messages.Leaders Institute - All Rights Reserved') }}</p>

        <div class="footer-socials">
            <a href="https://wa.me/963965121776" target="_blank" class="social-link whatsapp-link">
                <i class="fa-brands fa-whatsapp"></i>
            </a>
            <a href="https://www.facebook.com/share/1CpxMp7cSB/" target="_blank" class="social-link facebook-link">
                <i class="fa-brands fa-facebook-f"></i>
            </a>
        </div>
    </div>
</footer>

<script>
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('nav-menu');

    if (hamburger) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });
    }
</script>

@stack('scripts')

</body>
</html>
