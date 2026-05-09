@extends('voyager::master')

@section('page_title', 'إدارة الطلاب')

@section('css')
    @include('admin.partials.voyager_custom_styles')
    <style>
        .student-mgmt-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .student-mgmt-pagination {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
        }
        .student-mgmt-pagination .pagination {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .student-mgmt-pagination .pagination > li > a,
        .student-mgmt-pagination .pagination > li > span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            min-width: 40px;
            padding: 8px 16px;
            border-radius: 10px;
            border: 1px solid rgba(13, 92, 134, 0.18);
            background: #fff;
            color: #083b59;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 12px rgba(13, 92, 134, 0.08);
            transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.15s;
        }
        .student-mgmt-pagination .pagination > li > a:hover {
            background: linear-gradient(135deg, #ffd84d 0%, #efbf1d 100%);
            color: #083b59;
            border-color: rgba(214, 162, 10, 0.45);
            box-shadow: 0 8px 18px rgba(244, 194, 29, 0.25);
            transform: translateY(-1px);
        }
        .student-mgmt-pagination .pagination > .active > span {
            background: linear-gradient(135deg, #22a7f0 0%, #1a8bc7 100%);
            color: #fff;
            border-color: rgba(26, 139, 199, 0.5);
            box-shadow: 0 8px 18px rgba(34, 167, 240, 0.25);
        }
        .student-mgmt-pagination .pagination > .disabled > span {
            opacity: 0.55;
            cursor: not-allowed;
            box-shadow: none;
        }
    </style>
@endsection

@section('content')
<div class="page-content container-fluid">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- Filter Buttons --}}
    <div class="btn-group mb-4">
        <a href="{{ route('admin.students.management') }}"
           class="btn {{ $filter === 'all' ? 'btn-primary' : 'btn-default' }}">
            عرض الكل
        </a>

        <a href="{{ route('admin.students.management', ['status' => 'active']) }}"
           class="btn {{ $filter === 'active' ? 'btn-success' : 'btn-default' }}">
            نشط
        </a>

        <a href="{{ route('admin.students.management', ['status' => 'inactive']) }}"
           class="btn {{ $filter === 'inactive' ? 'btn-danger' : 'btn-default' }}">
            غير نشط
        </a>
    </div>

    {{-- Table --}}
    <div class="panel panel-bordered">
        <div class="panel-body">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>Email</th>
                    <th>الهاتف</th>
                    <th>الحالة</th>
                    <th>إجراء</th>
                </tr>
                </thead>
                <tbody>
                @forelse($students as $student)
                    <tr>
                        <td>{{ $student->id }}</td>
                        <td>
                            {{ $student->first_name }}
                            {{ $student->last_name }}
                        </td>
                        <td><code style="font-size:12px;">{{ $student->username }}</code></td>
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone ?: '—' }}</td>
                        <td>
                            @if($student->is_active)
                                <span class="label label-success">نشط</span>
                            @else
                                <span class="label label-danger">غير نشط</span>
                            @endif
                        </td>
                        <td>
                            <div class="student-mgmt-actions">
                                <form method="POST"
                                      action="{{ route('admin.students.toggle', $student) }}"
                                      style="display:inline-block;margin:0;">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-warning">
                                        تبديل الحالة
                                    </button>
                                </form>
                                <button type="button"
                                        class="btn btn-xs btn-info"
                                        data-toggle="collapse"
                                        data-target="#reset-student-{{ $student->id }}">
                                    إعادة تعيين كلمة المرور
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr id="reset-student-{{ $student->id }}" class="collapse">
                        <td colspan="7" style="background:#f3f8ff;">
                            <form method="POST" action="{{ route('admin.students.reset_password', $student) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-3">
                                        <label>كلمة المرور الجديدة</label>
                                        <input name="new_password" type="text" class="form-control" required autocomplete="new-password">
                                    </div>
                                    <div class="col-md-3">
                                        <label>تأكيد كلمة المرور الجديدة</label>
                                        <input name="new_password_confirmation" type="text" class="form-control" required autocomplete="new-password">
                                    </div>
                                    <div class="col-md-2" style="margin-top:24px;">
                                        <button type="submit" class="btn btn-info">تحديث كلمة المرور</button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">
                            لا يوجد طلاب
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="student-mgmt-pagination">
                {{ $students->withQueryString()->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection
