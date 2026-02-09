@extends('voyager::master')

@section('content')
<div class="container-fluid">

    <h1 class="page-title">
        <i class="voyager-book"></i>
        مواد كلية {{ $college->title }}
    </h1>

    {{-- إضافة مادة --}}
    <form method="POST" action="{{ route('admin.subjects.store', $college) }}" class="panel panel-bordered">
        @csrf
        <div class="panel-body">
            <input name="name" class="form-control" placeholder="اسم المادة" required>
            <br>
            <input name="code" class="form-control" placeholder="كود المادة" required>
            <br>
            <input name="credit_hours" type="number" class="form-control" placeholder="عدد الساعات" required>
            <br>
            <button class="btn btn-success">➕ إضافة مادة</button>
        </div>
    </form>

    {{-- جدول المواد --}}
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>الاسم</th>
            <th>الكود</th>
            <th>الساعات</th>
            <th>إجراءات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($subjects as $subject)
            <tr>
                <td>{{ $subject->name }}</td>
                <td>{{ $subject->code }}</td>
                <td>{{ $subject->credit_hours }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection
