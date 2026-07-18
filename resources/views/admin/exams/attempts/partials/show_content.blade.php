@php
    $examPortalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $gradeStatusLabels = [
        'draft' => 'default',
        'auto_corrected' => 'info',
        'pending_review' => 'warning',
        'reviewed' => 'info',
        'approved' => 'primary',
        'published' => 'success',
    ];
    $grade = $attempt->grade;
    $exam = $attempt->exam;
    $student = $attempt->student;
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $examPortalMode }}" data-portal-context="{{ $examPortalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-eye"></i> إجابات الطالب
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $student?->first_name }} {{ $student?->last_name }} —
                {{ $exam?->title }} ({{ $exam?->registrableSubject?->name }})
            </p>
        </div>
        <div class="employee-inline-form">
            <a href="{{ route($routeBase . '.exam_grades.index') }}" class="employee-action-btn employee-action-btn--neutral">العودة للدرجات</a>
            @if($exam)
                <a href="{{ route($routeBase . '.exams.show', $exam) }}" class="employee-action-btn employee-action-btn--neutral">عرض الامتحان</a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body employee-management-form-panel">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-muted">الطالب</div>
                    <strong>{{ $student?->first_name }} {{ $student?->last_name }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">الامتحان</div>
                    <strong>{{ $exam?->title ?? '—' }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">وقت التسليم</div>
                    <strong>{{ $attempt->submitted_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">الدرجة</div>
                    @if($grade)
                        <strong>{{ $grade->raw_score }} / {{ $grade->max_score }}</strong>
                        <span class="label label-{{ $gradeStatusLabels[$grade->status] ?? 'default' }}" style="margin-inline-start:6px;">
                            {{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}
                        </span>
                    @else
                        <strong>—</strong>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title">تفاصيل الإجابات ({{ $attempt->answers->count() }} سؤال)</h4>
            @include('exams.partials.attempt_answers', ['attempt' => $attempt, 'canGradeEssays' => false])
        </div>
    </div>
</div>
