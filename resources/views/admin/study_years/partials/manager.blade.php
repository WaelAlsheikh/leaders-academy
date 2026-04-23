@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $entityLabel = match($entity->entity_type) {
        'college' => 'الكلية',
        'program_branch' => 'فرع البرنامج الجامعي',
        'training_program_branch' => 'فرع البرنامج التدريبي',
        default => 'الكيان',
    };
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-university"></i> إدارة السنوات
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $entityLabel }}: <strong>{{ $entity->display_title }}</strong>
            </p>
        </div>
        @if($entity->entity_type === 'college' && $college)
            <a href="{{ route($routeBase . '.colleges.index') }}" class="employee-action-btn employee-action-btn--neutral">
                العودة إلى الكليات
            </a>
        @elseif($entity->entity_type === 'program_branch')
            <a href="{{ route($routeBase . '.program_branches.index') }}" class="employee-action-btn employee-action-btn--neutral">
                العودة إلى فروع البرامج الجامعية
            </a>
        @elseif($entity->entity_type === 'training_program_branch')
            <a href="{{ route($routeBase . '.training_program_branches.index') }}" class="employee-action-btn employee-action-btn--neutral">
                العودة إلى فروع البرامج التدريبية
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-management-form-panel">
        <h4 class="employee-cycle-section-title">إضافة سنة جديدة</h4>
        <form method="POST" action="{{ route($routeBase . '.study_years.store', $entity) }}">
            @csrf
            <div class="row employee-form-grid">
                <div class="col-md-6">
                    <label>اسم السنة</label>
                    <input type="text" name="name" class="form-control" required placeholder="مثال: السنة الثانية">
                </div>
                <div class="col-md-3">
                    <label>الترتيب</label>
                    <input type="number" min="1" name="sort_order" class="form-control" placeholder="يُحدد تلقائياً">
                </div>
            </div>
            <div class="employee-form-actions">
                <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--submit">إضافة السنة</button>
            </div>
        </form>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                    <tr>
                        <th>السنة</th>
                        <th>الترتيب</th>
                        <th>الفصول</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($entity->studyYears as $studyYear)
                        <tr>
                            <td>{{ $studyYear->name }}</td>
                            <td>{{ $studyYear->sort_order }}</td>
                            <td>{{ $studyYear->study_terms_count }}</td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <a href="{{ route($routeBase . '.study_years.terms', $studyYear) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                        إدارة الفصول
                                    </a>
                                    <button type="button" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm" data-toggle="collapse" data-target="#edit-study-year-{{ $studyYear->id }}">
                                        تعديل
                                    </button>
                                    <form method="POST" action="{{ route($routeBase . '.study_years.destroy', $studyYear) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد حذف هذه السنة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="employee-action-btn employee-action-btn--danger employee-action-btn--sm">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr id="edit-study-year-{{ $studyYear->id }}" class="collapse">
                            <td colspan="4" class="employee-table-editor-cell">
                                <form method="POST" action="{{ route($routeBase . '.study_years.update', $studyYear) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="row employee-form-grid">
                                        <div class="col-md-6">
                                            <label>اسم السنة</label>
                                            <input type="text" name="name" class="form-control" value="{{ $studyYear->name }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>الترتيب</label>
                                            <input type="number" min="1" name="sort_order" class="form-control" value="{{ $studyYear->sort_order }}" required>
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
                            <td colspan="4" class="text-center">لا توجد سنوات مضافة حالياً.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
