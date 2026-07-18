@extends('layouts.app')
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
@endphp
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>نتيجة الامتحان</h3>
                    <p class="exam-portal-subtitle">{{ $attempt->exam?->title }}</p>
                </div>
                <a href="{{ route('student.exams.index') }}" class="btn btn-secondary">العودة للامتحانات</a>
            </div>

            <div class="exam-portal-panel">
                @if($grade)
                    <div class="exam-score-card">
                        <div>
                            <div class="exam-portal-subtitle">درجتك</div>
                            <div class="exam-score-value">{{ number_format((float) $grade->raw_score, 2) }} <small>/ {{ number_format((float) $grade->max_score, 2) }}</small></div>
                        </div>
                        <span class="exam-badge {{ $gradeStatusClasses[$grade->status] ?? 'exam-badge--muted' }}">
                            {{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}
                        </span>
                    </div>

                    @if(in_array($grade->status, ['auto_corrected', 'pending_review', 'reviewed', 'approved']))
                        <div class="alert alert-info" style="margin-top:16px;">{{ $preliminaryMessage }}</div>
                    @endif
                    @if($grade->isPublished())
                        <div class="alert alert-success" style="margin-top:12px;">تم اعتماد ونشر نتيجتك النهائية.</div>
                    @endif
                @else
                    <div class="exam-portal-empty">جارٍ معالجة نتيجتك...</div>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection
