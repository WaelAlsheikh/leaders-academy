<div class="{{ $portalContext === 'employee' ? '' : 'container-fluid' }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title">
                <i class="voyager-book"></i> مواد كلية {{ $college->title }}
            </h1>
            <p class="doctor-portal-meta">إدارة المواد التابعة لهذه الكلية من حيث الإضافة والتعديل والحذف.</p>
        </div>
        <a href="{{ route($routeBase . '.colleges.index') }}" class="btn btn-default">العودة إلى الكليات</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route($routeBase . '.subjects.store', $college) }}" class="panel panel-bordered employee-management-panel">
        @csrf
        <div class="panel-body">
            <h4>إضافة مادة جديدة</h4>
            <div class="row">
                <div class="col-md-3">
                    <label>اسم المادة</label>
                    <input name="name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>كود المادة</label>
                    <input name="code" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>عدد الساعات</label>
                    <input name="credit_hours" type="number" min="1" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label style="display:flex;align-items:center;gap:6px;margin-top:30px;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        نشط
                    </label>
                </div>
            </div>
            <button class="btn btn-success" style="margin-top:12px;">إضافة مادة</button>
        </div>
    </form>

    <table class="table table-bordered employee-subject-table">
        <thead>
        <tr>
            <th>الاسم</th>
            <th>الكود</th>
            <th>الساعات</th>
            <th>نشط</th>
            <th>إجراءات</th>
        </tr>
        </thead>
        <tbody>
        @forelse($subjects as $subject)
            <tr>
                <td>{{ $subject->name }}</td>
                <td>{{ $subject->code }}</td>
                <td>{{ $subject->credit_hours }}</td>
                <td>{{ $subject->is_active ? 'نعم' : 'لا' }}</td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#edit-subject-{{ $subject->id }}">
                        تعديل
                    </button>
                    <form method="POST" action="{{ route($routeBase . '.subjects.destroy', $subject) }}" style="display:inline-block;" onsubmit="return confirm('هل تريد حذف هذه المادة؟');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            <tr id="edit-subject-{{ $subject->id }}" class="collapse">
                <td colspan="5" style="background:#f9f9f9;">
                    <form method="POST" action="{{ route($routeBase . '.subjects.update', $subject) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-3">
                                <label>اسم المادة</label>
                                <input name="name" class="form-control" value="{{ $subject->name }}" required>
                            </div>
                            <div class="col-md-2">
                                <label>الكود</label>
                                <input name="code" class="form-control" value="{{ $subject->code }}" required>
                            </div>
                            <div class="col-md-2">
                                <label>الساعات</label>
                                <input name="credit_hours" type="number" min="1" class="form-control" value="{{ $subject->credit_hours }}" required>
                            </div>
                            <div class="col-md-1">
                                <label>نشط</label>
                                <div>
                                    <input type="checkbox" name="is_active" value="1" @checked($subject->is_active)>
                                </div>
                            </div>
                            <div class="col-md-2" style="margin-top:24px;">
                                <button class="btn btn-success btn-sm">حفظ</button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">لا توجد مواد حالياً</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
