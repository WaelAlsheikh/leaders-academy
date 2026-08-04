@extends('layouts.app')
@section('hide-navbar', '1')
@section('content')
@php
    $examStatusClasses = [
        'draft' => 'exam-badge--muted',
        'scheduled' => 'exam-badge--info',
        'running' => 'exam-badge--success',
        'finished' => 'exam-badge--warning',
        'archived' => 'exam-badge--muted',
    ];
@endphp
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>{{ $exam->title }}</h3>
                    <p class="exam-portal-subtitle">{{ $exam->registrableSubject?->name }} — شعبة {{ $exam->classSection?->name }}</p>
                </div>
                <a href="{{ route('student.exams.index') }}" class="btn btn-secondary">العودة للقائمة</a>
            </div>

            <div class="exam-portal-panel">
                <div class="exam-portal-meta-grid">
                    <div class="exam-portal-meta-card">
                        <span>يبدأ</span>
                        <strong>{{ $exam->starts_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>ينتهي</span>
                        <strong>{{ $exam->ends_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>المدة</span>
                        <strong>{{ $exam->duration_minutes }} دقيقة</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>حالة الامتحان</span>
                        <strong>
                            <span class="exam-badge {{ $examStatusClasses[$exam->status] ?? 'exam-badge--muted' }}">
                                {{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}
                            </span>
                        </strong>
                    </div>
                </div>

                @if($grade)
                    @php
                        $passed = $grade->isPassed();
                        $resultMod = $grade->resultCssModifier();
                    @endphp
                    <div class="exam-score-card exam-score-card--{{ $resultMod }}">
                        <div>
                            <div class="exam-result-banner exam-result-banner--{{ $resultMod }}">
                                {{ $grade->resultLabel() }}
                            </div>
                            <div class="exam-portal-subtitle" style="margin-top:10px;">
                                {{ $grade->isPublished() ? 'نتيجتك المنشورة' : 'نتيجتك بعد التسليم' }}
                            </div>
                            <div class="exam-score-value">
                                {{ number_format((float) $grade->raw_score, 2) }}
                                <small>/ {{ number_format((float) $grade->max_score, 2) }}</small>
                            </div>
                            <div class="exam-portal-subtitle" style="margin-top:8px;">
                                النسبة: <strong>{{ number_format((float) $grade->percentage, 1) }}%</strong>
                                — حد النجاح: {{ $grade->passThreshold() }}%
                            </div>
                        </div>
                        <div class="exam-score-card-side">
                            <span class="exam-badge exam-badge--{{ $passed ? 'success' : 'danger' }}">{{ $grade->resultLabel() }}</span>
                            @if($grade->isPublished())
                                <span class="exam-badge exam-badge--success">منشورة</span>
                            @endif
                        </div>
                    </div>
                    @if($grade->attempt_id || $attempt)
                        <div class="exam-portal-actions">
                            <a href="{{ route('student.exams.result', $grade->attempt_id ?: $attempt) }}" class="btn btn-secondary">عرض شاشة النتيجة</a>
                        </div>
                    @endif
                @elseif($attempt)
                    <div class="exam-portal-actions">
                        <a href="{{ route('student.exams.attempt', $attempt) }}" class="btn btn-primary">متابعة الامتحان</a>
                        @if($attempt->isSubmitted())
                            <a href="{{ route('student.exams.result', $attempt) }}" class="btn btn-secondary">عرض النتيجة</a>
                        @endif
                    </div>
                @elseif($canStart)
                    <div class="exam-portal-actions">
                        <form method="POST" action="{{ route('student.exams.start', $exam) }}">@csrf<button class="btn btn-primary">بدء الامتحان</button></form>
                    </div>
                @else
                    <div class="alert alert-warning">الامتحان غير متاح للبدء حالياً. تحقق من موعد البداية والنهاية.</div>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection
