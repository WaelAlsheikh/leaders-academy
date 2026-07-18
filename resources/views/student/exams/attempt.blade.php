@extends('layouts.app')
@section('hide-navbar', '1')
@section('content')
<div class="student-layout">
    <main class="student-content" style="max-width:960px;margin:0 auto;">
        <section class="exam-portal-page">
            <div class="exam-portal-panel">
                <div class="exam-attempt-header">
                    <div>
                        <h3 style="margin:0 0 6px;color:#083b59;">{{ $exam->title }}</h3>
                        <p class="exam-portal-subtitle">{{ $exam->registrableSubject?->name }} — المدة {{ $exam->duration_minutes }} دقيقة</p>
                    </div>
                    <div id="exam-timer" class="exam-attempt-timer">--:--</div>
                </div>

                <form id="exam-attempt-form" method="POST" action="{{ route('student.exams.submit', $attempt) }}">
                    @csrf
                    <div id="question-nav" class="exam-question-nav"></div>
                    <div id="questions-container">
                        @foreach($exam->quizQuestions as $index => $qq)
                            @php $answer = $attempt->answers->firstWhere('exam_quiz_question_id', $qq->id); @endphp
                            <div class="exam-question-card" data-question-index="{{ $index }}" style="display:{{ $index === 0 ? 'block' : 'none' }};">
                                <div class="exam-question-meta">سؤال {{ $index + 1 }} من {{ $exam->quizQuestions->count() }} — {{ $qq->points }} درجة</div>
                                <div class="exam-question-text">{{ $qq->question_text_snapshot }}</div>
                                @include('exams.partials.question_image', ['imageUrl' => $qq->imageUrl()])
                                <input type="hidden" name="answers[{{ $index }}][exam_quiz_question_id]" value="{{ $qq->id }}">
                                @if($qq->type_snapshot === 'essay')
                                    <textarea name="answers[{{ $index }}][answer_text]" class="form-control essay-answer" rows="6" data-qq="{{ $qq->id }}" placeholder="اكتب إجابتك هنا...">{{ $answer?->answer_text }}</textarea>
                                @elseif($qq->type_snapshot === 'single_choice')
                                    @forelse($qq->choices as $choice)
                                        <label class="exam-choice-option">
                                            <input type="radio" name="answers[{{ $index }}][selected_choice_id]" value="{{ $choice->id }}" @checked($answer?->selected_choice_id == $choice->id)>
                                            <span>{{ $choice->choice_text }}</span>
                                        </label>
                                    @empty
                                        <div class="alert alert-warning">لا توجد خيارات لهذا السؤال. يرجى إبلاغ الدكتور أو المشرف.</div>
                                    @endforelse
                                @else
                                    @forelse($qq->choices as $choice)
                                        <label class="exam-choice-option">
                                            <input type="checkbox" name="answers[{{ $index }}][selected_choice_ids][]" value="{{ $choice->id }}" @checked(in_array($choice->id, $answer?->selected_choice_ids ?? []))>
                                            <span>{{ $choice->choice_text }}</span>
                                        </label>
                                    @empty
                                        <div class="alert alert-warning">لا توجد خيارات لهذا السؤال. يرجى إبلاغ الدكتور أو المشرف.</div>
                                    @endforelse
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="exam-attempt-footer">
                        <button type="button" class="btn btn-secondary" id="prev-question">السابق</button>
                        <button type="button" class="btn btn-secondary" id="next-question">التالي</button>
                        <button type="submit" class="btn btn-primary" id="finish-exam" onclick="return confirm('هل تريد إنهاء الامتحان وتسليم الإجابات؟');">إنهاء الامتحان</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
@endsection
@push('scripts')
<script>
window.examAttemptConfig = {
    attemptId: {{ $attempt->id }},
    expiresAt: '{{ $attempt->expires_at->toIso8601String() }}',
    autosaveUrl: '{{ route('student.exams.autosave', $attempt) }}',
    autosaveInterval: {{ (int) $autosaveInterval }} * 1000,
    csrf: '{{ csrf_token() }}',
    totalQuestions: {{ $exam->quizQuestions->count() }},
};
</script>
<script src="{{ asset('assets/js/exam-attempt.js') }}?v={{ filemtime(public_path('assets/js/exam-attempt.js')) }}"></script>
@endpush
