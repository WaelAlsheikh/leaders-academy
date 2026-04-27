@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-management-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-people"></i> طلبات التسجيل
            </h1>
            <p class="employee-cycle-subtitle">{{ $entityLabel }}: <strong>{{ $entity->display_title }}</strong></p>
        </div>
        <a href="{{ route($routeBase . '.registrables.years', $entity) }}" class="employee-action-btn employee-action-btn--neutral">
            العودة إلى إدارة السنوات
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-management-form-panel">
        <form method="GET" action="{{ route($routeBase . '.registrables.registrations.index', $entity) }}">
            <div class="row employee-form-grid">
                <div class="col-md-3">
                    <label>الدورة العامة</label>
                    <select name="season_id" class="form-control">
                        <option value="">الكل</option>
                        @foreach($seasonOptions as $seasonOption)
                            <option value="{{ $seasonOption->id }}" @selected((string) $seasonId === (string) $seasonOption->id)>
                                {{ $seasonOption->name }} @if($seasonOption->code) ({{ $seasonOption->code }}) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">الكل</option>
                        <option value="under_review" @selected($status === 'under_review')>قيد المراجعة</option>
                        <option value="accepted" @selected($status === 'accepted')>مقبول</option>
                        <option value="rejected" @selected($status === 'rejected')>مرفوض</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>بحث عن طالب</label>
                    <input type="text" name="student" class="form-control" value="{{ $studentSearch }}" placeholder="الاسم أو البريد أو اسم المستخدم">
                </div>
                <div class="col-md-3 employee-form-actions">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">تطبيق</button>
                    <a href="{{ route($routeBase . '.registrables.registrations.index', $entity) }}" class="employee-action-btn employee-action-btn--neutral employee-action-btn--sm">إزالة</a>
                </div>
            </div>
        </form>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>الدورة العامة</th>
                            <th>الطالب</th>
                            <th>نوع الحالة</th>
                            <th>آخر دورة/فصل سابق</th>
                            <th>حالة الطلب</th>
                            <th>المواد</th>
                            <th>نتائج المواد</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $registration)
                            @php
                                $summary = $progressSummaries[$registration->id] ?? null;
                                $statusLabel = [
                                    'under_review' => 'قيد المراجعة',
                                    'accepted' => 'مقبول',
                                    'rejected' => 'مرفوض',
                                ][$registration->status] ?? $registration->status;
                            @endphp
                            <tr>
                                <td>
                                    {{ $registration->enrollmentCycle?->registrationSeason?->name ?? '—' }}
                                    @if($registration->enrollmentCycle?->registrationSeason?->code)
                                        <div class="text-muted" style="font-size:12px;">{{ $registration->enrollmentCycle->registrationSeason->code }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $registration->student?->first_name }} {{ $registration->student?->last_name }}
                                    <div class="text-muted" style="font-size:12px;">
                                        {{ $registration->student?->username }} — {{ $registration->student?->email }}
                                    </div>
                                </td>
                                <td>{{ $summary['registration_type_label'] ?? '—' }}</td>
                                <td>
                                    <div>{{ $summary['last_accepted_season_name'] ?? 'لا يوجد' }}</div>
                                    <div class="text-muted" style="font-size:12px;">{{ $summary['last_study_term_label'] ?? 'لا يوجد فصل سابق' }}</div>
                                </td>
                                <td>{{ $statusLabel }}</td>
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
                                <td>
                                    <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.registrations.results', [$registration->enrollmentCycle, $registration]) }}">
                                        @csrf
                                        @foreach($registration->registrableSubjects as $subject)
                                            <div style="display:flex;gap:8px;align-items:center;margin-bottom:8px;">
                                                <span style="min-width:120px;">{{ $subject->name }}</span>
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
                                    <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.registrations.status', [$registration->enrollmentCycle, $registration]) }}">
                                        @csrf
                                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                                            <select name="status" class="form-control input-sm" style="min-width:140px;">
                                                <option value="under_review" @selected($registration->status === 'under_review')>قيد المراجعة</option>
                                                <option value="accepted" @selected($registration->status === 'accepted')>مقبول</option>
                                                <option value="rejected" @selected($registration->status === 'rejected')>مرفوض</option>
                                            </select>
                                            <button type="submit" class="employee-action-btn employee-action-btn--success employee-action-btn--sm">حفظ</button>
                                            <a href="{{ route($routeBase . '.enrollment_cycles.show', $registration->enrollmentCycle) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                                عرض التفاصيل
                                            </a>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد طلبات تسجيل مطابقة للفلاتر الحالية.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
