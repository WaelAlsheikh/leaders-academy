@extends('layouts.app')

@section('content')

<main>

<!-- ===== Hero Section ===== -->
<section class="hero" style="background-image: url('{{ asset('assets/images/hero-bg.jpg') }}');">
    <div class="overlay"></div>
    <div class="hero-content">
        <h1>{{ $about->title ?? 'أكاديمية ليدرز' }}</h1>
        <p>{!! Str::limit(strip_tags($about->content ?? ''), 200) !!}</p>
        <a href="{{ route('contact') }}" class="btn-primary">
            {{ __('messages.Contact Us') }}
        </a>
    </div>
</section>

<!-- ===== Colleges (Swiper) ===== -->
<section id="colleges" class="section" style="padding-top:48px; padding-bottom:48px;">
  <div class="container">
    <h2 style="text-align:center; margin-bottom:22px;">test</h2>

    <div class="three-cards-swiper" data-swiper-id="colleges" dir="rtl" aria-label="colleges carousel">
      <!-- Navigation buttons (local to container) -->
      <button class="three-cards-btn swiper-button-prev" aria-label="السابق" title="السابق">‹</button>

      <!-- Swiper -->
      <div class="swiper three-swiper" dir="rtl">
        <div class="swiper-wrapper">
          @if(isset($colleges) && $colleges->count() > 0)
            @foreach($colleges as $college)
              <div class="swiper-slide" role="group">
                <div class="program-card-wrapper">
                  <div class="card program-card" onclick="location.href='{{ route('colleges.show', $college->slug ?? $college->id) }}'">
                    <div class="card-media">
                      @php
                        $collegeImage = !empty($college->image) ? asset('storage/' . $college->image) : asset('assets/images/placeholder.png');
                      @endphp
                      <img src="{{ $collegeImage }}" alt="{{ $college->title }}" onerror="this.src='{{ asset('assets/images/placeholder.png') }}'">
                    </div>

                    <div class="card-body">
                      <h3 class="program-title">{{ $college->title }}</h3>
                      <p class="program-short">
                        {{ Str::limit(strip_tags($college->short_description ?? $college->long_description ?? ''), 140) }}
                      </p>
                      <div class="program-actions" style="text-align:left;">
                        <a href="{{ route('colleges.show', $college->slug ?? $college->id) }}" class="btn-secondary">
                          {{ __('messages.Read More') }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="swiper-slide">
              <div class="program-card-wrapper">
                <p style="text-align:center; color:#666;">لا توجد كليات للعرض حالياً.</p>
              </div>
            </div>
          @endif
        </div>

        <!-- pagination -->
        <div class="swiper-pagination" aria-hidden="true"></div>
      </div>

      <button class="three-cards-btn swiper-button-next" aria-label="التالي" title="التالي">›</button>
    </div>
  </div>
</section>

<!-- ===== University Programs (Swiper) ===== -->
<section id="university-programs" class="section" style="padding-top:48px; padding-bottom:48px;">
  <div class="container">
    <h2 style="text-align:center; margin-bottom:22px;">{{ __('messages.Programs') }}</h2>

    <div class="three-cards-swiper" data-swiper-id="university-programs" dir="rtl" aria-label="university programs carousel">
      <button class="three-cards-btn swiper-button-prev" aria-label="السابق" title="السابق">‹</button>

      <div class="swiper three-swiper" dir="rtl">
        <div class="swiper-wrapper">
          @if(isset($universityPrograms) && $universityPrograms->count() > 0)
            @foreach($universityPrograms as $upro)
              <div class="swiper-slide" role="group">
                <div class="program-card-wrapper">
                  <div class="card program-card" onclick="location.href='{{ route('programs.show', $upro->slug ?? $upro->id) }}'">
                    <div class="card-media">
                      @php
                        $uImage = !empty($upro->image) ? asset('storage/' . $upro->image) : asset('assets/images/placeholder.png');
                      @endphp
                      <img src="{{ $uImage }}" alt="{{ $upro->title }}" onerror="this.src='{{ asset('assets/images/placeholder.png') }}'">
                    </div>

                    <div class="card-body">
                      <h3 class="program-title">{{ $upro->title }}</h3>
                      <p class="program-short">
                        {{ Str::limit(strip_tags($upro->short_description ?? $upro->long_description ?? ''), 140) }}
                      </p>
                      <div class="program-actions" style="text-align:left;">
                        <a href="{{ route('programs.show', $upro->slug ?? $upro->id) }}" class="btn-secondary">
                          {{ __('messages.Read More') }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="swiper-slide">
              <div class="program-card-wrapper">
                <p style="text-align:center; color:#666;">لا توجد برامج الجامعة للعرض حالياً.</p>
              </div>
            </div>
          @endif
        </div>

        <div class="swiper-pagination" aria-hidden="true"></div>
      </div>

      <button class="three-cards-btn swiper-button-next" aria-label="التالي" title="التالي">›</button>
    </div>
  </div>
</section>

<!-- ===== Training Programs (Swiper) ===== -->
<section id="training-programs" class="section" style="padding-top:48px; padding-bottom:48px;">
  <div class="container">
    <h2 style="text-align:center; margin-bottom:22px;">{{ __('messages.Training Programs') }}</h2>

    <div class="three-cards-swiper" data-swiper-id="training-programs" dir="rtl" aria-label="training programs carousel">
      <button class="three-cards-btn swiper-button-prev" aria-label="السابق" title="السابق">‹</button>

      <div class="swiper three-swiper" dir="rtl">
        <div class="swiper-wrapper">
          @if(isset($trainingPrograms) && $trainingPrograms->count() > 0)
            @foreach($trainingPrograms as $program)
              <div class="swiper-slide" role="group">
                <div class="program-card-wrapper">
                  <div class="card program-card" onclick="location.href='{{ route('programs.show', $program->slug ?? $program->id) }}'">
                    <div class="card-media">
                      @php
                        $progImage = !empty($program->image) ? asset('storage/' . $program->image) : asset('assets/images/placeholder.png');
                      @endphp
                      <img src="{{ $progImage }}" alt="{{ $program->title }}" onerror="this.src='{{ asset('assets/images/placeholder.png') }}'">
                    </div>

                    <div class="card-body">
                      <h3 class="program-title">{{ $program->title }}</h3>
                      <p class="program-short">
                        {{ Str::limit(strip_tags($program->short_description ?? $program->long_description ?? ''), 140) }}
                      </p>
                      <div class="program-actions" style="text-align:left;">
                        <a href="{{ route('programs.show', $program->slug ?? $program->id) }}" class="btn-secondary">
                          {{ __('messages.Read More') }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          @else
            <div class="swiper-slide">
              <div class="program-card-wrapper">
                <p style="text-align:center; color:#666;">لا توجد برامج للعرض حالياً.</p>
              </div>
            </div>
          @endif
        </div>

        <div class="swiper-pagination" aria-hidden="true"></div>
      </div>

      <button class="three-cards-btn swiper-button-next" aria-label="التالي" title="التالي">›</button>
    </div>
  </div>
</section>

{{-- ---------- القسم الجديد: Sections (carousel تلقائي/واحد في كل مرة) ---------- --}}
@if(isset($sections) && $sections->count())
  <div style="margin-top:60px;">
    <div class="container">
      <h2 style="text-align:center; margin-bottom:22px;">{{ __('messages.Our partners') }}</h2>

      <!-- Sections carousel wrapper -->
      <div class="sections-carousel" id="sectionsCarousel" aria-live="polite" role="region" aria-label="Sections carousel">
        @php $first = true; @endphp
        @foreach($sections as $section)
          <article class="accredit-section card-centered section-slide {{ $first ? 'active' : '' }}" data-index="{{ $loop->index }}" aria-hidden="{{ $first ? 'false' : 'true' }}" style="margin-bottom:0; padding:26px; background:#fff; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.04);">
            @php $first = false; @endphp

            {{-- TITLE centered --}}
            <header style="text-align:center; margin-bottom:10px;">
              <h3 style="margin:0; color:var(--secondary); font-size:1.2rem; font-weight:700;">{{ $section->title }}</h3>
            </header>

            {{-- SHORT DESCRIPTION centered --}}
            @if($section->short_description)
              <div style="text-align:center; margin-bottom:18px; color:#555; max-width:900px; margin-left:auto; margin-right:auto;">
                <p style="margin:0;">{{ $section->short_description }}</p>
              </div>
            @endif

            {{-- ICONS GRID centered --}}
            <div style="display:flex; justify-content:center;">
              <div class="section-icons-grid" style="width:100%; max-width:980px;">

                @php
                  $icons = $section->icons ?? [];
                  if (is_string($icons)) {
                      $decoded = json_decode($icons, true);
                      if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                          $icons = $decoded;
                      } else {
                          $icons = array_filter(array_map('trim', explode(',', $icons)));
                      }
                  }
                @endphp

                @if(!empty($icons) && is_array($icons))
                  @foreach($icons as $icon)
                    <div class="section-icon-box">
                      @if(\Illuminate\Support\Str::startsWith($icon, ['http://','https://','/']))
                        <img src="{{ $icon }}" alt="" loading="lazy">
                      @else
                        <img src="{{ Voyager::image($icon) }}" alt="" loading="lazy">
                      @endif
                    </div>
                  @endforeach
                @else
                  <div style="grid-column:1/-1; text-align:center; color:#888; padding:18px 0;">
                    لا توجد أيقونات لهذه المجموعة بعد.
                  </div>
                @endif

              </div>
            </div>

          </article>
        @endforeach
      </div>
    </div>
  </div>
@endif

</main>
@endsection

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
@endpush

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

  <script>
  (function(){
    'use strict';

    // --- (لا تغيير على Swiper init الموجود لديك) ---
    function initThreeCardsSwipers() {
      const containers = Array.from(document.querySelectorAll('.three-cards-swiper'));
      if (!containers.length) return;

      containers.forEach((container) => {
        const swiperEl = container.querySelector('.three-swiper');
        if (!swiperEl) return;

        const prevBtn = container.querySelector('.swiper-button-prev');
        const nextBtn = container.querySelector('.swiper-button-next');
        const paginationEl = container.querySelector('.swiper-pagination');

        if (container.__swiperInstance && typeof container.__swiperInstance.destroy === 'function') {
          container.__swiperInstance.destroy(true, true);
          container.__swiperInstance = null;
        }

        const swiper = new Swiper(swiperEl, {
          direction: 'horizontal',
          slidesPerView: 3,
          slidesPerGroup: 3,
          spaceBetween: 12,
          breakpoints: {
            0:   { slidesPerView: 1, slidesPerGroup: 1, spaceBetween: 8 },
            700: { slidesPerView: 2, slidesPerGroup: 2, spaceBetween: 12 },
            1000:{ slidesPerView: 3, slidesPerGroup: 3, spaceBetween: 12 }
          },
          navigation: { prevEl: prevBtn, nextEl: nextBtn },
          pagination: { el: paginationEl, clickable: true, type: 'bullets' },
          a11y: { enabled: true },
          watchOverflow: true,
          loop: false,
          observer: true,
          observeParents: true,
          rtl: document.documentElement.getAttribute('dir') === 'rtl' || document.body.getAttribute('dir') === 'rtl'
        });

        container.__swiperInstance = swiper;
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initThreeCardsSwipers);
    } else {
      initThreeCardsSwipers();
    }
  })();
  </script>

  {{-- Sections auto-rotate (مُحسّن) --}}
  <script>
  (function(){
    'use strict';

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // wait for images in a set of elements to load
    function waitForImages(elements) {
      const imgs = [];
      elements.forEach(el => {
        el.querySelectorAll('img').forEach(img => imgs.push(img));
      });
      const promises = imgs.map(img => {
        if (img.complete && img.naturalHeight !== 0) return Promise.resolve();
        return new Promise(resolve => {
          const onEnd = () => { img.removeEventListener('load', onEnd); img.removeEventListener('error', onEnd); resolve(); };
          img.addEventListener('load', onEnd);
          img.addEventListener('error', onEnd);
          // timeout fallback in case image never fires
          setTimeout(resolve, 3000);
        });
      });
      return Promise.all(promises);
    }

    function measureSlideHeight(slide) {
      // clone slide temporarily and append to body hidden to measure true height (styles apply)
      const clone = slide.cloneNode(true);
      clone.style.position = 'absolute';
      clone.style.left = '-9999px';
      clone.style.top = '-9999px';
      clone.style.visibility = 'hidden';
      clone.style.pointerEvents = 'none';
      clone.style.opacity = '1';
      document.body.appendChild(clone);
      const h = clone.offsetHeight;
      document.body.removeChild(clone);
      return h;
    }

    function initSectionsCarousel() {
      const container = document.getElementById('sectionsCarousel');
      if (!container) return;

      const slides = Array.from(container.querySelectorAll('.section-slide'));
      if (!slides.length) return;

      // ensure only one active class exists
      let current = slides.findIndex(s => s.classList.contains('active'));
      if (current < 0) {
        current = 0;
        slides.forEach((s, i) => s.classList.toggle('active', i === 0));
      }

      // wait images to load to measure properly
      waitForImages(slides).then(() => {
        // measure max height across slides
        let maxH = 0;
        slides.forEach(s => {
          const h = measureSlideHeight(s);
          if (h > maxH) maxH = h;
        });
        if (maxH) {
          container.style.minHeight = maxH + 'px';
        } else {
          // fallback: ensure at least current slide height
          container.style.minHeight = slides[current].offsetHeight + 'px';
        }
      }).catch(() => {
        // on errors fallback to measuring currently visible
        container.style.minHeight = slides[current].offsetHeight + 'px';
      });

      // ensure accessibility attributes initial
      slides.forEach((s, i) => {
        s.setAttribute('aria-hidden', i === current ? 'false' : 'true');
        s.style.zIndex = i === current ? 2 : 1;
      });

      const delay = 2000;
      let interval = null;

      function showSlide(index) {
        if (index === current) return;
        slides.forEach((s, i) => {
          if (i === index) {
            s.classList.add('active');
            s.setAttribute('aria-hidden', 'false');
            s.style.zIndex = 2;
            s.style.pointerEvents = 'auto';
          } else {
            s.classList.remove('active');
            s.setAttribute('aria-hidden', 'true');
            s.style.zIndex = 1;
            s.style.pointerEvents = 'none';
          }
        });
        current = index;
      }

      function nextSlide() {
        const next = (current + 1) % slides.length;
        showSlide(next);
      }

      function start() {
        if (reduceMotion) return;
        stop();
        interval = setInterval(nextSlide, delay);
      }
      function stop() {
        if (interval) { clearInterval(interval); interval = null; }
      }

      // pause/resume on user interaction
      container.addEventListener('mouseenter', stop);
      container.addEventListener('mouseleave', start);
      container.addEventListener('focusin', stop);
      container.addEventListener('focusout', start);

      container.addEventListener('touchstart', stop, {passive:true});
      container.addEventListener('touchend', start, {passive:true});

      // start autoplay
      start();

      // recompute heights on window resize (debounced)
      let rTO;
      window.addEventListener('resize', () => {
        clearTimeout(rTO);
        rTO = setTimeout(() => {
          // recompute max height (images likely loaded already)
          let maxH = 0;
          slides.forEach(s => {
            const h = measureSlideHeight(s);
            if (h > maxH) maxH = h;
          });
          if (maxH) container.style.minHeight = maxH + 'px';
        }, 120);
      });
    }

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', initSectionsCarousel);
    } else {
      initSectionsCarousel();
    }
  })();
  </script>

@endpush
