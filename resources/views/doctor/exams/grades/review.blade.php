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
                    <h3>تصحيح: {{ $exam->title }}</h3>
                    <p class="exam-portal-subtitle">{{ $exam->registrableSubject?->name }} — مراجعة الإجابات التحريرية ونشر النتائج.</p>
                </div>
                <a href="{{ route('doctor.exams.index') }}" class="btn btn-secondary">العودة للامتحانات</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="exam-portal-panel">
                @forelse($attempts as $attempt)
                    <div class="exam-review-block">
                        <div class="exam-portal-header" style="margin-bottom:14px;">
                            <div>
                                <strong>{{ $attempt->student?->first_name }} {{ $attempt->student?->last_name }}</strong>
                                @if($attempt->grade)
                                    <span class="exam-badge {{ $gradeStatusClasses[$attempt->grade->status] ?? 'exam-badge--muted' }}" style="margin-inline-start:8px;">
                                        {{ config('exams.grade_statuses')[$attempt->grade->status] ?? $attempt->grade->status }}
                                    </span>
                                @endif
                            </div>
                            <div class="exam-portal-actions" style="margin-top:0;">
                                <a href="{{ route('doctor.exam_attempts.show', $attempt) }}" class="btn btn-secondary btn-sm">عرض جميع الإجابات</a>
                                @if($attempt->grade && $attempt->grade->status !== 'published')
                                    <form method="POST" action="{{ route('doctor.exam_grades.publish', $attempt->grade) }}">@csrf<button class="btn btn-primary btn-sm">نشر النتيجة</button></form>
                                @endif
                            </div>
                        </div>

                        @php $essayAnswers = $attempt->answers->filter(fn ($a) => $a->quizQuestion?->type_snapshot === 'essay'); @endphp
                        @if($essayAnswers->isEmpty())
                            <p class="exam-portal-subtitle">لا توجد إجابات تحريرية تحتاج تصحيحاً. يمكنك عرض جميع الإجابات من الزر أعلاه.</p>
                        @else
                            @foreach($essayAnswers as $answer)
                                <div class="exam-review-essay">
                                    <strong>سؤال تحريري ({{ $answer->quizQuestion?->points }} درجة):</strong>
                                    <div style="margin:8px 0;">{{ $answer->quizQuestion?->question_text_snapshot }}</div>
                                    @include('exams.partials.question_image', ['imageUrl' => $answer->quizQuestion?->imageUrl()])
                                    <div class="exam-portal-subtitle" style="margin-bottom:10px;">إجابة الطالب:</div>
                                    <div style="margin-bottom:12px;">{{ $answer->answer_text ?: '— لم يُجب —' }}</div>
                                    <form method="POST" action="{{ route('doctor.exam_answers.grade', $answer) }}" class="exam-portal-actions" style="margin-top:0;">
                                        @csrf
                                        <input type="number" step="0.01" name="points_awarded" class="form-control" style="max-width:120px;" value="{{ $answer->points_awarded ?? 0 }}" max="{{ $answer->quizQuestion?->points }}" required>
                                        <textarea name="feedback" class="form-control" rows="2" placeholder="ملاحظات للطالب">{{ $answer->feedback }}</textarea>
                                        <button class="btn btn-primary btn-sm">حفظ التصحيح</button>
                                    </form>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @empty
                    <div class="exam-portal-empty">لا توجد محاولات مُسلَّمة بعد.</div>
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection
