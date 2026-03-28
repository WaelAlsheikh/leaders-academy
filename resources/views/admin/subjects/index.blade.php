@extends('voyager::master')

@section('content')
<div class="container-fluid">

    <h1 class="page-title">
        <i class="voyager-book"></i>
        مواد كلية {{ $college->title }}
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    {{-- إضافة مادة --}}
    <form method="POST" action="{{ route('admin.subjects.store', $college) }}" class="panel panel-bordered">
        @csrf
        <div class="panel-body">
            <div class="row">
                <div class="col-md-3">
                    <input name="name" class="form-control" placeholder="اسم المادة" required>
                </div>
                <div class="col-md-2">
                    <input name="code" class="form-control" placeholder="كود المادة" required>
                </div>
                <div class="col-md-2">
                    <input name="credit_hours" type="number" min="1" class="form-control" placeholder="عدد الساعات" required>
                </div>
                <div class="col-md-2">
                    <label style="display:flex;align-items:center;gap:6px;margin-top:8px;">
                        <input type="checkbox" name="is_active" value="1" checked>
                        نشط
                    </label>
                </div>
            </div>
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
            <th>نشط</th>
            <th>إجراءات</th>
        </tr>
        </thead>
        <tbody>
        @foreach($subjects as $subject)
            <tr>
                <td>{{ $subject->name }}</td>
                <td>{{ $subject->code }}</td>
                <td>{{ $subject->credit_hours }}</td>
                <td>{{ $subject->is_active ? 'نعم' : 'لا' }}</td>
                <td>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#edit-subject-{{ $subject->id }}">
                        تعديل
                    </button>
                    <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" style="display:inline-block;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">حذف</button>
                    </form>
                </td>
            </tr>
            <tr id="edit-subject-{{ $subject->id }}" class="collapse">
                <td colspan="5" style="background:#f9f9f9;">
                    <form method="POST" action="{{ route('admin.subjects.update', $subject) }}">
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
                            <div class="col-md-1" style="margin-top:24px;">
                                <button class="btn btn-success btn-sm">حفظ</button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
@endsection
