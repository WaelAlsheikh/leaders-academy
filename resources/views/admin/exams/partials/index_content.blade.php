@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $statusClasses = [
        'draft' => 'default',
        'scheduled' => 'info',
        'running' => 'success',
        'finished' => 'warning',
        'archived' => 'default',
    ];
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-book"></i> إدارة الامتحانات
            </h1>
            <p class="employee-cycle-subtitle">إنشاء وجدولة الامتحانات العشوائية ومتابعة حالتها ودرجات الطلاب.</p>
        </div>
        <div class="employee-inline-form">
            <a href="{{ route($routeBase . '.exam_settings.edit') }}" class="employee-action-btn employee-action-btn--neutral">إعدادات الامتحانات</a>
            <a href="{{ route($routeBase . '.exams.create') }}" class="employee-action-btn employee-action-btn--primary">إنشاء امتحان عشوائي</a>
            <a href="{{ route($routeBase . '.exam_question_bank.index') }}" class="employee-action-btn employee-action-btn--neutral">بنك الأسئلة</a>
            <a href="{{ route($routeBase . '.exam_grades.index') }}" class="employee-action-btn employee-action-btn--neutral">الدرجات</a>
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
                            <th>العنوان</th>
                            <th>المادة</th>
                            <th>الشعبة</th>
                            <th>الدكتور</th>
                            <th>الأسئلة</th>
                            <th>الحالة</th>
                            <th>الموعد</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($exams as $exam)
                        <tr>
                            <td><strong>{{ $exam->title }}</strong></td>
                            <td>{{ $exam->registrableSubject?->name ?? '—' }}</td>
                            <td>{{ $exam->classSection?->name ?? '—' }}</td>
                            <td>{{ $exam->doctor?->full_name ?? '—' }}</td>
                            <td>{{ $exam->quizQuestions->count() }} / {{ $exam->question_count }}</td>
                            <td>
                                <span class="label label-{{ $statusClasses[$exam->status] ?? 'default' }}">
                                    {{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}
                                </span>
                            </td>
                            <td>{{ $exam->starts_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>
                                <a href="{{ route($routeBase . '.exams.show', $exam) }}" class="employee-action-btn employee-action-btn--neutral">عرض</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center">لا توجد امتحانات.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;">
                {{ $exams->links() }}
            </div>
        </div>
    </div>
</div>
