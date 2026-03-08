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
        <div>المادة: {{ $section->registrableSubject?->name ?? $section->subject?->name }}</div>
        <div>الشعبة: {{ $section->name }}</div>
        <div>طريقة الحضور: {{ $section->mode === 'online' ? 'أونلاين' : 'حضوري' }}</div>
        <div>
            رابط Zoom:
            @if($section->zoom_url)
                <a href="{{ $section->zoom_url }}" target="_blank">فتح الرابط</a>
            @else
                —
            @endif
        </div>
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
            <h4 style="margin-top:0;">طلاب الشعبة</h4>
            <form method="POST" action="{{ route('admin.sections.students.attach', $section) }}" class="row" style="margin-bottom:10px;">
                @csrf
                <div class="col-md-4">
                    <label>إضافة طالب</label>
                    <select name="student_id" class="form-control" required>
                        <option value="">اختر الطالب</option>
                        @foreach($eligibleStudents as $student)
                            @if(!$section->students->contains('id', $student->id))
                                <option value="{{ $student->id }}">
                                    {{ $student->first_name }} {{ $student->last_name }} - {{ $student->username }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2" style="margin-top:25px;">
                    <button type="submit" class="btn btn-primary">إضافة</button>
                </div>
            </form>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>اسم المستخدم</th>
                        <th>البريد</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($section->students as $student)
                        <tr>
                            <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                            <td>{{ $student->username }}</td>
                            <td>{{ $student->email }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.sections.students.detach', [$section, $student]) }}" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">حذف من الشعبة</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">لا يوجد طلاب ضمن هذه الشعبة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;">
        <div class="panel-body">
            <h4 style="margin-top:0;">جلسات الشعبة</h4>
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
                                <button type="button" class="btn btn-xs btn-default" data-toggle="collapse" data-target="#edit-meeting-{{ $meeting->id }}">
                                    تعديل
                                </button>
                                <form method="POST" action="{{ route('admin.meetings.destroy', $meeting) }}" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-meeting-{{ $meeting->id }}" class="collapse">
                            <td colspan="4" style="background:#f9f9f9;">
                                <form method="POST" action="{{ route('admin.meetings.update', $meeting) }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label>اليوم</label>
                                            <select name="day_of_week" class="form-control" required>
                                                <option value="0" @selected((int)$meeting->day_of_week === 0)>الأحد</option>
                                                <option value="1" @selected((int)$meeting->day_of_week === 1)>الاثنين</option>
                                                <option value="2" @selected((int)$meeting->day_of_week === 2)>الثلاثاء</option>
                                                <option value="3" @selected((int)$meeting->day_of_week === 3)>الأربعاء</option>
                                                <option value="4" @selected((int)$meeting->day_of_week === 4)>الخميس</option>
                                                <option value="5" @selected((int)$meeting->day_of_week === 5)>الجمعة</option>
                                                <option value="6" @selected((int)$meeting->day_of_week === 6)>السبت</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label>من</label>
                                            <input type="time" name="starts_at" class="form-control" value="{{ substr($meeting->starts_at, 0, 5) }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>إلى</label>
                                            <input type="time" name="ends_at" class="form-control" value="{{ substr($meeting->ends_at, 0, 5) }}" required>
                                        </div>
                                        <div class="col-md-3">
                                            <label>تاريخ البداية</label>
                                            <input type="date" name="start_date" class="form-control" value="{{ $meeting->start_date }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>تاريخ النهاية</label>
                                            <input type="date" name="end_date" class="form-control" value="{{ $meeting->end_date }}">
                                        </div>
                                        <div class="col-md-1" style="margin-top:24px;">
                                            <button type="submit" class="btn btn-sm btn-success">حفظ</button>
                                        </div>
                                    </div>
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
