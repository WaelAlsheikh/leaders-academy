@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')

@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')

    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>{{ $liveSession->section?->registrableSubject?->name ?? 'المادة' }}</h3>
                    <p class="doctor-portal-meta">
                        {{ $liveSession->section?->semester?->enrollmentCycle?->registrableEntity?->display_title ?? '—' }}
                        — {{ $liveSession->section?->semester?->enrollmentCycle?->name ?? '—' }}
                        — {{ $liveSession->section?->semester?->name ?? '—' }}
                        — الشعبة {{ $liveSession->section?->name ?? '—' }}
                    </p>
                </div>
                <span class="doctor-live-status doctor-live-status-{{ $status['code'] }}">
                    {{ $status['label'] }}
                </span>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="doctor-section-info-grid">
                <div><strong>الأستاذ:</strong> {{ $doctor->full_name }}</div>
                <div><strong>الموعد:</strong> {{ $liveSession->scheduled_starts_at?->format('Y-m-d H:i') }} - {{ $liveSession->scheduled_ends_at?->format('H:i') }}</div>
                <div><strong>طريقة الحضور:</strong> {{ $liveSession->section?->mode === 'online' ? 'أونلاين' : 'حضوري' }}</div>
                <div><strong>مزود الجلسة:</strong> {{ $liveSession->meeting_provider }}</div>
            </div>

            <div class="doctor-live-actions">
                @if(!$liveSession->ended_at)
                    @if($liveSession->entry_closed_at)
                        <form method="POST"
                              action="{{ route('doctor.live_sessions.reopen_entry', $liveSession) }}"
                              onsubmit="return confirm('هل تريد إعادة فتح الدخول للطلاب؟');">
                            @csrf
                            <button type="submit" class="btn btn-secondary">إعادة فتح الدخول</button>
                        </form>
                    @else
                        <form method="POST"
                              action="{{ route('doctor.live_sessions.close_entry', $liveSession) }}"
                              onsubmit="return confirm('هل تريد إيقاف إمكانية الدخول لهذه الجلسة؟');">
                            @csrf
                            <button type="submit" class="btn btn-secondary">إيقاف الدخول</button>
                        </form>
                    @endif

                    <form method="POST"
                          action="{{ route('doctor.live_sessions.end', $liveSession) }}"
                          data-end-session-form="1"
                          onsubmit="return confirm('هل تريد إنهاء الجلسة؟');">
                        @csrf
                        <button type="submit" class="btn btn-secondary doctor-end-button">إنهاء الجلسة</button>
                    </form>
                @endif

                <a href="{{ route('doctor.dashboard') }}#doctor-live-sessions" class="btn btn-secondary">عودة للمحاضرات</a>
            </div>

            <div class="doctor-inline-note" id="doctor-moderator-note">
                ملاحظة: على <code>meet.jit.si</code> قد تحتاج تسجيل الدخول الخارجي لتصبح <strong>moderator</strong> وتعمل أدوات التحكم المتقدمة.
            </div>

            @if(!$liveSession->ended_at)
                <div class="doctor-live-actions doctor-live-actions-inline">
                    <button type="button" class="btn btn-secondary" id="doctor-open-direct-jitsi-btn">
                        فتح الجلسة مباشرة في نافذة مستقلة
                    </button>
                </div>
            @endif
        </section>

        <section class="live-session-layout">
            <div class="live-session-main">
                <section class="doctor-portal-panel live-session-panel">
                    <div class="doctor-portal-panel-head">
                        <h3>قاعة المحاضرة</h3>
                        <div class="doctor-live-inline-controls">
                            <button type="button" class="btn btn-secondary" id="doctor-toggle-comments-btn">
                                {{ $liveSession->comments_enabled ? 'إيقاف التعليقات' : 'السماح بالتعليقات' }}
                            </button>
                            <button type="button" class="btn btn-secondary" id="doctor-toggle-audio-moderation-btn">
                                {{ $liveSession->audio_moderation_enabled ? 'فتح صوت الطلاب' : 'تقييد صوت الطلاب' }}
                            </button>
                            <button type="button" class="btn btn-secondary" id="doctor-toggle-video-moderation-btn">
                                {{ $liveSession->video_moderation_enabled ? 'فتح فيديو الطلاب' : 'تقييد فيديو الطلاب' }}
                            </button>
                            <button type="button" class="btn btn-secondary" id="doctor-recording-btn">
                                بدء تسجيل محلي
                            </button>
                        </div>
                    </div>

                    <div class="live-session-tip-stack">
                        <div class="live-session-tip">
                            لتحصل على عرض أوضح داخل القاعة، يمكنك التبديل إلى العرض الشبكي من أدوات Jitsi إذا رغبت بذلك.
                        </div>
                        <div class="live-session-tip live-session-tip-warning" id="doctor-screen-share-note" style="display:none;">
                            عند مشاركة الشاشة أو تسجيل الشرح: استخدم سماعة رأس، وشارك نافذة العرض أو سطح المكتب بدل تبويب الجلسة نفسه، وتجنب تفعيل مشاركة صوت النظام إذا لم تكن تحتاجه حتى لا يحدث تداخل بالصوت.
                        </div>
                    </div>

                    <div id="live-session-status-banner" class="doctor-inline-note" style="display:none;"></div>

                    <div class="live-session-embed-card">
                        @if(!$liveSession->ended_at)
                            <div id="jitsi-meeting-container" class="live-session-embed"></div>
                        @else
                            <div class="doctor-portal-empty">
                                انتهت هذه الجلسة، ويمكنك فقط مراجعة التعليقات والحضور.
                            </div>
                        @endif
                    </div>
                </section>
            </div>

            <div class="live-session-side">
                <section class="doctor-portal-panel live-session-panel">
                    <div class="doctor-portal-panel-head">
                        <h3>حضور الطلاب</h3>
                        <div id="attendance-summary" class="doctor-portal-meta">جارِ التحميل...</div>
                    </div>

                    <div class="doctor-live-bulk-actions">
                        <button type="button" class="btn btn-secondary" id="block-selected-comments-btn">حظر تعليقات المحددين</button>
                        <button type="button" class="btn btn-secondary" id="unblock-selected-comments-btn">فك حظر المحددين</button>
                    </div>

                    <div class="doctor-students-table-wrap">
                        <table class="doctor-students-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>الطالب</th>
                                    <th>اسم المستخدم</th>
                                    <th>الحالة</th>
                                    <th>الدخول الأول</th>
                                    <th>آخر ظهور</th>
                                    <th>التعليقات</th>
                                </tr>
                            </thead>
                            <tbody id="attendance-table-body">
                                <tr>
                                    <td colspan="7" style="text-align:center;">جارِ تحميل بيانات الحضور...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="doctor-portal-panel live-session-panel">
                    <div class="doctor-portal-panel-head">
                        <h3>التعليقات الفورية</h3>
                        <div class="doctor-portal-meta" id="comments-meta">
                            {{ $liveSession->comments_enabled ? 'مفعّلة' : 'متوقفة' }}
                        </div>
                    </div>

                    <div id="comments-list" class="live-session-comments-list"></div>

                    <form id="doctor-comment-form" class="live-session-comment-form">
                        <textarea name="body" id="doctor-comment-body" rows="3" class="form-control" placeholder="اكتب تعليقًا يظهر للجميع..."></textarea>
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
    <script src="{{ asset('assets/js/live-session.js') }}"></script>
@endpush
