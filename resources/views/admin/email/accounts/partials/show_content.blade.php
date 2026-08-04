@php $mode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager'; @endphp
<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $mode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">{{ $account->institutional_email }}</h1>
            <p class="employee-cycle-subtitle">{{ $account->identity_type?->label() }} — {{ $account->status }} / {{ $account->provisioning_status }}</p>
        </div>
        <a href="{{ route($routeBase.'.email.accounts.index') }}" class="employee-action-btn employee-action-btn--neutral">العودة</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($account->last_error)<div class="alert alert-danger">{{ $account->last_error }}</div>@endif

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body employee-management-form-panel">
            <div class="employee-form-actions">
                @if($account->status !== 'disabled')
                    <form method="POST" action="{{ route($routeBase.'.email.accounts.disable', $account) }}">@csrf<button class="employee-action-btn employee-action-btn--neutral">تعطيل</button></form>
                @else
                    <form method="POST" action="{{ route($routeBase.'.email.accounts.enable', $account) }}">@csrf<button class="employee-action-btn employee-action-btn--primary">تفعيل</button></form>
                @endif
                <form method="POST" action="{{ route($routeBase.'.email.accounts.reset_password', $account) }}">@csrf<button class="employee-action-btn employee-action-btn--primary" onclick="return confirm('إعادة تعيين كلمة المرور؟');">إعادة تعيين كلمة المرور</button></form>
                @if(($portalContext ?? 'admin') === 'admin' && $account->status === 'active')
                    <button type="button" class="employee-action-btn employee-action-btn--neutral" id="webmail-sso-btn"
                            data-url="{{ route('admin.email.accounts.webmail_sso', $account) }}">فتح WebMail (SSO)</button>
                @endif
            </div>
            @if(($portalContext ?? 'admin') === 'admin')
            <script>
                (function () {
                    var btn = document.getElementById('webmail-sso-btn');
                    if (!btn) return;
                    btn.addEventListener('click', function () {
                        fetch(btn.dataset.url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(function (r) { return r.json(); }).then(function (data) {
                            if (data.redirect_url) window.open(data.redirect_url, '_blank');
                            else alert(data.note || 'SSO token issued');
                        }).catch(function () { alert('تعذّر إنشاء جلسة WebMail'); });
                    });
                })();
            </script>
            @endif
            <form method="POST" action="{{ route($routeBase.'.email.accounts.quota', $account) }}" style="margin-top:16px;display:flex;gap:10px;align-items:flex-end;">
                @csrf
                <div class="form-group"><label>الحصة (MB)</label><input type="number" name="quota_mb" class="form-control" value="{{ $account->quota_mb }}" required></div>
                <button class="employee-action-btn employee-action-btn--neutral">حفظ الحصة</button>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title">Aliases</h4>
            <ul>
                @forelse($account->aliases as $alias)
                    <li>
                        {{ $alias->source_email }} → {{ $alias->destination_email }}
                        <form method="POST" action="{{ route($routeBase.'.email.aliases.destroy', $alias) }}" style="display:inline">@csrf @method('DELETE')<button class="employee-action-btn employee-action-btn--neutral">حذف</button></form>
                    </li>
                @empty
                    <li class="text-muted">لا توجد aliases</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route($routeBase.'.email.accounts.aliases.store', $account) }}" style="margin-top:12px;display:flex;gap:10px;">
                @csrf
                <input type="email" name="source_email" class="form-control" placeholder="alias@leaders-academy.net" required>
                <button class="employee-action-btn employee-action-btn--primary">إضافة</button>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title">سجل التدقيق</h4>
            <table class="table table-striped employee-cycle-table">
                <thead><tr><th>العملية</th><th>الوقت</th><th>IP</th></tr></thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->ip }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
