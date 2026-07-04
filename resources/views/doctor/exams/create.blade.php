@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <h3>إنشاء امتحان يدوي</h3>
            <p class="doctor-portal-meta">اختر الشعبة أولاً — ستظهر أسئلة بنك المادة المرتبطة بها فقط.</p>
            <form method="POST" action="{{ route('doctor.exams.store') }}" id="manual-exam-form">
                @csrf
                <div class="form-group"><label>العنوان</label><input name="title" class="form-control" required></div>
                <div class="form-group">
                    <label>الشعبة</label>
                    <select name="class_section_id" id="manual-section-id" class="form-control" required>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" data-subject-id="{{ $section->registrable_subject_id }}">
                                {{ $section->registrableSubject?->name }} — {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>تاريخ الامتحان</label><input type="date" name="exam_date" class="form-control" required></div>
                <div class="form-group"><label>البداية</label><input type="datetime-local" name="starts_at" class="form-control" required></div>
                <div class="form-group"><label>النهاية</label><input type="datetime-local" name="ends_at" class="form-control" required></div>
                <div class="form-group"><label>المدة (دقيقة)</label><input type="number" name="duration_minutes" class="form-control" value="60" required></div>
                <h4>اختر الأسئلة وحدد درجاتها</h4>
                <div id="manual-questions-list">
                @foreach($questions as $i => $q)
                    <label class="manual-question-row" style="display:block;margin:8px 0;" data-subject-id="{{ $q->registrable_subject_id }}">
                        <input type="checkbox" name="questions[{{ $i }}][enabled]" value="1">
                        <input type="hidden" name="questions[{{ $i }}][question_id]" value="{{ $q->id }}">
                        [{{ config('exams.question_types')[$q->type] }}] {{ Str::limit($q->question_text, 60) }}
                        <small>({{ $q->registrableSubject?->name }})</small>
                        <input type="number" step="0.01" name="questions[{{ $i }}][points]" value="{{ $q->default_points }}" style="width:80px;margin-inline-start:8px;"> درجة
                    </label>
                @endforeach
                </div>
                <button type="submit" class="btn btn-primary">إنشاء وجدولة</button>
            </form>
        </section>
    </main>
</div>
@endsection
@push('scripts')
<script>
(function(){
    const sectionSelect = document.getElementById('manual-section-id');
    const rows = Array.from(document.querySelectorAll('.manual-question-row'));
    function filterQuestions(){
        const subjectId = sectionSelect.selectedOptions[0]?.dataset.subjectId;
        rows.forEach((row) => {
            row.style.display = row.dataset.subjectId === subjectId ? 'block' : 'none';
        });
    }
    sectionSelect.addEventListener('change', filterQuestions);
    filterQuestions();
})();
</script>
@endpush
