@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title"><i class="voyager-list"></i> إدارة كيانات التسجيل</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered">
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>النوع</th>
                    <th>الاسم</th>
                    <th>سعر الساعة</th>
                    <th>نشط</th>
                    <th>إجراءات</th>
                </tr>
                </thead>
                <tbody>
                @foreach($entities as $entity)
                    <tr>
                        <td>{{ $entity->entity_type }}</td>
                        <td>{{ $entity->display_title }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.registrables.update', $entity) }}" style="display:flex;gap:8px;align-items:center;">
                                @csrf
                                @method('PUT')
                                <input type="number" step="0.01" min="0" name="price_per_credit_hour" value="{{ $entity->price_per_credit_hour }}" class="form-control input-sm" style="width:120px;">
                                <label style="display:flex;align-items:center;gap:4px;margin:0;">
                                    <input type="checkbox" name="is_active" value="1" @checked($entity->is_active)>
                                    نشط
                                </label>
                                <button type="submit" class="btn btn-xs btn-primary">حفظ</button>
                            </form>
                        </td>
                        <td>{{ $entity->is_active ? 'نعم' : 'لا' }}</td>
                        <td>
                            <a href="{{ route('admin.registrables.subjects', $entity) }}" class="btn btn-sm btn-success">
                                إدارة المواد
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

