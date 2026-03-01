@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-list"></i> شعب الفصل
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>إضافة شعبة</h4>
        <form method="POST" action="{{ route('admin.semesters.sections.store', $semester) }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label>المادة</label>
                    <select name="subject_id" class="form-control" required>
                        <option value="">اختر</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label>اسم الشعبة</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>طريقة الحضور</label>
                    <select name="mode" class="form-control" required>
                        <option value="online">أونلاين</option>
                        <option value="in_person">حضوري</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label>رابط Zoom</label>
                    <input type="url" name="zoom_url" class="form-control">
                </div>
                <div class="col-md-4" style="margin-top:10px;">
                    <label>ملاحظات</label>
                    <input type="text" name="notes" class="form-control">
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
                        <th>المادة</th>
                        <th>الشعبة</th>
                        <th>الطريقة</th>
                        <th>Zoom</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semester->classSections as $section)
                        <tr>
                            <td>{{ $section->subject?->name }}</td>
                            <td>{{ $section->name }}</td>
                            <td>{{ $section->mode === 'online' ? 'أونلاين' : 'حضوري' }}</td>
                            <td>
                                @if($section->zoom_url)
                                    <a href="{{ $section->zoom_url }}" target="_blank">فتح</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.sections.meetings.index', $section) }}" class="btn btn-xs btn-primary">
                                    إدارة الجلسات
                                </a>
                                <form method="POST" action="{{ route('admin.sections.update', $section) }}" style="display:inline-block;">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $section->name }}">
                                    <input type="hidden" name="mode" value="{{ $section->mode }}">
                                    <input type="hidden" name="zoom_url" value="{{ $section->zoom_url }}">
                                    <input type="hidden" name="notes" value="{{ $section->notes }}">
                                    <button type="submit" class="btn btn-xs btn-default">تحديث سريع</button>
                                </form>
                                <form method="POST" action="{{ route('admin.sections.destroy', $section) }}" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($semester->classSections->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">لا توجد شعب</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
