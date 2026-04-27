<div class="container-fluid employee-cycle-page">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-calendar"></i> إدارة الكيان داخل الدورة
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $cycle->registrableEntity?->display_title }}
                @if($cycle->registrationSeason)
                    — ضمن <strong>{{ $cycle->registrationSeason->name }}</strong>
                    @if($cycle->registrationSeason->code)
                        ({{ $cycle->registrationSeason->code }})
                    @endif
                @endif
            </p>
        </div>
        @if($cycle->registrationSeason)
            <a href="{{ route($routeBase . '.registration_seasons.show', $cycle->registrationSeason) }}" class="employee-action-btn employee-action-btn--neutral">
                العودة إلى الدورة العامة
            </a>
        @else
            <a href="{{ route($routeBase . '.enrollment_cycles.index') }}" class="employee-action-btn employee-action-btn--neutral">
                العودة إلى الدورات
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="row employee-form-grid">
                <div class="col-md-3">
                    <label>اسم الدورة العامة</label>
                    <input type="text" class="form-control" value="{{ $cycle->registrationSeason?->name ?? $cycle->name }}" disabled>
                </div>
                <div class="col-md-2">
                    <label>رمز الدورة</label>
                    <input type="text" class="form-control" value="{{ $cycle->registrationSeason?->code ?? $cycle->code }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>بداية التسجيل</label>
                    <input type="text" class="form-control" value="{{ optional($cycle->registrationSeason?->registration_starts_at ?? $cycle->registration_starts_at)->format('Y-m-d H:i') ?? '—' }}" disabled>
                </div>
                <div class="col-md-3">
                    <label>نهاية التسجيل</label>
                    <input type="text" class="form-control" value="{{ optional($cycle->registrationSeason?->registration_ends_at ?? $cycle->registration_ends_at)->format('Y-m-d H:i') ?? '—' }}" disabled>
                </div>
                <div class="col-md-2">
                    <label>الحالة</label>
                    <input type="text" class="form-control" value="{{ $cycle->is_enabled ? 'مفتوح' : 'مغلق' }}" disabled>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <h4 style="margin-top:0;">المواد المفتوحة لهذا الكيان داخل الدورة</h4>
            <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.subjects', $cycle) }}">
                @csrf
                @foreach($groupedSubjects as $yearGroup)
                    <div style="margin-bottom:18px;">
                        <h5 style="margin:0 0 12px;color:#0d5c86;">
                            {{ $yearGroup['study_year']?->name ?? 'سنة غير محددة' }}
                        </h5>

                        @foreach($yearGroup['terms'] as $termGroup)
                            <div style="border:1px solid #e5edf2;border-radius:12px;padding:14px;margin-bottom:12px;">
                                <div style="font-weight:700;color:#083b59;margin-bottom:12px;">
                                    {{ $termGroup['study_term']?->name ?? 'فصل غير محدد' }}
                                    @if($termGroup['study_term']?->code)
                                        <small class="text-muted">({{ $termGroup['study_term']->code }})</small>
                                    @endif
                                </div>
                                <div class="row">
                                    @foreach($termGroup['subjects'] as $subject)
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
                            </div>
                        @endforeach
                    </div>
                @endforeach
                @if($groupedSubjects->isEmpty())
                    <div class="text-muted">لا توجد مواد مرتبطة بخطة هذا الكيان بعد.</div>
                @endif
                <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">حفظ المواد المفتوحة</button>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <h4 style="margin-top:0;">الشعب والجلسات</h4>
            @if($semesters->isEmpty())
                <div class="alert alert-warning" style="margin-bottom:0;">
                    لم يتم توليد فصل تشغيلي لهذا الكيان بعد. أغلق الصفحة وافتح الكيان مجددًا داخل الدورة العامة إذا استمرت المشكلة.
                </div>
            @else
                <table class="table table-striped employee-cycle-table" style="margin-top:10px;">
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
                                <a class="employee-action-btn employee-action-btn--success employee-action-btn--sm" href="{{ route($routeBase . '.semesters.sections.index', $semester) }}">
                                    إدارة الشعب والجلسات
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <h4 style="margin-top:0;">طلبات تسجيل هذا الكيان داخل الدورة</h4>
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

            <form method="GET" action="{{ route($routeBase . '.enrollment_cycles.show', $cycle) }}" class="row" style="margin-bottom:10px;">
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
                    <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">تطبيق</button>
                    <a href="{{ route($routeBase . '.enrollment_cycles.show', $cycle) }}" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">إزالة</a>
                </div>
                <div class="col-md-3" style="margin-top:25px;">
                    <a href="{{ route($routeBase . '.registrables.registrations.index', $cycle->registrableEntity) }}?season_id={{ $cycle->registrationSeason?->id }}" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">
                        عرض جميع طلبات الكيان
                    </a>
                </div>
            </form>

            <form id="bulkStatusForm" method="POST" action="{{ route($routeBase . '.enrollment_cycles.registrations.bulk_status', $cycle) }}" style="margin-bottom:10px;">
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
                        <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">تطبيق على المحدد</button>
                    </div>
                </div>
            </form>

            <table class="table table-striped employee-cycle-table">
                <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllRegistrations"></th>
                    <th>الطالب</th>
                    <th>المواد</th>
                    <th>الحالة</th>
                    <th>نتائج المواد</th>
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
                            <input type="checkbox" name="registration_ids[]" value="{{ $registration->id }}" form="bulkStatusForm">
                        </td>
                        <td>
                            {{ $registration->student?->first_name }} {{ $registration->student?->last_name }}
                            <div class="text-muted" style="font-size:12px;">
                                {{ $registration->student?->email }}
                            </div>
                        </td>
                        <td>
                            @foreach($registration->registrableSubjects as $subject)
                                <div>
                                    {{ $subject->name }}
                                    <small class="text-muted">
                                        — {{ $subject->studyTerm?->studyYear?->name ?? '—' }} / {{ $subject->studyTerm?->name ?? '—' }}
                                    </small>
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $statusLabel }}</td>
                        <td>
                            <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.registrations.results', [$cycle, $registration]) }}">
                                @csrf
                                @foreach($registration->registrableSubjects as $subject)
                                    <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                                        <span style="min-width:140px;">{{ $subject->name }}</span>
                                        <select name="result_statuses[{{ $subject->id }}]" class="form-control input-sm" style="min-width:140px;">
                                            <option value="undefined" @selected(($subject->pivot->result_status ?? 'undefined') === 'undefined')>غير محدد</option>
                                            <option value="passed" @selected(($subject->pivot->result_status ?? 'undefined') === 'passed')>نجاح</option>
                                            <option value="failed" @selected(($subject->pivot->result_status ?? 'undefined') === 'failed')>رسوب</option>
                                        </select>
                                    </div>
                                @endforeach
                                <button type="submit" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">حفظ النتائج</button>
                            </form>
                        </td>
                        <td>
                            <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.registrations.status', [$cycle, $registration]) }}" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">
                                @csrf
                                <select name="status" class="form-control input-sm" style="min-width:140px;">
                                    <option value="under_review" @selected($registration->status === 'under_review')>قيد المراجعة</option>
                                    <option value="accepted" @selected($registration->status === 'accepted')>مقبول</option>
                                    <option value="rejected" @selected($registration->status === 'rejected')>مرفوض</option>
                                </select>
                                <button type="submit" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">حفظ</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if($registrations->isEmpty())
                    <tr>
                        <td colspan="6" class="text-center">لا توجد تسجيلات لهذه الدورة</td>
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
