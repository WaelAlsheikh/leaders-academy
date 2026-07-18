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
                    <h3>إنشاء امتحان يدوي</h3>
                    <p class="exam-portal-subtitle">اختر الشعبة أولاً — ستظهر أسئلة بنك المادة المرتبطة بها فقط.</p>
                </div>
                <a href="{{ route('doctor.exams.index') }}" class="btn btn-secondary">العودة</a>
            </div>

            <div class="exam-portal-panel">
                <form method="POST" action="{{ route('doctor.exams.store') }}" id="manual-exam-form">
                    @csrf
                    <div class="exam-portal-meta-grid">
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
                    </div>

                    <h4 style="margin:20px 0 12px;color:#083b59;">اختر الأسئلة وحدد درجاتها</h4>
                    <div id="manual-questions-list">
                        @foreach($questions as $i => $q)
                            <label class="exam-question-preview manual-question-row" data-subject-id="{{ $q->registrable_subject_id }}">
                                <div style="display:flex;align-items:flex-start;gap:10px;flex-wrap:wrap;">
                                    <input type="checkbox" name="questions[{{ $i }}][enabled]" value="1">
                                    <input type="hidden" name="questions[{{ $i }}][question_id]" value="{{ $q->id }}">
                                    <div style="flex:1;">
                                        <strong>[{{ config('exams.question_types')[$q->type] }}]</strong> {{ Str::limit($q->question_text, 80) }}
                                        <div class="exam-portal-subtitle">{{ $q->registrableSubject?->name }}</div>
                                    </div>
                                    <input type="number" step="0.01" name="questions[{{ $i }}][points]" value="{{ $q->default_points }}" class="form-control" style="width:90px;"> درجة
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="exam-portal-actions">
                        <button type="submit" class="btn btn-primary">إنشاء وجدولة</button>
                    </div>
                </form>
            </div>
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
