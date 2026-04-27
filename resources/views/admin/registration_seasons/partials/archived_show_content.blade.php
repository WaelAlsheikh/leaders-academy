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
                <i class="voyager-archive"></i> عرض دورة عامة مؤرشفة
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $season->name }}
                @if($season->code)
                    <strong>({{ $season->code }})</strong>
                @endif
            </p>
        </div>
        <a href="{{ route($routeBase . '.archived_enrollment_cycles.index') }}" class="employee-action-btn employee-action-btn--neutral">
            العودة إلى المؤرشفات
        </a>
    </div>

    <div class="employee-form-actions" style="margin-bottom:16px;">
        <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.restore', $season) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد استعادة هذه الدورة العامة؟ ستعود إلى صفحة إدارة الدورات بحالة مغلقة.');">
            @csrf
            <button type="submit" class="employee-action-btn employee-action-btn--success">
                استعادة الدورة
            </button>
        </form>
        <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.destroy', $season) }}" class="employee-inline-form" onsubmit="return confirm('سيتم حذف الدورة العامة نهائيًا مع جميع توابعها. هل تريد المتابعة؟');">
            @csrf
            @method('DELETE')
            <button type="submit" class="employee-action-btn employee-action-btn--danger">
                حذف نهائي
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="row employee-form-grid">
                <div class="col-md-3">
                    <label>اسم الدورة</label>
                    <input type="text" class="form-control" value="{{ $season->name }}" disabled>
                </div>
                <div class="col-md-2">
                    <label>رمز الدورة</label>
                    <input type="text" class="form-control" value="{{ $season->code ?: '—' }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>بداية التسجيل</label>
                    <input type="text" class="form-control" value="{{ optional($season->registration_starts_at)->format('Y-m-d H:i') ?? '—' }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>نهاية التسجيل</label>
                    <input type="text" class="form-control" value="{{ optional($season->registration_ends_at)->format('Y-m-d H:i') ?? '—' }}" disabled>
                </div>
                <div class="col-md-2">
                    <label>الحالة بعد الاستعادة</label>
                    <input type="text" class="form-control" value="مغلقة" disabled>
                </div>
                <div class="col-md-3">
                    <label>أرشفت بتاريخ</label>
                    <input type="text" class="form-control" value="{{ optional($season->archived_at)->format('Y-m-d H:i') ?? '—' }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>أرشفت بواسطة</label>
                    <input type="text" class="form-control" value="{{ $season->archivedBy?->name ?? '—' }}" disabled>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title" style="margin-top:0;">الكيانات التي كانت مفتوحة داخل الدورة</h4>
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                    <tr>
                        <th>النوع</th>
                        <th>الكيان</th>
                        <th>حالة الفتح</th>
                        <th>المواد المفتوحة</th>
                        <th>طلبات التسجيل</th>
                        <th>الفصل التشغيلي</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($season->enrollmentCycles as $cycle)
                        <tr>
                            <td>{{ $entityTypeLabels[$cycle->registrableEntity?->entity_type] ?? ($cycle->registrableEntity?->entity_type ?? '—') }}</td>
                            <td>{{ $cycle->registrableEntity?->display_title ?? '—' }}</td>
                            <td>
                                <span class="employee-cycle-status employee-cycle-status--{{ $cycle->is_enabled ? 'open' : 'closed' }}">
                                    {{ $cycle->is_enabled ? 'كان مفتوحًا' : 'كان مغلقًا' }}
                                </span>
                            </td>
                            <td>{{ $cycle->registrable_subjects_count ?? 0 }}</td>
                            <td>{{ $cycle->registrations_count ?? 0 }}</td>
                            <td>{{ $cycle->semester?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد كيانات مرتبطة بهذه الدورة.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
