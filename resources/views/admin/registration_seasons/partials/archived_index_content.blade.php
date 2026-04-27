@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-archive"></i> الدورات العامة المؤرشفة
            </h1>
            <p class="employee-cycle-subtitle">يمكنك من هنا عرض الدورة المؤرشفة أو استعادتها أو حذفها نهائيًا.</p>
        </div>

        <a href="{{ route($routeBase . '.enrollment_cycles.index') }}" class="employee-action-btn employee-action-btn--neutral employee-cycle-header-link">
            العودة إلى إدارة الدورات
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                    <tr>
                        <th>الدورة</th>
                        <th>الرمز</th>
                        <th>الفترة</th>
                        <th>الكيانات المفتوحة</th>
                        <th>أرشفت بتاريخ</th>
                        <th>أرشفت بواسطة</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($seasons as $season)
                        <tr>
                            <td>{{ $season->name }}</td>
                            <td>{{ $season->code ?: '—' }}</td>
                            <td>
                                {{ optional($season->registration_starts_at)->format('Y-m-d H:i') ?? '—' }}
                                -
                                {{ optional($season->registration_ends_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td>{{ $season->enabled_enrollment_cycles_count }}</td>
                            <td>{{ optional($season->archived_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $season->archivedBy?->name ?? '—' }}</td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <a href="{{ route($routeBase . '.archived_enrollment_cycles.show', $season) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                        عرض
                                    </a>
                                    <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.restore', $season) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد استعادة هذه الدورة العامة؟ ستعود إلى صفحة إدارة الدورات بحالة مغلقة.');">
                                        @csrf
                                        <button type="submit" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">
                                            استعادة
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.destroy', $season) }}" class="employee-inline-form" onsubmit="return confirm('سيتم حذف الدورة العامة نهائيًا مع جميع توابعها. هل تريد المتابعة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="employee-action-btn employee-action-btn--danger employee-action-btn--sm">
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد دورات عامة مؤرشفة حاليًا.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
