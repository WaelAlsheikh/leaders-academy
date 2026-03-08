@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-calendar"></i> إدارة دورات التسجيل
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>إنشاء دورة تسجيل جديدة</h4>
        <form method="POST" action="{{ route('admin.enrollment_cycles.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <label>كيان التسجيل</label>
                    <select name="registrable_entity_id" class="form-control" required>
                        <option value="">اختر</option>
                        @foreach($registrableEntities as $entity)
                            <option value="{{ $entity->id }}">
                                [{{ $entity->entity_type }}] {{ $entity->display_title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>اسم الدورة</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>بداية التسجيل</label>
                    <input type="datetime-local" name="registration_starts_at" class="form-control">
                </div>
                <div class="col-md-3">
                    <label>نهاية التسجيل</label>
                    <input type="datetime-local" name="registration_ends_at" class="form-control">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:10px;">إضافة</button>
        </form>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;">
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الكلية</th>
                        <th>النوع</th>
                        <th>الدورة</th>
                        <th>الحالة</th>
                        <th>الفصل</th>
                        <th>المدة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cycles as $cycle)
                        <tr>
                            <td>{{ $cycle->registrableEntity?->display_title }}</td>
                            <td>{{ $cycle->registrableEntity?->entity_type }}</td>
                            <td>{{ $cycle->name }}</td>
                            <td>{{ $cycle->status }}</td>
                            <td>
                                @if($cycle->semester)
                                    {{ $cycle->semester->name }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                {{ optional($cycle->registration_starts_at)->format('Y-m-d H:i') ?? '—' }}
                                -
                                {{ optional($cycle->registration_ends_at)->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td>
                                <a href="{{ route('admin.enrollment_cycles.show', $cycle) }}" class="btn btn-sm btn-primary">
                                    إدارة
                                </a>
                                @if($cycle->semester)
                                    <a href="{{ route('admin.semesters.sections.index', $cycle->semester) }}" class="btn btn-sm btn-success">
                                        إدارة الشعب
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    @if($cycles->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center">لا توجد دورات</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
