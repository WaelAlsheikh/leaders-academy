@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>بنك الأسئلة</h3>
                    <p class="exam-portal-subtitle">إدارة أسئلة المواد المرتبطة بشعبك.</p>
                </div>
                <div class="exam-portal-actions" style="margin-top:0;">
                    <a href="{{ route('doctor.exams.categories.index') }}" class="btn btn-secondary">التصنيفات</a>
                    <a href="{{ route('doctor.exams.questions.create') }}" class="btn btn-primary">سؤال جديد</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="exam-portal-panel">
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>السؤال</th>
                                <th>صورة</th>
                                <th>المادة</th>
                                <th>النوع</th>
                                <th>التصنيف</th>
                                <th>الدرجة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($questions as $question)
                            <tr>
                                <td>{{ Str::limit($question->question_text, 80) }}</td>
                                <td>
                                    @if($question->imageUrl())
                                        <img src="{{ $question->imageUrl() }}" alt="" class="exam-question-image-thumb" data-exam-image="{{ $question->imageUrl() }}">
                                    @else
                                        <span class="exam-portal-subtitle">—</span>
                                    @endif
                                </td>
                                <td>{{ $question->registrableSubject?->name ?? '—' }}</td>
                                <td>{{ config('exams.question_types')[$question->type] ?? $question->type }}</td>
                                <td>{{ $question->category?->name ?? '—' }}</td>
                                <td>{{ $question->default_points }}</td>
                                <td>
                                    @if(in_array($question->type, ['single_choice', 'multiple_choice']) && $question->choices->count() < 2)
                                        <span class="exam-badge exam-badge--warning" style="display:inline-block;margin-bottom:6px;">ناقص خيارات</span><br>
                                    @endif
                                    <a href="{{ route('doctor.exams.questions.edit', $question) }}" class="btn btn-secondary btn-sm">تعديل</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="exam-portal-empty">لا توجد أسئلة.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px;">{{ $questions->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection
@push('scripts')
<script src="{{ asset('assets/js/exam-question-image.js') }}?v={{ @filemtime(public_path('assets/js/exam-question-image.js')) }}"></script>
@endpush
