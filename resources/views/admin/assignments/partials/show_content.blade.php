@php
    $examPortalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $examPortalMode }}" data-portal-context="{{ $examPortalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-folder"></i> {{ $assignment->title }}
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $assignment->registrableSubject?->name }} — شعبة {{ $assignment->classSection?->name }} — {{ $assignment->doctor?->full_name }}
            </p>
        </div>
        <a href="{{ route($routeBase . '.assignments.index') }}" class="employee-action-btn employee-action-btn--neutral employee-cycle-header-link">العودة للقائمة</a>
    </div>

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body employee-management-form-panel">
            <div class="row">
                <div class="col-md-3"><div class="text-muted">يبدأ</div><strong>{{ $assignment->starts_at?->format('Y-m-d H:i') }}</strong></div>
                <div class="col-md-3"><div class="text-muted">ينتهي</div><strong>{{ $assignment->ends_at?->format('Y-m-d H:i') }}</strong></div>
                <div class="col-md-3"><div class="text-muted">النافذة</div><strong>{{ $assignment->windowStatusLabel() }}</strong></div>
                <div class="col-md-3"><div class="text-muted">الحالة</div><strong>{{ $assignment->statusLabel() }}</strong></div>
            </div>
            @if($assignment->description)
                <p style="margin-top:16px;line-height:1.8;">{{ $assignment->description }}</p>
            @endif
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title">تسليمات الطلاب</h4>
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>الحالة</th>
                            <th>الملفات</th>
                            <th>ملاحظات الدكتور</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($students as $student)
                        @php $submission = $submissions->get($student->id); @endphp
                        <tr>
                            <td><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></td>
                            <td>
                                @if($submission && $submission->files->count())
                                    <span class="label label-success">تم التسليم</span>
                                    <div class="text-muted" style="margin-top:4px;">{{ $submission->submitted_at?->format('Y-m-d H:i') }}</div>
                                @else
                                    <span class="label label-default">لم يرفع</span>
                                @endif
                            </td>
                            <td>
                                @if($submission && $submission->files->count())
                                    <ul style="margin:0;padding-right:18px;">
                                        @foreach($submission->files as $file)
                                            <li style="margin-bottom:8px;">
                                                {{ $file->original_name }} ({{ $file->humanSize() }})
                                                <div style="margin-top:4px;">
                                                    @if($file->isPreviewableInline())
                                                        <a class="employee-action-btn employee-action-btn--neutral" target="_blank" href="{{ route($routeBase . '.assignment_files.download', $file) }}">معاينة</a>
                                                    @endif
                                                    <a class="employee-action-btn employee-action-btn--neutral" href="{{ route($routeBase . '.assignment_files.download', $file) }}?download=1">تحميل</a>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="max-width:280px;white-space:pre-wrap;">{{ $submission?->doctor_notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">لا يوجد طلاب في الشعبة.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
