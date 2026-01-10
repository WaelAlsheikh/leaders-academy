<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>{{ $title ?? __('messages.Leaders Institute') }}</title>

  <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
  
              <!-- كليات الجامعة (dropdown) -->
				<div class="dropdown">
  					<a href="#" class="dropdown-toggle">
    					{{ __('messages.University Faculties') }} <i class="fa-solid fa-chevron-down"></i>
  					</a>
  					<div class="dropdown-menu">
    					@if(isset($allColleges) && $allColleges->count())
      						@foreach($allColleges as $college)
        						<a href="{{ route('colleges.show', $college->slug) }}">{{ $college->title }}</a>
      						@endforeach
    					@else
      						<a href="#">{{ __('messages.No colleges available') }}</a>
    					@endif
  					</div>
				</div>

              {{-- القائمة المنسدلة للبرامج --}}
              <div class="dropdown">
  				<a href="#" class="dropdown-toggle">
    				{{ __('messages.Programs') }} <i class="fa-solid fa-chevron-down"></i>
  				</a>
  				<div class="dropdown-menu">
      				@if(isset($allPrograms) && count($allPrograms) > 0)
          				@foreach($allPrograms as $program)
              				<a href="{{ route('programs.show', $program->slug) }}">{{ $program->title }}</a>
          				@endforeach
      				@else
          				<a href="#">{{ __('messages.No programs available') }}</a>
      				@endif
  				</div>
			  </div>
              
              <!-- زر البرامج التدريبية -->
				<div class="dropdown">
  					<a href="#" class="dropdown-toggle">
    					{{ __('messages.Training Programs') }} <i class="fa-solid fa-chevron-down"></i>
  					</a>
  					<div class="dropdown-menu">
    					@if(isset($allTrainingPrograms) && $allTrainingPrograms->count())
      						@foreach($allTrainingPrograms as $tp)
        						<a href="{{ route('training.show', $tp->slug) }}">{{ $tp->title }}</a>
      						@endforeach
    					@else
      						<a href="#">{{ __('messages.No programs available') }}</a>
    					@endif
  					</div>
				</div>

              <!-- منصة الطالب (dropdown) -->
				<div class="dropdown">
  					<a href="#" class="dropdown-toggle">
    					{{ __('messages.Student Platform') }} <i class="fa-solid fa-chevron-down"></i>
  					</a>
  					<div class="dropdown-menu">
    					@if(isset($allStudentPlatforms) && $allStudentPlatforms->count())
      						@foreach($allStudentPlatforms as $sp)
        						<a href="{{ route('student-platform.show', $sp->slug) }}">{{ $sp->title }}</a>
      						@endforeach
    					@else
      						<a href="#">{{ __('messages.No items available') }}</a>
    					@endif
  					</div>
				</div>

              <a href="{{ route('accreditations.index') }}">{{ __('messages.Accreditations') }}</a>
              <a href="{{ route('student.login') }}">{{ __('messages.Virtual university') }}</a>
              <a href="{{ route('contact') }}">{{ __('messages.Contact') }}</a>

              {{-- زر تبديل اللغة --}}
              @if(app()->getLocale() === 'ar')
                <a href="{{ route('lang.switch', 'en') }}" class="lang-btn" title="Switch to English">
                  <i class="fa-solid fa-globe"></i> EN
                </a>
              @else
                <a href="{{ route('lang.switch', 'ar') }}" class="lang-btn" title="تبديل إلى العربية">
                  <i class="fa-solid fa-globe"></i> AR
                </a>
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

    <!-- Social icons: WhatsApp & Facebook -->
<div class="footer-socials" aria-label="Social links">
  <!-- WhatsApp (business) -->
  <a href="https://wa.me/963965121776" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp" class="social-link whatsapp-link" title="WhatsApp">
    <i class="fa-brands fa-whatsapp" aria-hidden="true"></i>
    <span class="sr-only">WhatsApp</span>
  </a>

  <!-- Facebook -->
  <a href="https://www.facebook.com/share/1CpxMp7cSB/" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="social-link facebook-link" title="Facebook">
    <i class="fa-brands fa-facebook-f" aria-hidden="true"></i>
    <span class="sr-only">Facebook</span>
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
