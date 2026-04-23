@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp
<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-archive"></i> الدورات المؤرشفة
            </h1>
            <p class="employee-cycle-subtitle">الدورات المؤرشفة تبقى محفوظة بكل بياناتها ويمكن استعادتها أو حذفها النهائي عند الحاجة.</p>
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
                        <th>الكيان</th>
                        <th>النوع</th>
                        <th>الدورة</th>
                        <th>الرمز</th>
                        <th>الحالة الحالية</th>
                        <th>تاريخ الأرشفة</th>
                        <th>أرشفت بواسطة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cycles as $cycle)
                        <tr>
                            <td>{{ $cycle->registrableEntity?->display_title }}</td>
                            <td>{{ $cycle->registrableEntity?->entity_type }}</td>
                            <td>{{ $cycle->name }}</td>
                            <td>{{ $cycle->code ?: '—' }}</td>
                            <td>
                                <span class="employee-cycle-status employee-cycle-status--{{ \Illuminate\Support\Str::slug($cycle->status, '-') }}">
                                    {{ $cycle->status }}
                                </span>
                            </td>
                            <td>{{ optional($cycle->archiveRecord?->archived_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $cycle->archiveRecord?->archivedBy?->name ?? '—' }}</td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <a href="{{ route($routeBase . '.archived_enrollment_cycles.show', $cycle) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                        عرض التفاصيل
                                    </a>
                                    <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.restore', $cycle) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد استعادة هذه الدورة إلى صفحة إدارة دورات التسجيل؟');">
                                        @csrf
                                        <button type="submit" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">
                                            استعادة الدورة
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.destroy', $cycle) }}" class="employee-inline-form" onsubmit="return confirm('سيتم حذف الدورة نهائياً مع كل التوابع المرتبطة بها. هل تريد المتابعة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="employee-action-btn employee-action-btn--danger employee-action-btn--sm">
                                            حذف الدورة
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">لا توجد دورات مؤرشفة</td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
