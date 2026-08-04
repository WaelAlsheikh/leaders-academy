@php $mode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager'; @endphp
<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $mode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title"><i class="voyager-mail"></i> صناديق البريد المؤسسي</h1>
            <p class="employee-cycle-subtitle">إدارة الحسابات، الحصص، والحالة. Driver: {{ $health['driver'] ?? '—' }} — {{ ($health['ok'] ?? false) ? 'صحي' : 'غير متصل' }}</p>
        </div>
        <div class="employee-inline-form">
            <a href="{{ route($routeBase.'.email.lists.index') }}" class="employee-action-btn employee-action-btn--neutral">القوائم</a>
            <a href="{{ route($routeBase.'.email.logs.index') }}" class="employee-action-btn employee-action-btn--neutral">السجل</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body">
            <form method="GET" class="row" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                <div class="form-group"><label>بحث</label><input type="text" name="q" value="{{ request('q') }}" class="form-control"></div>
                <div class="form-group">
                    <label>النوع</label>
                    <select name="identity_type" class="form-control">
                        <option value="">—</option>
                        @foreach(['student','doctor','employee','admin','system'] as $t)
                            <option value="{{ $t }}" @selected(request('identity_type')===$t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">—</option>
                        @foreach(['pending','active','disabled','deleted'] as $s)
                            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="employee-action-btn employee-action-btn--primary">تصفية</button>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>الإيميل المؤسسي</th>
                            <th>النوع</th>
                            <th>الحالة</th>
                            <th>المزامنة</th>
                            <th>الحصة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($accounts as $account)
                        <tr>
                            <td><strong>{{ $account->institutional_email }}</strong></td>
                            <td>{{ $account->identity_type?->value ?? $account->identity_type }}</td>
                            <td>{{ $account->status }}</td>
                            <td>{{ $account->provisioning_status }}</td>
                            <td>{{ $account->quota_mb }} MB</td>
                            <td><a href="{{ route($routeBase.'.email.accounts.show', $account) }}" class="employee-action-btn employee-action-btn--neutral">عرض</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">لا توجد حسابات.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $accounts->links() }}
        </div>
    </div>
</div>
