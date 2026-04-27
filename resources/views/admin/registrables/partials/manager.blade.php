@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-categories"></i> {{ $pageTitle }}
            </h1>
            <p class="employee-cycle-subtitle">{{ $pageDescription }}</p>
        </div>
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
                        <th>النوع</th>
                        <th>الاسم</th>
                        <th>الرمز والإعدادات</th>
                        <th>السنوات</th>
                        <th>سعر الساعة</th>
                        <th>نشط</th>
                        <th>إجراءات</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($entities as $entity)
                        @php
                            $sourceEntity = $entity->entity;
                            $entityCode = $sourceEntity->code ?? '—';
                        @endphp
                        <tr>
                            <td>{{ $entity->entity_type }}</td>
                            <td>{{ $entity->display_title }}</td>
                            <td>
                                <form method="POST" action="{{ route($routeBase . '.registrables.update', $entity) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="code" value="{{ $entityCode === '—' ? '' : $entityCode }}" class="form-control input-sm" style="width:110px;" placeholder="اختياري">
                                    <input type="number" step="0.01" min="0" name="price_per_credit_hour" value="{{ $entity->price_per_credit_hour }}" class="form-control input-sm" style="width:120px;">
                                    <label style="display:flex;align-items:center;gap:4px;margin:0;">
                                        <input type="checkbox" name="is_active" value="1" @checked($entity->is_active)>
                                        نشط
                                    </label>
                                    <button type="submit" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">حفظ</button>
                                </form>
                            </td>
                            <td>{{ $entity->study_years_count ?? $entity->studyYears->count() }}</td>
                            <td>${{ number_format((float) $entity->price_per_credit_hour, 2) }}</td>
                            <td>{{ $entity->is_active ? 'نعم' : 'لا' }}</td>
                            <td>
                                <div class="employee-cycle-actions">
                                    <a href="{{ route($routeBase . '.registrables.years', $entity) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                        إدارة السنوات
                                    </a>
                                    <a href="{{ route($routeBase . '.registrables.registrations.index', $entity) }}" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">
                                        طلبات التسجيل
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد عناصر حالياً.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
