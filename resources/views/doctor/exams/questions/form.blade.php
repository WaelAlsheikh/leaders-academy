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
                    <h3>{{ isset($question) ? 'تعديل سؤال' : 'سؤال جديد' }}</h3>
                    <p class="exam-portal-subtitle">أضف سؤالاً لبنك المادة مع الخيارات أو الإجابة التحريرية.</p>
                </div>
                <a href="{{ route('doctor.exams.questions.index') }}" class="btn btn-secondary">العودة للقائمة</a>
            </div>

            <div class="exam-portal-panel">
            <form method="POST" action="{{ isset($question) ? route('doctor.exams.questions.update', $question) : route('doctor.exams.questions.store') }}" id="question-form" enctype="multipart/form-data">
                @csrf
                @if(isset($question)) @method('PUT') @endif
                <div class="form-group">
                    <label>المادة</label>
                    <select name="registrable_subject_id" class="form-control" required>
                        <option value="">— اختر المادة —</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('registrable_subject_id', $question->registrable_subject_id ?? '') == $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                    <small class="doctor-portal-meta">يُربط السؤال بمادة من شعبك — يُستخدم عند توليد الامتحانات العشوائية للمادة نفسها.</small>
                </div>
                <div class="form-group">
                    <label>التصنيف</label>
                    <select name="category_id" class="form-control">
                        <option value="">— بدون —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $question->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>نوع السؤال</label>
                    <select name="type" id="question-type" class="form-control" required>
                        @foreach(config('exams.question_types') as $key => $label)
                            <option value="{{ $key }}" @selected(old('type', $question->type ?? 'single_choice') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>نص السؤال</label><textarea name="question_text" class="form-control" rows="4" required>{{ old('question_text', $question->question_text ?? '') }}</textarea></div>

                <div class="exam-question-image-upload">
                    <label>صورة السؤال (اختياري)</label>
                    <p class="exam-portal-subtitle" style="margin-bottom:10px;">يمكن إرفاق رسم أو مخطط مع أي نوع سؤال. الصورة اختيارية، ويُفضّل وضوحها بنسبة مناسبة للعرض.</p>
                    @if(isset($question) && $question->imageUrl())
                        <div class="exam-question-image-current">
                            @include('exams.partials.question_image', ['imageUrl' => $question->imageUrl()])
                            <label style="display:flex;align-items:center;gap:8px;font-weight:500;">
                                <input type="checkbox" name="remove_image" value="1"> حذف الصورة الحالية
                            </label>
                        </div>
                    @endif
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                    <small class="doctor-portal-meta">الصيغ المدعومة: JPG, PNG, WEBP, GIF — الحد الأقصى 5MB</small>
                </div>

                <div class="form-group"><label>الدرجة الافتراضية</label><input type="number" step="0.01" name="default_points" class="form-control" value="{{ old('default_points', $question->default_points ?? 1) }}"></div>
                <div class="form-group">
                    <label>الصعوبة</label>
                    <select name="difficulty" class="form-control">
                        <option value="">—</option>
                        @foreach(['easy'=>'سهل','medium'=>'متوسط','hard'=>'صعب'] as $k=>$v)
                            <option value="{{ $k }}" @selected(old('difficulty', $question->difficulty ?? '') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group"><label>وسوم (مفصولة بفاصلة)</label><input type="text" name="tags" class="form-control" value="{{ old('tags', isset($question) ? implode(', ', $question->tags ?? []) : '') }}"></div>
                <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $question->is_active ?? true))> نشط</label>

                <div id="choices-block" style="margin-top:16px;">
                    <script type="application/json" id="question-choice-defaults">@json($choiceDefaults)</script>
                    <h4>الخيارات</h4>
                    <div id="choices-list"></div>
                    <button type="button" class="btn btn-secondary" id="add-choice">إضافة خيار</button>
                </div>

                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                <div class="exam-portal-actions">
                    <button type="submit" class="btn btn-primary">حفظ</button>
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
    const typeEl = document.getElementById('question-type');
    const list = document.getElementById('choices-list');
    const block = document.getElementById('choices-block');
    const addChoiceBtn = document.getElementById('add-choice');
    const defaultsEl = document.getElementById('question-choice-defaults');
    const existing = JSON.parse(defaultsEl ? defaultsEl.textContent : '[]');

    function renderChoices(){
        const isEssay = typeEl.value === 'essay';
        block.style.display = isEssay ? 'none' : 'block';
        if (addChoiceBtn) {
            addChoiceBtn.style.display = isEssay ? 'none' : '';
            addChoiceBtn.disabled = isEssay;
        }

        // Always clear: hidden required fields otherwise block HTML5 submit silently.
        list.innerHTML = '';
        if (isEssay) {
            return;
        }

        existing.forEach((c, i) => {
            const row = document.createElement('div');
            row.className = 'doctor-live-actions';

            const input = document.createElement('input');
            input.name = 'choices[' + i + '][choice_text]';
            input.className = 'form-control';
            input.value = c.choice_text || '';
            input.placeholder = 'نص الخيار';
            input.required = true;

            const label = document.createElement('label');
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.name = 'choices[' + i + '][is_correct]';
            checkbox.value = '1';
            if (c.is_correct) checkbox.checked = true;
            label.appendChild(checkbox);
            label.appendChild(document.createTextNode(' صحيح'));

            row.appendChild(input);
            row.appendChild(label);
            list.appendChild(row);
        });
    }

    document.getElementById('add-choice').addEventListener('click', () => {
        existing.push({choice_text: '', is_correct: false});
        renderChoices();
    });
    typeEl.addEventListener('change', renderChoices);
    renderChoices();
})();
</script>
@endpush
