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

        <section class="doctor-portal-panel" id="doctor-subjects">
            <div class="doctor-portal-panel-head">
                <h3>المواد والشعب المسندة إليك</h3>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

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
