@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-calendar"></i> إدارة دورة التسجيل
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>تفاصيل الدورة</h4>
        <form method="POST" action="{{ route('admin.enrollment_cycles.update', $cycle) }}">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-3">
                    <label>كيان التسجيل</label>
                    <input type="text" class="form-control" value="{{ $cycle->registrableEntity?->display_title }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>النوع</label>
                    <input type="text" class="form-control" value="{{ $cycle->registrableEntity?->entity_type }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>اسم الدورة</label>
                    <input type="text" name="name" class="form-control" value="{{ $cycle->name }}" required>
                </div>
                <div class="col-md-2">
                    <label>بداية التسجيل</label>
                    <input type="datetime-local" name="registration_starts_at" class="form-control"
                           value="{{ $cycle->registration_starts_at?->format('Y-m-d\\TH:i') }}">
                </div>
                <div class="col-md-2">
                    <label>نهاية التسجيل</label>
                    <input type="datetime-local" name="registration_ends_at" class="form-control"
                           value="{{ $cycle->registration_ends_at?->format('Y-m-d\\TH:i') }}">
                </div>
            </div>
            <div class="row" style="margin-top:10px;">
                <div class="col-md-3">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        @foreach(['draft','open','closed','approved','cancelled'] as $status)
                            <option value="{{ $status }}" @selected($cycle->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top:10px;">حفظ</button>
        </form>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;padding:15px;">
        <h4>مواد الدورة</h4>
        <form method="POST" action="{{ route('admin.enrollment_cycles.subjects', $cycle) }}">
            @csrf
            <div class="row">
                @foreach($subjects as $subject)
                    <div class="col-md-4" style="margin-bottom:10px;">
                        <label>
                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}"
                                   @checked($cycle->registrableSubjects->contains($subject->id))>
                            {{ $subject->name }}
                            <small class="text-muted">
                                ({{ $subjectStats[$subject->id] ?? 0 }} تسجيل)
                            </small>
                        </label>
                    </div>
                @endforeach
            </div>
            <button type="submit" class="btn btn-primary">تحديث المواد</button>
        </form>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;padding:15px;">
        <h4>إجراءات</h4>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <form method="POST" action="{{ route('admin.enrollment_cycles.open', $cycle) }}">
                @csrf
                <button type="submit" class="btn btn-success">فتح التسجيل</button>
            </form>
            <form method="POST" action="{{ route('admin.enrollment_cycles.close', $cycle) }}">
                @csrf
                <button type="submit" class="btn btn-warning">إغلاق التسجيل</button>
            </form>
            <form method="POST" action="{{ route('admin.enrollment_cycles.approve', $cycle) }}">
                @csrf
                <button type="submit" class="btn btn-primary">اعتماد الدورة</button>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;padding:15px;">
        <h4>الفصول المرتبطة بالدورة</h4>
        @if($semesters->isEmpty())
            <div class="text-muted">لا توجد فصول مضافة بعد</div>
        @else
            <table class="table table-striped" style="margin-top:10px;">
                <thead>
                    <tr>
                        <th>اسم الفصل</th>
                        <th>الرمز</th>
                        <th>البداية</th>
                        <th>النهاية</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($semesters as $semester)
                        <tr>
                            <td>{{ $semester->name }}</td>
                            <td>{{ $semester->code }}</td>
                            <td>{{ optional($semester->start_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ optional($semester->end_date)->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $semester->status }}</td>
                            <td>
                                <a class="btn btn-sm btn-success" href="{{ route('admin.semesters.sections.index', $semester) }}">
                                    إدارة الشعب
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;padding:15px;">
        <h4>بدء فصل جديد</h4>
        @if($semesters->isNotEmpty())
            <div class="alert alert-info" style="margin-bottom:0;">
                تم إنشاء فصل لهذه الدورة بالفعل. يمكنك إدارة الشعب من جدول الفصول أعلاه.
            </div>
        @else
            <form method="POST" action="{{ route('admin.enrollment_cycles.start_semester', $cycle) }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <label>اسم الفصل</label>
                        <input type="text" name="semester_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>رمز الفصل</label>
                        <input type="text" name="semester_code" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>تاريخ البداية</label>
                        <input type="date" name="start_date" class="form-control" required>
                    </div>
                    <div class="col-md-4" style="margin-top:10px;">
                        <label>تاريخ النهاية</label>
                        <input type="date" name="end_date" class="form-control">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:10px;">بدء الفصل</button>
            </form>
        @endif
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;">
        <div class="panel-body">
            <h4 style="margin-top:0;">تسجيلات الدورة</h4>
            <div class="row" style="margin-bottom:10px;">
                <div class="col-md-12">
                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <span class="label label-default">الكل: {{ $statusCounts->sum() }}</span>
                        <span class="label label-info">قيد المراجعة: {{ $statusCounts['under_review'] ?? 0 }}</span>
                        <span class="label label-success">مقبولة: {{ $statusCounts['accepted'] ?? 0 }}</span>
                        <span class="label label-danger">مرفوضة: {{ $statusCounts['rejected'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('admin.enrollment_cycles.show', $cycle) }}" class="row" style="margin-bottom:10px;">
                <div class="col-md-3">
                    <label>فلترة الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">الكل</option>
                        <option value="under_review" @selected($filterStatus === 'under_review')>قيد المراجعة</option>
                        <option value="accepted" @selected($filterStatus === 'accepted')>مقبولة</option>
                        <option value="rejected" @selected($filterStatus === 'rejected')>مرفوضة</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>فلترة المادة</label>
                    <select name="subject_id" class="form-control">
                        <option value="">الكل</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string)$filterSubjectId === (string)$subject->id)>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" style="margin-top:25px;">
                    <button type="submit" class="btn btn-primary">تطبيق</button>
                    <a href="{{ route('admin.enrollment_cycles.show', $cycle) }}" class="btn btn-default">إزالة</a>
                </div>
            </form>

            <form id="bulkStatusForm" method="POST" action="{{ route('admin.enrollment_cycles.registrations.bulk_status', $cycle) }}" style="margin-bottom:10px;">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>تغيير جماعي للحالة</label>
                        <select name="status" class="form-control" required>
                            <option value="">اختر</option>
                            <option value="accepted">قبول</option>
                            <option value="rejected">رفض</option>
                            <option value="under_review">إرجاع للمراجعة</option>
                        </select>
                    </div>
                    <div class="col-md-3" style="margin-top:25px;">
                        <button type="submit" class="btn btn-primary">تطبيق على المحدد</button>
                    </div>
                </div>
            </form>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAllRegistrations"></th>
                        <th>الطالب</th>
                        <th>المواد</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($registrations as $registration)
                        @php
                            $statusLabel = [
                                'under_review' => 'قيد المراجعة',
                                'accepted' => 'مقبولة',
                                'rejected' => 'مرفوضة',
                            ][$registration->status] ?? $registration->status;
                        @endphp
                        <tr>
                            <td>
                                <input
                                    type="checkbox"
                                    name="registration_ids[]"
                                    value="{{ $registration->id }}"
                                    form="bulkStatusForm"
                                >
                            </td>
                            <td>
                                {{ $registration->student?->first_name }} {{ $registration->student?->last_name }}
                                <div class="text-muted" style="font-size:12px;">
                                    {{ $registration->student?->email }}
                                </div>
                            </td>
                            <td>
                                    @foreach($registration->registrableSubjects as $subject)
                                        <div>{{ $subject->name }}</div>
                                    @endforeach
                            </td>
                            <td>{{ $statusLabel }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.enrollment_cycles.registrations.status', [$cycle, $registration]) }}" style="display:flex;gap:6px;align-items:center;">
                                    @csrf
                                    <select name="status" class="form-control input-sm" style="min-width:140px;">
                                        <option value="under_review" @selected($registration->status === 'under_review')>قيد المراجعة</option>
                                        <option value="accepted" @selected($registration->status === 'accepted')>مقبول</option>
                                        <option value="rejected" @selected($registration->status === 'rejected')>مرفوض</option>
                                    </select>
                                    <button type="submit" class="btn btn-xs btn-primary">حفظ</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($registrations->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">لا توجد تسجيلات لهذه الدورة</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    const selectAll = document.getElementById('selectAllRegistrations');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('input[name="registration_ids[]"][form="bulkStatusForm"]').forEach(cb => {
                cb.checked = selectAll.checked;
            });
        });
    }
</script>
@endsection
