@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title"><i class="voyager-study"></i> مواد الكيان: {{ $entity->display_title }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>إضافة مادة</h4>
        <form method="POST" action="{{ route('admin.registrables.subjects.store', $entity) }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label>الاسم</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>الكود</label>
                    <input type="text" name="code" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>الساعات</label>
                    <input type="number" min="1" name="credit_hours" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>نشط</label>
                    <div><input type="checkbox" name="is_active" value="1" checked></div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:10px;">إضافة</button>
        </form>
    </div>

    <div class="panel panel-bordered">
        <div class="panel-body">
            <table class="table table-striped">
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
                        <td>{{ $subject->code ?: '—' }}</td>
                        <td>{{ $subject->credit_hours }}</td>
                        <td>{{ $subject->is_active ? 'نعم' : 'لا' }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.registrable_subjects.update', $subject) }}" style="display:inline-flex;gap:6px;align-items:center;">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $subject->name }}" class="form-control input-sm" style="width:130px;" required>
                                <input type="text" name="code" value="{{ $subject->code }}" class="form-control input-sm" style="width:90px;">
                                <input type="number" min="1" name="credit_hours" value="{{ $subject->credit_hours }}" class="form-control input-sm" style="width:80px;" required>
                                <label style="display:flex;align-items:center;gap:3px;margin:0;">
                                    <input type="checkbox" name="is_active" value="1" @checked($subject->is_active)>
                                    نشط
                                </label>
                                <button type="submit" class="btn btn-xs btn-primary">حفظ</button>
                            </form>
                            <form method="POST" action="{{ route('admin.registrable_subjects.destroy', $subject) }}" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-xs btn-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">لا توجد مواد</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

