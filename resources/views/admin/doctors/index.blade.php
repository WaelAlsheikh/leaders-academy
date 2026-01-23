@extends('voyager::master')

@section('content')
<div class="page-content container-fluid">

    <h2>إدارة الأساتذة الجامعيين</h2>

    <div class="btn-group mb-3">
        <a href="?status=all" class="btn btn-primary">الكل</a>
        <a href="?status=active" class="btn btn-success">نشط</a>
        <a href="?status=inactive" class="btn btn-danger">غير نشط</a>
    </div>

    <form method="POST" action="{{ route('admin.doctors.store') }}">
        @csrf
        <input name="full_name" placeholder="الاسم الكامل" required>
        <input name="email" placeholder="البريد الإلكتروني" required>
        <input name="academic_degree" placeholder="الدرجة العلمية">
        <input name="specialization" placeholder="الاختصاص">
        <button class="btn btn-primary">إضافة دكتور</button>
    </form>

    <table class="table mt-4">
        <thead>
            <tr>
                <th>الاسم</th>
                <th>الإيميل</th>
                <th>الحالة</th>
                <th>تحكم</th>
            </tr>
        </thead>
        <tbody>
        @foreach($doctors as $doctor)
            <tr>
                <td>{{ $doctor->full_name }}</td>
                <td>{{ $doctor->email }}</td>
                <td>
                    @if($doctor->is_active)
                        <span class="label label-success">نشط</span>
                    @else
                        <span class="label label-danger">غير نشط</span>
                    @endif
                </td>
                <td>
                    <form method="POST" action="{{ route('admin.doctors.toggle', $doctor) }}">
                        @csrf
                        <button class="btn btn-sm btn-warning">
                            تبديل الحالة
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection
