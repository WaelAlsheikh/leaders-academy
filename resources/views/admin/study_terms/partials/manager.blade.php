@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $termEntity = $entity ?? $studyYear->registrableEntity;
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-layers"></i> إدارة الفصول
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $termEntity->display_title }} — <strong>{{ $studyYear->name }}</strong>
            </p>
        </div>
        <a href="{{ $termEntity->entity_type === 'college' && $college ? route($routeBase . '.colleges.years', $college) : route($routeBase . '.registrables.years', $termEntity) }}" class="employee-action-btn employee-action-btn--neutral">
            العودة إلى السنوات
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-management-form-panel">
        <h4 class="employee-cycle-section-title">إضافة فصل جديد</h4>
        <form method="POST" action="{{ route($routeBase . '.study_terms.store', $studyYear) }}">
            @csrf
            <div class="row employee-form-grid">
                <div class="col-md-4">
                    <label>اسم الفصل</label>
                    <input type="text" name="name" class="form-control" required placeholder="مثال: الفصل الثاني">
                </div>
                <div class="col-md-3">
                    <label>الرمز</label>
                    <input type="text" name="code" class="form-control" placeholder="اختياري">
                </div>
                <div class="col-md-3">
                    <label>الترتيب</label>
                    <input type="number" min="1" name="sort_order" class="form-control" placeholder="يُحدد تلقائياً">
                </div>
            </div>
            <div class="employee-form-actions">
                <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--submit">إضافة الفصل</button>
            </div>
        </form>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                    <tr>
                        <th>الفصل</th>
                        <th>الرمز</th>
                        <th>الترتيب</th>
                        <th>عدد المواد</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($studyYear->studyTerms as $studyTerm)
                        @php
                            $subjectsCount = $termEntity->entity_type === 'college'
                                ? $studyTerm->legacy_subjects_count
                                : $studyTerm->registrable_subjects_count;
                        @endphp
                        <tr>
                            <td>{{ $studyTerm->name }}</td>
                            <td>{{ $studyTerm->code ?: '—' }}</td>
                            <td>{{ $studyTerm->sort_order }}</td>
                            <td>{{ $subjectsCount }}</td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <a href="{{ route($routeBase . '.study_terms.subjects', $studyTerm) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                        إدارة المواد
                                    </a>
                                    <button type="button" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm" data-toggle="collapse" data-target="#edit-study-term-{{ $studyTerm->id }}">
                                        تعديل
                                    </button>
                                    <form method="POST" action="{{ route($routeBase . '.study_terms.destroy', $studyTerm) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد حذف هذا الفصل؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="employee-action-btn employee-action-btn--danger employee-action-btn--sm">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="edit-study-term-{{ $studyTerm->id }}" class="collapse">
                            <td colspan="5" class="employee-table-editor-cell">
                                <form method="POST" action="{{ route($routeBase . '.study_terms.update', $studyTerm) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="row employee-form-grid">
                                        <div class="col-md-4">
                                            <label>اسم الفصل</label>
                                            <input type="text" name="name" class="form-control" value="{{ $studyTerm->name }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>الرمز</label>
                                            <input type="text" name="code" class="form-control" value="{{ $studyTerm->code }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label>الترتيب</label>
                                            <input type="number" min="1" name="sort_order" class="form-control" value="{{ $studyTerm->sort_order }}" required>
                                        </div>
                                        <div class="col-md-12 employee-form-actions">
                                            <button type="submit" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">حفظ التعديلات</button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">لا توجد فصول مضافة حالياً.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
