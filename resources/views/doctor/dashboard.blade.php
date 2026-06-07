@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')

@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')

    <main class="student-content doctor-portal">
        <section class="doctor-portal-hero">
            <h1 class="doctor-portal-title">أهلاً {{ $doctor->full_name }}</h1>
            <p class="doctor-portal-subtitle">
                اسم المستخدم: <strong>{{ $doctor->username }}</strong>
            </p>
        </section>

        <section class="doctor-portal-summary">
            <div class="doctor-portal-stat">
                <span>المواد المسندة</span>
                <strong>{{ $subjectCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>الشعب الحالية</span>
                <strong>{{ $sectionCount }}</strong>
            </div>
            <div class="doctor-portal-stat">
                <span>إجمالي الطلاب</span>
                <strong>{{ $studentCount }}</strong>
            </div>
        </section>

        <section class="doctor-portal-panel" id="doctor-live-sessions">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>محاضراتك القريبة</h3>
                    <p class="doctor-portal-meta">هذه القائمة تعرض محاضرات اليوم والأيام القريبة مع حالة كل جلسة فيديو.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @if($upcomingSessions->isEmpty())
                <div class="doctor-portal-empty">
                    لا توجد محاضرات أونلاين خلال الأيام القادمة.
                </div>
            @else
                @foreach($upcomingSessions as $date => $items)
                    <div class="doctor-occurrence-group">
                        <div class="doctor-occurrence-date">
                            {{ \Illuminate\Support\Carbon::parse($date)->translatedFormat('l d-m-Y') }}
                        </div>

                        <div class="doctor-occurrence-grid">
                            @foreach($items as $item)
                                @php
                                    $section = $item['section'];
                                    $liveSession = $item['live_session'];
                                    $status = $item['status'];
                                    $isArchived = $section->semester?->enrollmentCycle?->is_archived;
                                @endphp
                                <article class="doctor-occurrence-card">
                                    <div class="doctor-occurrence-card-head">
                                        <div>
                                            <h4>{{ $section->registrableSubject?->name ?? 'المادة' }}</h4>
                                            <p>
                                                {{ $section->semester?->enrollmentCycle?->registrableEntity?->display_title ?? '—' }}
                                                — {{ $section->semester?->enrollmentCycle?->name ?? '—' }}
                                            </p>
                                        </div>
                                        <span class="doctor-live-status doctor-live-status-{{ $status['code'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </div>

                                    @if(!empty($item['shared_lecture_label']))
                                        @include('doctor.partials.shared-lecture-badge', ['sharedLectureLabel' => $item['shared_lecture_label']])
                                    @endif

                                    <div class="doctor-occurrence-meta">
                                        <div><strong>الفصل:</strong> {{ $section->semester?->name ?? '—' }}</div>
                                        <div><strong>الشعبة:</strong> {{ $section->name }}</div>
                                        <div><strong>الوقت:</strong> {{ $item['scheduled_starts_at']->format('H:i') }} - {{ $item['scheduled_ends_at']->format('H:i') }}</div>
                                        <div><strong>عدد الطلاب:</strong> {{ $section->students_count }}</div>
                                    </div>

                                    @if($isArchived)
                                        <div class="doctor-inline-note">
                                            هذه الدورة مؤرشفة، لذلك لا يمكن بدء جلسات جديدة لها.
                                        </div>
                                    @endif

                                    <div class="doctor-live-actions">
                                        @if(!$liveSession && !$isArchived)
                                            @if($item['can_start_today'])
                                                <form method="POST"
                                                      action="{{ route('doctor.meetings.start', $item['section_meeting']) }}"
                                                      onsubmit="return confirm('هل تريد بدء هذه الجلسة الآن؟');">
                                                    @csrf
                                                    <input type="hidden" name="occurrence_date" value="{{ $item['occurrence_date'] }}">
                                                    <button type="submit" class="btn btn-primary">بدء الجلسة</button>
                                                </form>
                                            @else
                                                <span class="doctor-inline-note">يمكن بدء الجلسة في يوم المحاضرة فقط.</span>
                                            @endif
                                        @elseif($liveSession)
                                            <a href="{{ route('doctor.live_sessions.show', $liveSession) }}" class="btn btn-primary">
                                                {{ $status['code'] === 'ended' ? 'عرض التفاصيل' : 'الدخول إلى الجلسة' }}
                                            </a>

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
                                                      onsubmit="return confirm('هل تريد إنهاء الجلسة؟');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary doctor-end-button">إنهاء الجلسة</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </section>

        <section class="doctor-portal-panel" id="doctor-subjects">
            <div class="doctor-portal-panel-head">
                <h3>المواد والشعب المسندة إليك</h3>
            </div>

            @if($subjectGroups->isEmpty())
                <div class="doctor-portal-empty">
                    لا توجد مواد مسندة إليك حالياً.
                </div>
            @else
                @foreach($subjectGroups as $group)
                    @php
                        $subject = $group['subject'];
                        $sections = $group['sections'];
                    @endphp
                    <article class="doctor-subject-card">
                        <div class="doctor-subject-head">
                            <div>
                                <h4>{{ $subject?->name ?? 'مادة غير معروفة' }}</h4>
                                <p>
                                    {{ $subject?->registrableEntity?->display_title ?? '—' }}
                                    @if($subject?->code)
                                        — {{ $subject->code }}
                                    @endif
                                </p>
                            </div>
                            <div class="doctor-subject-hours">
                                {{ $subject?->credit_hours ?? '—' }} ساعة
                            </div>
                        </div>

                        @if($sections->isEmpty())
                            <div class="doctor-portal-empty doctor-portal-empty-inline">
                                لا توجد شعبة حالياً لهذه المادة.
                            </div>
                        @else
                            <div class="doctor-section-grid">
                                @foreach($sections as $section)
                                    <a href="{{ route('doctor.sections.show', $section) }}" class="doctor-section-card">
                                        <div class="doctor-section-card-head">
                                            <strong>الشعبة {{ $section->name }}</strong>
                                            @if($section->semester?->enrollmentCycle?->is_archived)
                                                <span class="doctor-status-badge archived">الدورة مؤرشفة</span>
                                            @endif
                                        </div>
                                        <p>الدورة: {{ $section->semester?->enrollmentCycle?->name ?? '—' }}</p>
                                        <p>الفصل: {{ $section->semester?->name ?? '—' }}</p>
                                        @if(!empty($sharedLectureLabelsBySectionId[$section->id] ?? null))
                                            <p class="doctor-shared-lecture-badge doctor-shared-lecture-badge--compact">
                                                {{ $sharedLectureLabelsBySectionId[$section->id] }}
                                            </p>
                                        @endif
                                        <p>طريقة الحضور: {{ $section->mode === 'online' ? 'أونلاين' : 'حضوري' }}</p>
                                        <p>عدد الطلاب: {{ $section->students_count }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </article>
                @endforeach
            @endif
        </section>
    </main>
</div>
@endsection
