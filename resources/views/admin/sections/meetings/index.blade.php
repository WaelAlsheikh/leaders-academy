@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-time"></i> جلسات الشعبة
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>تفاصيل الشعبة</h4>
        <div>الفصل: {{ $section->semester?->name }}</div>
        <div>المادة: {{ $section->subject?->name }}</div>
        <div>الشعبة: {{ $section->name }}</div>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;padding:15px;">
        <h4>إضافة جلسة</h4>
        <form method="POST" action="{{ route('admin.sections.meetings.store', $section) }}">
            @csrf
            <div class="row">
                <div class="col-md-2">
                    <label>اليوم</label>
                    <select name="day_of_week" class="form-control" required>
                        <option value="0">الأحد</option>
                        <option value="1">الاثنين</option>
                        <option value="2">الثلاثاء</option>
                        <option value="3">الأربعاء</option>
                        <option value="4">الخميس</option>
                        <option value="5">الجمعة</option>
                        <option value="6">السبت</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>من</label>
                    <input type="time" name="starts_at" class="form-control" required>
                </div>
                <div class="col-md-2">
                    <label>إلى</label>
                    <input type="time" name="ends_at" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>تاريخ البداية</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label>تاريخ النهاية</label>
                    <input type="date" name="end_date" class="form-control">
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
                        <th>اليوم</th>
                        <th>الوقت</th>
                        <th>الفترة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($section->meetings as $meeting)
                        <tr>
                            <td>
                                @switch($meeting->day_of_week)
                                    @case(0) الأحد @break
                                    @case(1) الاثنين @break
                                    @case(2) الثلاثاء @break
                                    @case(3) الأربعاء @break
                                    @case(4) الخميس @break
                                    @case(5) الجمعة @break
                                    @case(6) السبت @break
                                @endswitch
                            </td>
                            <td>{{ $meeting->starts_at }} - {{ $meeting->ends_at }}</td>
                            <td>{{ $meeting->start_date }} - {{ $meeting->end_date ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.meetings.destroy', $meeting) }}" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($section->meetings->isEmpty())
                        <tr>
                            <td colspan="4" class="text-center">لا توجد جلسات</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
