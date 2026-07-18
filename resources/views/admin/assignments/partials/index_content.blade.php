@php
    $examPortalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $examPortalMode }}" data-portal-context="{{ $examPortalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-folder"></i> الوظائف
            </h1>
            <p class="employee-cycle-subtitle">تصفح وظائف الأساتذة وتسليمات الطلاب.</p>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body employee-management-form-panel">
            <form method="GET" action="{{ route($routeBase . '.assignments.index') }}" class="row" style="display:flex;flex-wrap:wrap;gap:16px;align-items:flex-end;">
                <div class="form-group" style="min-width:200px;flex:1;">
                    <label>الدكتور</label>
                    <select name="doctor_id" class="form-control">
                        <option value="">— الكل —</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected((string) $doctorId === (string) $doctor->id)>{{ $doctor->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="min-width:200px;flex:1;">
                    <label>المادة</label>
                    <select name="registrable_subject_id" class="form-control">
                        <option value="">— الكل —</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected((string) $subjectId === (string) $subject->id)>{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="employee-form-actions" style="margin:0;">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary">تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>العنوان</th>
                            <th>المادة</th>
                            <th>الشعبة</th>
                            <th>الدكتور</th>
                            <th>الموعد</th>
                            <th>النافذة</th>
                            <th>التسليمات</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($assignments as $assignment)
                        <tr>
                            <td><strong>{{ $assignment->title }}</strong></td>
                            <td>{{ $assignment->registrableSubject?->name }}</td>
                            <td>{{ $assignment->classSection?->name }}</td>
                            <td>{{ $assignment->doctor?->full_name }}</td>
                            <td>
                                {{ $assignment->starts_at?->format('Y-m-d H:i') }}
                                <br>
                                {{ $assignment->ends_at?->format('Y-m-d H:i') }}
                            </td>
                            <td>{{ $assignment->windowStatusLabel() }}</td>
                            <td>{{ $assignment->submissions_count }}</td>
                            <td>
                                <a href="{{ route($routeBase . '.assignments.show', $assignment) }}" class="employee-action-btn employee-action-btn--neutral">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">لا توجد وظائف.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div style="margin-top:16px;">{{ $assignments->links() }}</div>
        </div>
    </div>
</div>
