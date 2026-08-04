@php $mode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager'; @endphp
<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $mode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">سجل تدقيق البريد</h1>
        </div>
        <a href="{{ route($routeBase.'.email.accounts.index') }}" class="employee-action-btn employee-action-btn--neutral">الصناديق</a>
    </div>
    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <table class="table table-striped employee-cycle-table">
                <thead><tr><th>العملية</th><th>الصندوق</th><th>الوقت</th><th>IP</th></tr></thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->account?->institutional_email ?? '—' }}</td>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->ip }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">لا سجلات.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $logs->links() }}
        </div>
    </div>
</div>
