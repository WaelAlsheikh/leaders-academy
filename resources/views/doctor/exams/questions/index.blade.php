@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <h3>بنك الأسئلة</h3>
                <a href="{{ route('doctor.exams.questions.create') }}" class="btn btn-primary">سؤال جديد</a>
            </div>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <table class="doctor-students-table">
                <thead><tr><th>السؤال</th><th>المادة</th><th>النوع</th><th>التصنيف</th><th>الدرجة</th><th></th></tr></thead>
                <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td>{{ Str::limit($question->question_text, 80) }}</td>
                        <td>{{ $question->registrableSubject?->name ?? '—' }}</td>
                        <td>{{ config('exams.question_types')[$question->type] ?? $question->type }}</td>
                        <td>{{ $question->category?->name ?? '—' }}</td>
                        <td>{{ $question->default_points }}</td>
                        <td>
                            @if(in_array($question->type, ['single_choice', 'multiple_choice']) && $question->choices->count() < 2)
                                <span class="doctor-live-status doctor-live-status-ended" style="display:inline-block;margin-bottom:6px;">ناقص خيارات</span><br>
                            @endif
                            <a href="{{ route('doctor.exams.questions.edit', $question) }}" class="btn btn-secondary btn-sm">تعديل</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;">لا توجد أسئلة.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $questions->links() }}
        </section>
    </main>
</div>
@endsection
