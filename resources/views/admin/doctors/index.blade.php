@extends('voyager::master')

@section('css')
    @include('admin.partials.voyager_custom_styles')
@endsection

@section('content')
<div class="page-content container-fluid">
    <h2>إدارة الأساتذة الجامعيين</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="btn-group mb-3">
        <a href="?status=all" class="btn btn-primary">الكل</a>
        <a href="?status=active" class="btn btn-success">نشط</a>
        <a href="?status=inactive" class="btn btn-danger">غير نشط</a>
    </div>

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>إضافة أستاذ جديد</h4>
        <form method="POST" action="{{ route('admin.doctors.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <label>الاسم الكامل</label>
                    <input name="full_name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>اسم المستخدم</label>
                    <input name="username" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>البريد الإلكتروني</label>
                    <input name="email" type="email" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>كلمة المرور الابتدائية</label>
                    <input name="password" type="text" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>الحالة</label>
                    <div>
                        <label style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                            <input type="checkbox" name="is_active" value="1">
                            مفعل
                        </label>
                    </div>
                </div>
                <div class="col-md-3" style="margin-top:12px;">
                    <label>الدرجة العلمية</label>
                    <input name="academic_degree" class="form-control">
                </div>
                <div class="col-md-3" style="margin-top:12px;">
                    <label>الاختصاص</label>
                    <input name="specialization" class="form-control">
                </div>
            </div>
            <button class="btn btn-primary" style="margin-top:12px;">إضافة الأستاذ</button>
        </form>
    </div>

    <div class="panel panel-bordered">
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>البريد</th>
                        <th>الدرجة</th>
                        <th>الاختصاص</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($doctors as $doctor)
                    <tr>
                        <td>{{ $doctor->full_name }}</td>
                        <td>{{ $doctor->username ?? '—' }}</td>
                        <td>{{ $doctor->email }}</td>
                        <td>{{ $doctor->academic_degree ?: '—' }}</td>
                        <td>{{ $doctor->specialization ?: '—' }}</td>
                        <td>
                            @if($doctor->is_active)
                                <span class="label label-success">نشط</span>
                            @else
                                <span class="label label-danger">غير نشط</span>
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary" data-toggle="collapse" data-target="#edit-doctor-{{ $doctor->id }}">
                                تعديل
                            </button>
                            <button type="button" class="btn btn-sm btn-info" data-toggle="collapse" data-target="#reset-doctor-{{ $doctor->id }}">
                                إعادة تعيين كلمة المرور
                            </button>
                            <form method="POST" action="{{ route('admin.doctors.toggle', $doctor) }}" style="display:inline-block;">
                                @csrf
                                <button class="btn btn-sm btn-warning">
                                    {{ $doctor->is_active ? 'إيقاف' : 'تفعيل' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    <tr id="edit-doctor-{{ $doctor->id }}" class="collapse">
                        <td colspan="7" style="background:#f9f9f9;">
                            <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>الاسم الكامل</label>
                                        <input name="full_name" class="form-control" value="{{ $doctor->full_name }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>اسم المستخدم</label>
                                        <input name="username" class="form-control" value="{{ $doctor->username }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>البريد الإلكتروني</label>
                                        <input name="email" type="email" class="form-control" value="{{ $doctor->email }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>الدرجة العلمية</label>
                                        <input name="academic_degree" class="form-control" value="{{ $doctor->academic_degree }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label>الاختصاص</label>
                                        <input name="specialization" class="form-control" value="{{ $doctor->specialization }}">
                                    </div>
                                    <div class="col-md-2" style="margin-top:12px;">
                                        <label style="display:flex;align-items:center;gap:6px;margin-top:26px;">
                                            <input type="checkbox" name="is_active" value="1" @checked($doctor->is_active)>
                                            مفعل
                                        </label>
                                    </div>
                                    <div class="col-md-2" style="margin-top:36px;">
                                        <button class="btn btn-success">حفظ التعديلات</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                    <tr id="reset-doctor-{{ $doctor->id }}" class="collapse">
                        <td colspan="7" style="background:#f3f8ff;">
                            <form method="POST" action="{{ route('admin.doctors.reset_password', $doctor) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>كلمة المرور الجديدة</label>
                                        <input name="new_password" type="text" class="form-control" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>تأكيد كلمة المرور الجديدة</label>
                                        <input name="new_password_confirmation" type="text" class="form-control" required>
                                    </div>
                                    <div class="col-md-2" style="margin-top:24px;">
                                        <button class="btn btn-info">تحديث كلمة المرور</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">لا يوجد أساتذة حالياً</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
