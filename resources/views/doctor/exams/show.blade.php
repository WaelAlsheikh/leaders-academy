@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
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
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>{{ $exam->title }}</h3>
                    <p class="exam-portal-subtitle">{{ $exam->registrableSubject?->name }} — شعبة {{ $exam->classSection?->name }}</p>
                </div>
                <a href="{{ route('doctor.exams.grading.review', $exam) }}" class="btn btn-primary">تصحيح المحاولات</a>
            </div>

            <div class="exam-portal-panel">
                <div class="exam-portal-meta-grid">
                    <div class="exam-portal-meta-card">
                        <span>الحالة</span>
                        <strong>
                            <span class="exam-badge {{ $examStatusClasses[$exam->status] ?? 'exam-badge--muted' }}">
                                {{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}
                            </span>
                        </strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>الدرجة الكلية</span>
                        <strong>{{ $exam->total_points }} درجة</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>يبدأ</span>
                        <strong>{{ $exam->starts_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>ينتهي</span>
                        <strong>{{ $exam->ends_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
