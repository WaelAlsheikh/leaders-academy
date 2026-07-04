@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <h3>تصحيح: {{ $exam->title }}</h3>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            @foreach($attempts as $attempt)
                <div class="live-session-comment" style="margin-bottom:20px;">
                    <h4>{{ $attempt->student?->first_name }} {{ $attempt->student?->last_name }} — {{ config('exams.grade_statuses')[$attempt->grade?->status ?? 'draft'] ?? '' }}</h4>
                    @foreach($attempt->answers as $answer)
                        @if($answer->quizQuestion?->type_snapshot === 'essay')
                            <div style="margin:12px 0;padding:12px;background:#f7fafc;border-radius:8px;">
                                <strong>سؤال تحريري:</strong> {{ $answer->quizQuestion?->question_text_snapshot }}
                                <div style="margin:8px 0;">{{ $answer->answer_text ?: '— لم يُجب —' }}</div>
                                <form method="POST" action="{{ route('doctor.exam_answers.grade', $answer) }}" class="doctor-live-actions">
                                    @csrf
                                    <input type="number" step="0.01" name="points_awarded" class="form-control" value="{{ $answer->points_awarded ?? 0 }}" max="{{ $answer->quizQuestion?->points }}" required>
                                    <textarea name="feedback" class="form-control" placeholder="ملاحظات">{{ $answer->feedback }}</textarea>
                                    <button class="btn btn-primary btn-sm">حفظ التصحيح</button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                    @if($attempt->grade && $attempt->grade->status !== 'published')
                        <form method="POST" action="{{ route('doctor.exam_grades.publish', $attempt->grade) }}">@csrf<button class="btn btn-secondary btn-sm">نشر النتيجة</button></form>
                    @endif
                </div>
            @endforeach
        </section>
    </main>
</div>
@endsection
