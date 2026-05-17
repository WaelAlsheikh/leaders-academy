@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')

@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')

    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>{{ $liveSession->section?->registrableSubject?->name ?? 'المادة' }}</h3>
                    <p class="doctor-portal-meta">
                        {{ $liveSession->section?->semester?->enrollmentCycle?->registrableEntity?->display_title ?? '—' }}
                        — {{ $liveSession->section?->semester?->name ?? '—' }}
                        — الشعبة {{ $liveSession->section?->name ?? '—' }}
                    </p>
                </div>
                <span class="doctor-live-status doctor-live-status-{{ $sessionState['status_code'] }}">
                    {{ $sessionState['status_label'] }}
                </span>
            </div>

            <div class="doctor-section-info-grid">
                <div><strong>المدرّس:</strong> {{ $liveSession->section?->doctor?->full_name ?? 'لم يُحدَّد المدرس بعد' }}</div>
                <div><strong>الموعد:</strong> {{ $liveSession->scheduled_starts_at?->format('Y-m-d H:i') }} - {{ $liveSession->scheduled_ends_at?->format('H:i') }}</div>
                <div><strong>طريقة الحضور:</strong> {{ $liveSession->section?->mode === 'online' ? 'أونلاين' : 'حضوري' }}</div>
                <div><strong>الحالة:</strong> {{ $sessionState['status_label'] }}</div>
            </div>

            <div id="live-session-status-banner" class="doctor-inline-note" @if(!$sessionState['ended'] && !$sessionState['entry_closed']) style="display:none;" @endif>
                {{ $sessionState['status_label'] }}
            </div>

            @if($errors->has('meet'))
                <div class="alert alert-danger">{{ $errors->first('meet') }}</div>
            @endif
        </section>

        <section class="live-session-layout live-session-layout-student">
            <div class="live-session-main">
                <section class="doctor-portal-panel live-session-panel">
                    <div class="doctor-portal-panel-head">
                        <h3>نافذة المحاضرة</h3>
                    </div>

                    <div class="live-session-embed-card">
                        @if(!$sessionState['ended'])
                            <div id="jitsi-meeting-container" class="live-session-embed"></div>
                        @else
                            <div class="doctor-portal-empty">
                                {{ $sessionState['status_label'] }}
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="live-session-side">
                <section class="doctor-portal-panel live-session-panel">
                    <div class="doctor-portal-panel-head">
                        <h3>التعليقات الفورية</h3>
                        <div class="doctor-portal-meta" id="comments-meta">
                            {{ $sessionState['comments_enabled'] ? 'مفعّلة' : 'متوقفة' }}
                        </div>
                    </div>

                    <div id="comments-list" class="live-session-comments-list"></div>

                    <div id="student-comments-disabled-note"
                         class="doctor-inline-note"
                         @if($sessionState['comments_enabled'] && !$sessionState['comments_blocked']) style="display:none;" @endif>
                        @if($sessionState['comments_blocked'])
                            تم إيقاف إمكانية التعليق لحسابك في هذه الجلسة.
                        @elseif(!$sessionState['comments_enabled'])
                            التعليقات متوقفة حالياً.
                        @endif
                    </div>

                    <form id="student-comment-form"
                          class="live-session-comment-form"
                          @if(!$sessionState['comments_enabled'] || $sessionState['comments_blocked']) style="display:none;" @endif>
                        <textarea name="body" id="student-comment-body" rows="3" class="form-control" placeholder="اكتب تعليقًا يظهر للجميع..."></textarea>
                        <button type="submit" class="btn btn-primary">إرسال</button>
                    </form>
                </section>
            </div>
        </section>
    </main>
</div>
@endsection

@push('scripts')
    <script>
        window.liveSessionPageConfig = {{ \Illuminate\Support\Js::from($pageConfig) }};
    </script>
    <script src="{{ asset('assets/js/live-session.js') }}?v={{ filemtime(public_path('assets/js/live-session.js')) }}"></script>
@endpush
