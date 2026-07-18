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
                    <h3>امتحاناتي</h3>
                    <p class="exam-portal-subtitle">إدارة الامتحانات اليدوية وتصحيح المحاولات.</p>
                </div>
                @if(\App\Models\ExamSetting::isManualMode())
                    <a href="{{ route('doctor.exams.create') }}" class="btn btn-primary">إنشاء امتحان يدوي</a>
                @endif
            </div>

            <div class="exam-portal-panel">
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>المادة</th>
                                <th>الحالة</th>
                                <th>الموعد</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($exams as $exam)
                            <tr>
                                <td><strong>{{ $exam->title }}</strong></td>
                                <td>{{ $exam->registrableSubject?->name }}</td>
                                <td>
                                    <span class="exam-badge {{ $examStatusClasses[$exam->status] ?? 'exam-badge--muted' }}">
                                        {{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}
                                    </span>
                                </td>
                                <td>{{ $exam->starts_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('doctor.exams.show', $exam) }}" class="btn btn-secondary btn-sm">عرض</a>
                                    <a href="{{ route('doctor.exams.grading.review', $exam) }}" class="btn btn-secondary btn-sm">تصحيح</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="exam-portal-empty">لا توجد امتحانات.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px;">{{ $exams->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection
