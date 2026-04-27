@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $entityTypeLabels = [
        'college' => 'كلية',
        'program_branch' => 'برنامج جامعي',
        'training_program_branch' => 'برنامج تدريبي',
    ];
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-calendar"></i> إدارة الدورة العامة
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $season->name }}
                @if($season->code)
                    <strong>({{ $season->code }})</strong>
                @endif
            </p>
        </div>
        <a href="{{ route($routeBase . '.enrollment_cycles.index') }}" class="employee-action-btn employee-action-btn--neutral">
            العودة إلى الدورات
        </a>
    </div>

    <div class="employee-form-actions" style="margin-bottom:16px;">
        <form method="POST" action="{{ route($routeBase . '.registration_seasons.archive', $season) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد أرشفة هذه الدورة العامة؟ ستنتقل إلى صفحة المؤرشفات ويمكنك استعادتها أو حذفها لاحقًا.');">
            @csrf
            <button type="submit" class="employee-action-btn employee-action-btn--warning">
                أرشفة الدورة
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-management-form-panel">
        <h4 class="employee-cycle-section-title">بيانات الدورة العامة</h4>
        <form method="POST" action="{{ route($routeBase . '.registration_seasons.update', $season) }}">
            @csrf
            @method('PUT')
            <div class="row employee-form-grid">
                <div class="col-md-3">
                    <label>اسم الدورة</label>
                    <input type="text" name="name" class="form-control" value="{{ $season->name }}" required>
                </div>
                <div class="col-md-2">
                    <label>رمز الدورة</label>
                    <input type="text" name="code" class="form-control" value="{{ $season->code }}">
                </div>
                <div class="col-md-3">
                    <label>بداية التسجيل</label>
                    <input type="datetime-local" name="registration_starts_at" class="form-control" value="{{ $season->registration_starts_at?->format('Y-m-d\\TH:i') }}">
                </div>
                <div class="col-md-3">
                    <label>نهاية التسجيل</label>
                    <input type="datetime-local" name="registration_ends_at" class="form-control" value="{{ $season->registration_ends_at?->format('Y-m-d\\TH:i') }}">
                </div>
                <div class="col-md-2">
                    <label>الحالة العامة</label>
                    <select name="status" class="form-control">
                        <option value="open" @selected($season->status === 'open')>مفتوحة للتسجيل</option>
                        <option value="closed" @selected($season->status === 'closed')>مغلقة التسجيل</option>
                    </select>
                </div>
            </div>
            <div class="employee-form-actions">
                <button type="submit" class="employee-action-btn employee-action-btn--success employee-action-btn--submit">حفظ الدورة</button>
            </div>
        </form>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title" style="margin-top:0;">إدارة الكيانات المفتوحة داخل الدورة</h4>
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>النوع</th>
                            <th>الكيان</th>
                            <th>الحالة داخل الدورة</th>
                            <th>المواد المفتوحة</th>
                            <th>طلبات التسجيل</th>
                            <th>الفصل التشغيلي</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($registrableEntities as $entity)
                            @php
                                $cycle = $cyclesByEntity[$entity->id] ?? null;
                                $isEnabled = $cycle?->is_enabled ?? false;
                            @endphp
                            <tr>
                                <td>{{ $entityTypeLabels[$entity->entity_type] ?? $entity->entity_type }}</td>
                                <td>{{ $entity->display_title }}</td>
                                <td>
                                    <span class="employee-cycle-status employee-cycle-status--{{ $isEnabled ? 'open' : 'closed' }}">
                                        {{ $isEnabled ? 'مفتوح' : 'مغلق' }}
                                    </span>
                                </td>
                                <td>{{ $cycle?->registrable_subjects_count ?? 0 }}</td>
                                <td>{{ $cycle?->registrations_count ?? 0 }}</td>
                                <td>{{ $cycle?->semester?->name ?? '—' }}</td>
                                <td>
                                    <div class="employee-cycle-actions">
                                        <form method="POST" action="{{ route($routeBase . '.registration_seasons.entities.toggle', [$season, $entity]) }}" class="employee-inline-form">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_enabled" value="{{ $isEnabled ? 0 : 1 }}">
                                            <button type="submit" class="employee-action-btn {{ $isEnabled ? 'employee-action-btn--warning' : 'employee-action-btn--success' }} employee-action-btn--sm">
                                                {{ $isEnabled ? 'إلغاء الفتح' : 'فتح ضمن الدورة' }}
                                            </button>
                                        </form>

                                        @if($cycle)
                                            <a href="{{ route($routeBase . '.enrollment_cycles.show', $cycle) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                                إدارة المواد والقبولات
                                            </a>

                                            @if($cycle->semester)
                                                <a href="{{ route($routeBase . '.semesters.sections.index', $cycle->semester) }}" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">
                                                    إدارة الشعب والجلسات
                                                </a>
                                            @endif
                                        @endif

                                        <a href="{{ route($routeBase . '.registrables.registrations.index', $entity) }}?season_id={{ $season->id }}" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">
                                            طلبات التسجيل
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
