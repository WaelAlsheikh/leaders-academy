<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-archive"></i> تفاصيل دورة مؤرشفة
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="alert alert-warning">
        هذه الدورة مؤرشفة وتُعرض للقراءة فقط. لا يمكن تعديل بياناتها أو حالاتها من هذه الصفحة.
    </div>

    <div style="margin-bottom:15px; display:flex; gap:10px; flex-wrap:wrap;">
        <a href="{{ route($routeBase . '.archived_enrollment_cycles.index') }}" class="btn btn-default">
            العودة إلى الدورات المؤرشفة
        </a>
        <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.restore', $cycle) }}" style="display:inline-block;" onsubmit="return confirm('هل تريد استعادة هذه الدورة؟');">
            @csrf
            <button type="submit" class="btn btn-success">استعادة الدورة</button>
        </form>
        <form method="POST" action="{{ route($routeBase . '.archived_enrollment_cycles.destroy', $cycle) }}" style="display:inline-block;" onsubmit="return confirm('سيتم حذف الدورة نهائياً مع كل التوابع المرتبطة بها. هل تريد المتابعة؟');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">حذف الدورة نهائياً</button>
        </form>
    </div>

    <div class="panel panel-bordered" style="padding:15px;">
        <h4>تفاصيل الدورة</h4>
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
                <input type="text" class="form-control" value="{{ $cycle->name }}" disabled>
            </div>
            <div class="col-md-3">
                <label>الحالة</label>
                <input type="text" class="form-control" value="{{ $cycle->status }}" disabled>
            </div>
            <div class="col-md-3" style="margin-top:10px;">
                <label>بداية التسجيل</label>
                <input type="text" class="form-control" value="{{ $cycle->registration_starts_at?->format('Y-m-d H:i') ?? '—' }}" disabled>
            </div>
            <div class="col-md-3" style="margin-top:10px;">
                <label>نهاية التسجيل</label>
                <input type="text" class="form-control" value="{{ $cycle->registration_ends_at?->format('Y-m-d H:i') ?? '—' }}" disabled>
            </div>
            <div class="col-md-3" style="margin-top:10px;">
                <label>تاريخ الأرشفة</label>
                <input type="text" class="form-control" value="{{ $cycle->archiveRecord?->archived_at?->format('Y-m-d H:i') ?? '—' }}" disabled>
            </div>
            <div class="col-md-3" style="margin-top:10px;">
                <label>أرشفت بواسطة</label>
                <input type="text" class="form-control" value="{{ $cycle->archiveRecord?->archivedBy?->name ?? '—' }}" disabled>
            </div>
        </div>
    </div>

    <div class="panel panel-bordered" style="margin-top:20px;padding:15px;">
        <h4>مواد الدورة</h4>
        <div class="row">
            @foreach($subjects as $subject)
                <div class="col-md-4" style="margin-bottom:10px;">
                    <label>
                        <input type="checkbox" disabled
                               @checked($cycle->registrableSubjects->contains($subject->id))>
                        {{ $subject->name }}
                        <small class="text-muted">
                            ({{ $subjectStats[$subject->id] ?? 0 }} تسجيل)
                        </small>
                    </label>
                </div>
            @endforeach
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
                        </tr>
                    @endforeach
                </tbody>
            </table>
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

            <form method="GET" action="{{ route($routeBase . '.archived_enrollment_cycles.show', $cycle) }}" class="row" style="margin-bottom:10px;">
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
                            <option value="{{ $subject->id }}" @selected((string) $filterSubjectId === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3" style="margin-top:25px;">
                    <button type="submit" class="btn btn-primary">تطبيق</button>
                    <a href="{{ route($routeBase . '.archived_enrollment_cycles.show', $cycle) }}" class="btn btn-default">إزالة</a>
                </div>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الطالب</th>
                        <th>المواد</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $registration)
                        @php
                            $statusLabel = [
                                'under_review' => 'قيد المراجعة',
                                'accepted' => 'مقبولة',
                                'rejected' => 'مرفوضة',
                            ][$registration->status] ?? $registration->status;
                        @endphp
                        <tr>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">لا توجد تسجيلات لهذه الدورة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
