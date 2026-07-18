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
@endphp
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>درجات الامتحانات</h3>
                    <p class="exam-portal-subtitle">متابعة درجات الطلاب ونشر النتائج بعد التصحيح.</p>
                </div>
            </div>

            <div class="exam-portal-panel">
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>الامتحان</th>
                                <th>المادة</th>
                                <th>الدرجة</th>
                                <th>الحالة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($grades as $grade)
                            <tr>
                                <td><strong>{{ $grade->student?->first_name }} {{ $grade->student?->last_name }}</strong></td>
                                <td>{{ $grade->exam?->title }}</td>
                                <td>{{ $grade->exam?->registrableSubject?->name }}</td>
                                <td><strong>{{ $grade->raw_score }}</strong> / {{ $grade->max_score }}</td>
                                <td>
                                    <span class="exam-badge {{ $gradeStatusClasses[$grade->status] ?? 'exam-badge--muted' }}">
                                        {{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}
                                    </span>
                                </td>
                                <td>
                                    @if($grade->exam)
                                        <a href="{{ route('doctor.exams.grading.review', $grade->exam) }}" class="btn btn-secondary btn-sm">تصحيح</a>
                                    @endif
                                    @if($grade->attempt_id)
                                        <a href="{{ route('doctor.exam_attempts.show', $grade->attempt_id) }}" class="btn btn-secondary btn-sm">عرض الإجابات</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><div class="exam-portal-empty">لا توجد درجات بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px;">{{ $grades->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection
