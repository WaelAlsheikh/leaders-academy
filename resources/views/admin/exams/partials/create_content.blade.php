<div class="page-content container-fluid">
    <div class="employee-management-panel doctor-portal-panel">
        <h3>إنشاء امتحان (توليد عشوائي من بنك الأسئلة)</h3>
        <p class="doctor-portal-meta">يُختار الامتحان للشعبة والمادة المسجّل عليها الطلاب، وتُسحب الأسئلة من بنك دكتور الشعبة للمادة نفسها — مشابه لفكرة Question Bank في Moodle.</p>

        <form method="POST" action="{{ route($routeBase . '.exams.store') }}" class="doctor-portal-form" id="exam-create-form">
            @csrf
            <div class="form-group">
                <label>عنوان الامتحان</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
            </div>
            <div class="form-group">
                <label>الوصف</label>
                <textarea name="description" class="form-control">{{ old('description') }}</textarea>
            </div>

            <div class="form-group">
                <label>الشعبة (المادة + الدكتور + الطلاب)</label>
                <select name="class_section_id" id="class-section-id" class="form-control" required>
                    <option value="">— اختر الشعبة —</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" @selected(old('class_section_id') == $section->id)>
                            {{ $section->registrableSubject?->name }} — شعبة {{ $section->name }} — {{ $section->doctor?->full_name }} ({{ $section->students_count }} طالب)
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="section-context-panel" class="live-session-comment" style="display:none;margin:16px 0;">
                <h4>سياق الامتحان</h4>
                <div class="doctor-portal-meta" id="context-summary"></div>
                <div id="bank-stats" style="margin-top:8px;"></div>
            </div>

            <div class="form-group" id="category-filter-block" style="display:none;">
                <label>تصنيفات بنك الأسئلة (اختياري — اتركها فارغة لاستخدام كل التصنيفات)</label>
                <select name="category_ids[]" id="category-ids" class="form-control" multiple size="5"></select>
            </div>

            <div class="form-group" id="type-filter-block">
                <label>أنواع الأسئلة المطلوبة</label>
                @foreach(config('exams.question_types') as $key => $label)
                    <label style="display:block;margin:4px 0;">
                        <input type="checkbox" name="question_types[]" value="{{ $key }}" class="question-type-filter" checked>
                        {{ $label }}
                    </label>
                @endforeach
            </div>

            <div class="form-group">
                <label>عدد الأسئلة</label>
                <input type="number" name="question_count" id="question-count" class="form-control" value="{{ old('question_count', 25) }}" min="1" required>
                <small class="doctor-portal-meta" id="question-count-hint">الدرجة الكاملة 100 وتُوزَّع تلقائياً على الأسئلة.</small>
            </div>

            <div class="form-group">
                <label>تاريخ الامتحان</label>
                <input type="date" name="exam_date" id="exam-date" class="form-control" value="{{ old('exam_date') }}" required>
            </div>
            <div class="form-group">
                <label>وقت البداية</label>
                <input type="datetime-local" name="starts_at" id="starts-at" class="form-control" value="{{ old('starts_at') }}" required>
            </div>
            <div class="form-group">
                <label>وقت النهاية (نافذة إتاحة الامتحان)</label>
                <input type="datetime-local" name="ends_at" id="ends-at" class="form-control" value="{{ old('ends_at') }}" required>
                <small class="doctor-portal-meta">يجب أن يكون بعد وقت البداية. يُقترح تلقائياً = البداية + مدة الامتحان.</small>
            </div>
            <div class="form-group">
                <label>مدة محاولة الطالب (دقيقة)</label>
                <input type="number" name="duration_minutes" id="duration-minutes" class="form-control" value="{{ old('duration_minutes', 60) }}" min="5" max="480" required>
            </div>
            <label><input type="checkbox" name="allow_late_entry" value="1" @checked(old('allow_late_entry'))> السماح بالدخول المتأخر</label>

            @if($errors->any())<div class="alert alert-danger" style="margin-top:12px;">{{ $errors->first() }}</div>@endif
            <button type="submit" class="btn btn-primary" style="margin-top:16px;">إنشاء وتوليد الأسئلة</button>
        </form>
    </div>
</div>

<script>
window.examAdminCreateConfig = {
    sectionContextUrl: '{{ url($routeBase . '/exams/section-context') }}',
    questionTypes: @json(config('exams.question_types')),
    oldSectionId: @json(old('class_section_id')),
    oldCategoryIds: @json(old('category_ids', [])),
};
</script>
<script src="{{ asset('assets/js/exam-admin-create.js') }}?v={{ filemtime(public_path('assets/js/exam-admin-create.js')) }}"></script>
