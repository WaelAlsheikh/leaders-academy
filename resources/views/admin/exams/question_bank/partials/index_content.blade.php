@php
    $examPortalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $examPortalMode }}" data-portal-context="{{ $examPortalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-list"></i> بنك الأسئلة
            </h1>
            <p class="employee-cycle-subtitle">تصفح أسئلة الأساتذة حسب الكلية والمادة مع معرفة الدكتور الذي أضاف كل سؤال.</p>
        </div>
        <div class="employee-inline-form">
            <a href="{{ route($routeBase . '.exams.index') }}" class="employee-action-btn employee-action-btn--neutral">الامتحانات</a>
            <a href="{{ route($routeBase . '.exam_grades.index') }}" class="employee-action-btn employee-action-btn--neutral">الدرجات</a>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body employee-management-form-panel">
            <form method="GET" action="{{ route($routeBase . '.exam_question_bank.index') }}" id="exam-question-bank-filter" class="row" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;">
                <div class="form-group" style="min-width:220px;flex:1;">
                    <label for="college_id">الكلية</label>
                    <select name="college_id" id="college_id" class="form-control" required>
                        <option value="">— اختر الكلية —</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}" @selected((string) $collegeId === (string) $college->id)>{{ $college->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="min-width:220px;flex:1;">
                    <label for="registrable_subject_id">المادة</label>
                    <select name="registrable_subject_id" id="registrable_subject_id" class="form-control" required @disabled(! $collegeId)>
                        <option value="">— اختر المادة —</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) $subjectId === (string) $subject->id)>
                                {{ $subject->name }}@if($subject->code) ({{ $subject->code }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="employee-form-actions" style="margin:0;">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary">عرض الأسئلة</button>
                </div>
            </form>
        </div>
    </div>

    @if(! $collegeId || ! $subjectId)
        <div class="panel panel-bordered employee-management-panel">
            <div class="panel-body">
                <div class="exam-portal-empty">اختر الكلية ثم المادة لعرض أسئلة بنك الأسئلة المرتبطة بهما.</div>
            </div>
        </div>
    @else
        <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
            <div class="panel-body">
                <h4 class="employee-cycle-section-title">
                    نتائج البحث
                    @if($questionsPaginator)
                        <small class="text-muted">({{ $questionsPaginator->total() }} سؤال)</small>
                    @endif
                </h4>

                <div class="employee-cycle-table-wrap">
                    <table class="table table-striped employee-cycle-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>السؤال</th>
                                <th>النوع</th>
                                <th>التصنيف</th>
                                <th>الدرجة</th>
                                <th>الدكتور</th>
                                <th>الحالة</th>
                                <th>الخيارات</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($questions as $question)
                            <tr>
                                <td>{{ $question->id }}</td>
                                <td style="max-width:360px;">
                                    <strong>{{ Str::limit($question->question_text, 140) }}</strong>
                                    @if($question->difficulty)
                                        <div class="text-muted" style="margin-top:4px;">الصعوبة: {{ ['easy'=>'سهل','medium'=>'متوسط','hard'=>'صعب'][$question->difficulty] ?? $question->difficulty }}</div>
                                    @endif
                                    @if($question->imageUrl())
                                        <div style="margin-top:10px;">
                                            @include('exams.partials.question_image', ['imageUrl' => $question->imageUrl()])
                                        </div>
                                    @endif
                                </td>
                                <td>{{ config('exams.question_types')[$question->type] ?? $question->type }}</td>
                                <td>{{ $question->category?->name ?? '—' }}</td>
                                <td>{{ $question->default_points }}</td>
                                <td>
                                    <strong>{{ $question->doctor?->full_name ?? '—' }}</strong>
                                </td>
                                <td>
                                    @if($question->is_active)
                                        <span class="label label-success">نشط</span>
                                    @else
                                        <span class="label label-default">غير نشط</span>
                                    @endif
                                    @if($question->isAutoGradable() && $question->choices->count() < 2)
                                        <div style="margin-top:6px;"><span class="label label-warning">ناقص خيارات</span></div>
                                    @endif
                                </td>
                                <td style="min-width:200px;">
                                    @if($question->type === 'essay')
                                        <span class="text-muted">سؤال تحريري</span>
                                    @elseif($question->choices->isEmpty())
                                        <span class="text-muted">لا خيارات</span>
                                    @else
                                        <ul style="margin:0;padding-right:18px;">
                                            @foreach($question->choices as $choice)
                                                <li>
                                                    @if($choice->is_correct)<strong>✓ </strong>@endif
                                                    {{ $choice->choice_text }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد أسئلة في بنك هذه المادة بعد.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                @if($questionsPaginator)
                    <div style="margin-top:16px;">
                        {{ $questionsPaginator->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<script>
(function () {
    const collegeSelect = document.getElementById('college_id');
    const subjectSelect = document.getElementById('registrable_subject_id');
    const subjectsUrl = @json(route($routeBase . '.exam_question_bank.subjects'));

    if (!collegeSelect || !subjectSelect) return;

    collegeSelect.addEventListener('change', async function () {
        const collegeId = this.value;
        subjectSelect.innerHTML = '<option value="">— اختر المادة —</option>';
        subjectSelect.disabled = !collegeId;

        if (!collegeId) return;

        try {
            const response = await fetch(subjectsUrl + '?college_id=' + encodeURIComponent(collegeId), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();
            (data.subjects || []).forEach(function (subject) {
                const option = document.createElement('option');
                option.value = subject.id;
                option.textContent = subject.code ? (subject.name + ' (' + subject.code + ')') : subject.name;
                subjectSelect.appendChild(option);
            });
            subjectSelect.disabled = false;
        } catch (e) {
            console.warn('Failed to load subjects', e);
        }
    });
})();
</script>
