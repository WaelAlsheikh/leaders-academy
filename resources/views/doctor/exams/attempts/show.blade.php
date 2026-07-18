@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
@php
    $gradeStatusClasses = [
        'draft' => 'exam-badge--muted',
        'auto_corrected' => 'exam-badge--info',
        'pending_review' => 'exam-badge--warning',
        'reviewed' => 'exam-badge--info',
        'approved' => 'exam-badge--primary',
        'published' => 'exam-badge--success',
    ];
    $grade = $attempt->grade;
    $exam = $attempt->exam;
    $student = $attempt->student;
@endphp
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>إجابات الطالب</h3>
                    <p class="exam-portal-subtitle">
                        {{ $student?->first_name }} {{ $student?->last_name }} —
                        {{ $exam?->title }} ({{ $exam?->registrableSubject?->name }})
                    </p>
                </div>
                <div class="exam-portal-actions" style="margin-top:0;">
                    <a href="{{ route('doctor.exam_grades.index') }}" class="btn btn-secondary">الدرجات</a>
                    @if($exam)
                        <a href="{{ route('doctor.exams.grading.review', $exam) }}" class="btn btn-secondary">تصحيح الامتحان</a>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="exam-portal-panel">
                <div class="exam-portal-meta-grid">
                    <div class="exam-portal-meta-card">
                        <span>وقت التسليم</span>
                        <strong>{{ $attempt->submitted_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>الدرجة</span>
                        <strong>
                            @if($grade)
                                {{ $grade->raw_score }} / {{ $grade->max_score }}
                            @else
                                —
                            @endif
                        </strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>حالة النتيجة</span>
                        <strong>
                            @if($grade)
                                <span class="exam-badge {{ $gradeStatusClasses[$grade->status] ?? 'exam-badge--muted' }}">
                                    {{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}
                                </span>
                            @else
                                —
                            @endif
                        </strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>عدد الأسئلة</span>
                        <strong>{{ $attempt->answers->count() }}</strong>
                    </div>
                </div>

                @if($grade && $grade->status !== 'published' && ($canGradeEssays ?? false))
                    <div class="exam-portal-actions">
                        <form method="POST" action="{{ route('doctor.exam_grades.publish', $grade) }}">@csrf<button class="btn btn-primary btn-sm">نشر النتيجة للطالب</button></form>
                    </div>
                @endif
            </div>

            <div class="exam-portal-panel">
                <h4 style="margin:0 0 16px;color:#083b59;">تفاصيل الإجابات</h4>
                @include('exams.partials.attempt_answers', [
                    'attempt' => $attempt,
                    'canGradeEssays' => $canGradeEssays ?? false,
                ])
            </div>
        </section>
    </main>
</div>
@endsection
