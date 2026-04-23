@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $subjectEntity = $entity ?? $studyTerm->studyYear->registrableEntity;
    $isCollegeEntity = $subjectEntity->entity_type === 'college';
    $backRoute = $isCollegeEntity && $college
        ? route($routeBase . '.study_years.terms', $studyTerm->studyYear)
        : route($routeBase . '.study_years.terms', $studyTerm->studyYear);
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-book"></i> إدارة المواد
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $subjectEntity->display_title }} — {{ $studyTerm->studyYear->name }} / {{ $studyTerm->name }}
            </p>
        </div>
        <a href="{{ $backRoute }}" class="employee-action-btn employee-action-btn--neutral">العودة إلى الفصول</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST"
          action="{{ $isCollegeEntity ? route($routeBase . '.subjects.store', $college) : route($routeBase . '.registrables.subjects.store', $subjectEntity) }}"
          class="panel panel-bordered employee-management-panel employee-management-form-panel">
        @csrf
        <input type="hidden" name="study_term_id" value="{{ $studyTerm->id }}">
        <div class="panel-body">
            <h4 class="employee-management-form-title">إضافة مادة جديدة</h4>
            <div class="row employee-form-grid">
                <div class="col-md-3">
                    <label>اسم المادة</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>كود المادة</label>
                    <input name="code" class="form-control" @if($isCollegeEntity) required @endif>
                </div>
                <div class="col-md-2">
                    <label>عدد الساعات</label>
                    <input name="credit_hours" type="number" min="1" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label style="display:flex;align-items:center;gap:6px;margin-top:30px;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        نشط
                    </label>
                </div>
            </div>
            <button class="btn employee-action-btn employee-action-btn--success employee-action-btn--submit">إضافة مادة</button>
        </div>
    </form>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الكود</th>
                        <th>الساعات</th>
                        <th>نشط</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($subjects as $subject)
                        @php
                            $updateRoute = $isCollegeEntity
                                ? route($routeBase . '.subjects.update', $subject)
                                : route($routeBase . '.registrable_subjects.update', $subject);
                            $destroyRoute = $isCollegeEntity
                                ? route($routeBase . '.subjects.destroy', $subject)
                                : route($routeBase . '.registrable_subjects.destroy', $subject);
                        @endphp
                        <tr>
                            <td>{{ $subject->name }}</td>
                            <td>{{ $subject->code ?: '—' }}</td>
                            <td>{{ $subject->credit_hours }}</td>
                            <td>{{ $subject->is_active ? 'نعم' : 'لا' }}</td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <button type="button" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm" data-toggle="collapse" data-target="#edit-study-term-subject-{{ $isCollegeEntity ? 'legacy' : 'reg' }}-{{ $subject->id }}">
                                        تعديل
                                    </button>
                                    <form method="POST" action="{{ $destroyRoute }}" class="employee-inline-form" onsubmit="return confirm('هل تريد حذف هذه المادة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="employee-action-btn employee-action-btn--danger employee-action-btn--sm">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="edit-study-term-subject-{{ $isCollegeEntity ? 'legacy' : 'reg' }}-{{ $subject->id }}" class="collapse">
                            <td colspan="5" class="employee-table-editor-cell">
                                <form method="POST" action="{{ $updateRoute }}">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="study_term_id" value="{{ $studyTerm->id }}">
                                    <div class="row employee-form-grid">
                                        <div class="col-md-3">
                                            <label>اسم المادة</label>
                                            <input name="name" class="form-control" value="{{ $subject->name }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>كود المادة</label>
                                            <input name="code" class="form-control" value="{{ $subject->code }}" @if($isCollegeEntity) required @endif>
                                        </div>
                                        <div class="col-md-2">
                                            <label>عدد الساعات</label>
                                            <input name="credit_hours" type="number" min="1" class="form-control" value="{{ $subject->credit_hours }}" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label>نشط</label>
                                            <div>
                                                <input type="checkbox" name="is_active" value="1" @checked($subject->is_active)>
                                            </div>
                                        </div>
                                        <div class="col-md-12 employee-form-actions">
                                            <button class="btn employee-action-btn employee-action-btn--success employee-action-btn--sm">حفظ</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">لا توجد مواد حالياً.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
