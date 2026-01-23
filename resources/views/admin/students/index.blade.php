@extends('voyager::master')

@section('page_title', 'إدارة الطلاب')

@section('content')
<div class="page-content container-fluid">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
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
                        <td>{{ $student->email }}</td>
                        <td>{{ $student->phone }}</td>
                        <td>
                            @if($student->is_active)
                                <span class="label label-success">نشط</span>
                            @else
                                <span class="label label-danger">غير نشط</span>
                            @endif
                        </td>
                        <td>
                            <form method="POST"
                                  action="{{ route('admin.students.toggle', $student) }}">
                                @csrf
                                <button class="btn btn-xs btn-warning">
                                    تبديل الحالة
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            لا يوجد طلاب
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $students->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
