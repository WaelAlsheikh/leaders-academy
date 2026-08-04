@php $mode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager'; @endphp
<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $mode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">قوائم التوزيع</h1>
            <p class="employee-cycle-subtitle">students@ / doctors@ ومزامنة تلقائية حسب النوع.</p>
        </div>
        <a href="{{ route($routeBase.'.email.accounts.index') }}" class="employee-action-btn employee-action-btn--neutral">الصناديق</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body">
            <form method="POST" action="{{ route($routeBase.'.email.lists.store') }}" style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
                @csrf
                <div class="form-group"><label>العنوان</label><input name="address" class="form-control" placeholder="students@leaders-academy.net" required></div>
                <div class="form-group"><label>الاسم</label><input name="name" class="form-control" required></div>
                <div class="form-group">
                    <label>مزامنة حسب النوع</label>
                    <select name="identity_type" class="form-control">
                        <option value="">— يدوي —</option>
                        @foreach(['student','doctor','employee','admin'] as $t)
                            <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="employee-action-btn employee-action-btn--primary">إنشاء</button>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <table class="table table-striped employee-cycle-table">
                <thead><tr><th>العنوان</th><th>الاسم</th><th>الأعضاء</th><th></th></tr></thead>
                <tbody>
                @forelse($lists as $list)
                    <tr>
                        <td>{{ $list->address }}</td>
                        <td>{{ $list->name }}</td>
                        <td>{{ $list->members_count }}</td>
                        <td>
                            <form method="POST" action="{{ route($routeBase.'.email.lists.sync', $list) }}">@csrf<button class="employee-action-btn employee-action-btn--neutral">مزامنة</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">لا توجد قوائم.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $lists->links() }}
        </div>
    </div>
</div>
