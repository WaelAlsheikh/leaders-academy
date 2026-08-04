@php
    $examPortalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $examStatusLabels = [
        'draft' => 'default',
        'scheduled' => 'info',
        'running' => 'success',
        'finished' => 'warning',
        'archived' => 'default',
    ];
    $gradeStatusLabels = [
        'draft' => 'default',
        'auto_corrected' => 'info',
        'pending_review' => 'warning',
        'reviewed' => 'info',
        'approved' => 'primary',
        'published' => 'success',
    ];
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $examPortalMode }}" data-portal-context="{{ $examPortalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-medal"></i> درجات الامتحانات
            </h1>
            <p class="employee-cycle-subtitle">مراجعة درجات الطلاب، اعتماد النتائج، ونشرها في بوابة الطالب.</p>
        </div>
        <div class="employee-inline-form">
            <a href="{{ route($routeBase . '.exams.index') }}" class="employee-action-btn employee-action-btn--neutral">الامتحانات</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>الطالب</th>
                            <th>الامتحان</th>
                            <th>المادة</th>
                            <th>الدرجة</th>
                            <th>النسبة</th>
                            <th>النتيجة</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($grades as $grade)
                        <tr>
                            <td><strong>{{ $grade->student?->first_name }} {{ $grade->student?->last_name }}</strong></td>
                            <td>{{ $grade->exam?->title ?? '—' }}</td>
                            <td>{{ $grade->exam?->registrableSubject?->name ?? '—' }}</td>
                            <td><strong>{{ $grade->raw_score }}</strong> / {{ $grade->max_score }}</td>
                            <td>{{ number_format((float) $grade->percentage, 1) }}%</td>
                            <td>
                                <span class="label label-{{ $grade->isPassed() ? 'success' : 'danger' }}">
                                    {{ $grade->resultLabel() }}
                                </span>
                            </td>
                            <td>
                                <span class="label label-{{ $gradeStatusLabels[$grade->status] ?? 'default' }}">
                                    {{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}
                                </span>
                            </td>
                            <td>
                                @if($grade->status !== 'published')
                                    <form method="POST" action="{{ route($routeBase . '.exam_grades.approve', $grade) }}" style="display:inline">@csrf<button class="employee-action-btn employee-action-btn--neutral">اعتماد</button></form>
                                    <form method="POST" action="{{ route($routeBase . '.exam_grades.publish', $grade) }}" style="display:inline">@csrf<button class="employee-action-btn employee-action-btn--primary">نشر</button></form>
                                @else
                                    <span class="text-muted">منشورة</span>
                                @endif
                                @if($grade->attempt_id)
                                    <a href="{{ route($routeBase . '.exam_attempts.show', $grade->attempt_id) }}" class="employee-action-btn employee-action-btn--neutral" style="margin-inline-start:6px;">عرض الإجابات</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">لا توجد درجات بعد.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;">
                {{ $grades->links() }}
            </div>
        </div>
    </div>
</div>
