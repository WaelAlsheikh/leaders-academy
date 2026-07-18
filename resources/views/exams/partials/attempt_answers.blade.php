@php
    $sortedAnswers = $attempt->answers
        ->sortBy(fn ($answer) => $answer->quizQuestion?->sort_order ?? 0)
        ->values();
    $canGradeEssays = $canGradeEssays ?? false;
@endphp

@foreach($sortedAnswers as $index => $answer)
    @php
        $question = $answer->quizQuestion;
        $type = $question?->type_snapshot;
        $maxPoints = (float) ($question?->points ?? 0);
        $awarded = $answer->points_awarded !== null ? (float) $answer->points_awarded : null;
    @endphp
    <div class="exam-answer-review-card">
        <div class="exam-answer-review-head">
            <div>
                <span class="exam-badge exam-badge--info">سؤال {{ $index + 1 }}</span>
                <span class="exam-badge exam-badge--muted">{{ config('exams.question_types')[$type] ?? $type }}</span>
            </div>
            <div class="exam-answer-review-score">
                @if($awarded !== null)
                    <strong>{{ number_format($awarded, 2) }}</strong> / {{ number_format($maxPoints, 2) }}
                    @if($type !== 'essay')
                        @if($answer->is_correct === true)
                            <span class="exam-badge exam-badge--success">صحيح</span>
                        @elseif($answer->is_correct === false)
                            <span class="exam-badge exam-badge--warning">خطأ</span>
                        @endif
                    @endif
                @else
                    <span class="exam-badge exam-badge--muted">بانتظار التصحيح</span>
                @endif
            </div>
        </div>

        <div class="exam-answer-review-question">{{ $question?->question_text_snapshot }}</div>
        @include('exams.partials.question_image', ['imageUrl' => $question?->imageUrl()])

        @if(in_array($type, ['single_choice', 'multiple_choice'], true))
            <div class="exam-answer-choice-list">
                @forelse($question?->choices ?? [] as $choice)
                    @php
                        $selected = $answer->isChoiceSelected($choice->id);
                        $choiceClass = 'exam-answer-choice';
                        if ($choice->is_correct) {
                            $choiceClass .= ' exam-answer-choice--correct';
                        }
                        if ($selected && ! $choice->is_correct) {
                            $choiceClass .= ' exam-answer-choice--wrong';
                        }
                        if ($selected) {
                            $choiceClass .= ' exam-answer-choice--selected';
                        }
                    @endphp
                    <div class="{{ $choiceClass }}">
                        <span class="exam-answer-choice-marker">
                            @if($selected) ● @else ○ @endif
                        </span>
                        <span>{{ $choice->choice_text }}</span>
                        @if($choice->is_correct)
                            <span class="exam-badge exam-badge--success">إجابة صحيحة</span>
                        @endif
                        @if($selected)
                            <span class="exam-badge exam-badge--primary">اختيار الطالب</span>
                        @endif
                    </div>
                @empty
                    <div class="exam-portal-subtitle">لا توجد خيارات مسجلة لهذا السؤال.</div>
                @endforelse
            </div>
            @unless($answer->hasAnySelection())
                <div class="exam-answer-empty">لم يُجب الطالب على هذا السؤال.</div>
            @endunless
        @elseif($type === 'essay')
            <div class="exam-answer-essay-box">
                {{ filled(trim((string) $answer->answer_text)) ? $answer->answer_text : '— لم يُجب —' }}
            </div>
            @if(filled($answer->feedback))
                <div class="exam-answer-feedback">
                    <strong>ملاحظات التصحيح:</strong> {{ $answer->feedback }}
                </div>
            @endif
            @if($canGradeEssays)
                <form method="POST" action="{{ route('doctor.exam_answers.grade', $answer) }}" class="exam-portal-actions exam-answer-grade-form">
                    @csrf
                    <input type="number" step="0.01" name="points_awarded" class="form-control" style="max-width:120px;" value="{{ $answer->points_awarded ?? 0 }}" max="{{ $maxPoints }}" required>
                    <textarea name="feedback" class="form-control" rows="2" placeholder="ملاحظات للطالب">{{ $answer->feedback }}</textarea>
                    <button class="btn btn-primary btn-sm">حفظ التصحيح</button>
                </form>
            @endif
        @endif
    </div>
@endforeach
