<div class="container-fluid employee-cycle-page">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-calendar"></i> إدارة دورات التسجيل
            </h1>
            <p class="employee-cycle-subtitle">يمكنك من هنا إنشاء دورات التسجيل ومتابعة الشعب والجلسات المرتبطة بها.</p>
        </div>

        <a href="{{ route($routeBase . '.archived_enrollment_cycles.index') }}" class="employee-action-btn employee-action-btn--neutral employee-cycle-header-link">
            الدورات المؤرشفة
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-create-panel">
        <div class="employee-management-form-panel">
            <h4 class="employee-cycle-section-title">إنشاء دورة تسجيل جديدة</h4>
            <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.store') }}">
            @csrf
                <div class="row employee-form-grid">
                    <div class="col-md-3">
                        <label>كيان التسجيل</label>
                        <select name="registrable_entity_id" class="form-control" required>
                            <option value="">اختر</option>
                            @foreach($registrableEntities as $entity)
                                <option value="{{ $entity->id }}">
                                    [{{ $entity->entity_type }}] {{ $entity->display_title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>اسم الدورة</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>بداية التسجيل</label>
                        <input type="datetime-local" name="registration_starts_at" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label>نهاية التسجيل</label>
                        <input type="datetime-local" name="registration_ends_at" class="form-control">
                    </div>
                </div>

                <div class="employee-form-actions">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--submit">
                        إضافة
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                <thead>
                    <tr>
                        <th>الكلية</th>
                        <th>النوع</th>
                        <th>الدورة</th>
                        <th>الحالة</th>
                        <th>الفصل</th>
                        <th>المدة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cycles as $cycle)
                        <tr>
                            <td>{{ $cycle->registrableEntity?->display_title }}</td>
                            <td>{{ $cycle->registrableEntity?->entity_type }}</td>
                            <td>{{ $cycle->name }}</td>
                            <td>
                                <span class="employee-cycle-status employee-cycle-status--{{ \Illuminate\Support\Str::slug($cycle->status, '-') }}">
                                    {{ $cycle->status }}
                                </span>
                            </td>
                            <td>
                                @if($cycle->semester)
                                    {{ $cycle->semester->name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ optional($cycle->registration_starts_at)->format('Y-m-d H:i') ?? '—' }}
                                -
                                {{ optional($cycle->registration_ends_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <a href="{{ route($routeBase . '.enrollment_cycles.show', $cycle) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                        إدارة
                                    </a>
                                    @if($cycle->semester)
                                        <a href="{{ route($routeBase . '.semesters.sections.index', $cycle->semester) }}" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">
                                            إدارة الشعب
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.archive', $cycle) }}" class="employee-inline-form" onsubmit="return confirm('هل أنت متأكد من أرشفة هذه الدورة؟ ستنتقل إلى صفحة الدورات المؤرشفة مع الاحتفاظ بكل بياناتها.');">
                                        @csrf
                                        <button type="submit" class="employee-action-btn employee-action-btn--warning employee-action-btn--sm">
                                            أرشفة
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    @if($cycles->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center">لا توجد دورات</td>
                        </tr>
                    @endif
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
