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
                <i class="voyager-book"></i> {{ $exam->title }}
            </h1>
            <p class="employee-cycle-subtitle">
                {{ $exam->registrableSubject?->name }} — شعبة {{ $exam->classSection?->name }} — {{ $exam->doctor?->full_name }}
            </p>
        </div>
        <a href="{{ route($routeBase . '.exams.index') }}" class="employee-action-btn employee-action-btn--neutral employee-cycle-header-link">العودة للقائمة</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="panel panel-bordered employee-management-panel">
        <div class="panel-body employee-management-form-panel">
            <div class="row">
                <div class="col-md-3"><div class="text-muted">الحالة</div><span class="label label-{{ $examStatusLabels[$exam->status] ?? 'default' }}">{{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}</span></div>
                <div class="col-md-3"><div class="text-muted">الدرجة الكاملة</div><strong>{{ $exam->total_points }}</strong></div>
                <div class="col-md-3"><div class="text-muted">عدد الأسئلة</div><strong>{{ $exam->quizQuestions->count() }}</strong></div>
                <div class="col-md-3"><div class="text-muted">الأسئلة</div><strong>{{ $exam->questions_locked ? 'معتمدة' : 'مسودة' }}</strong></div>
            </div>

            @if($exam->category_ids || $exam->question_types_filter)
                <p class="employee-cycle-subtitle" style="margin-top:16px;">
                    فلاتر التوليد:
                    @if($exam->category_ids) تصنيفات محددة @else كل التصنيفات @endif |
                    @if($exam->question_types_filter)
                        @foreach($exam->question_types_filter as $typeKey)
                            {{ config('exams.question_types')[$typeKey] ?? $typeKey }}@if(!$loop->last)، @endif
                        @endforeach
                    @else
                        كل الأنواع
                    @endif
                </p>
            @endif

            <div class="employee-form-actions">
                @if(!$exam->questions_locked)
                    <form method="POST" action="{{ route($routeBase . '.exams.regenerate', $exam) }}">@csrf<button class="employee-action-btn employee-action-btn--neutral">إعادة التوليد العشوائي</button></form>
                    <form method="POST" action="{{ route($routeBase . '.exams.approve', $exam) }}">@csrf<button class="employee-action-btn employee-action-btn--primary">اعتماد الأسئلة</button></form>
                @endif
                <form method="POST" action="{{ route($routeBase . '.exams.sync_choices', $exam) }}">@csrf<button class="employee-action-btn employee-action-btn--neutral">مزامنة الخيارات من البنك</button></form>
                @if($exam->questions_locked && $exam->status === 'draft')
                    <form method="POST" action="{{ route($routeBase . '.exams.schedule', $exam) }}">@csrf<button class="employee-action-btn employee-action-btn--primary">جدولة الامتحان</button></form>
                @endif
                @if($exam->status !== 'archived')
                    <form method="POST" action="{{ route($routeBase . '.exams.archive', $exam) }}">@csrf<button class="employee-action-btn employee-action-btn--neutral">أرشفة</button></form>
                @endif
            </div>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel">
        <div class="panel-body">
            <h4 class="employee-cycle-section-title">أسئلة الامتحان ({{ $exam->quizQuestions->count() }})</h4>
            @foreach($exam->quizQuestions as $index => $qq)
                <div class="exam-question-preview">
                    <strong>{{ $index + 1 }}. [{{ config('exams.question_types')[$qq->type_snapshot] ?? $qq->type_snapshot }}] ({{ $qq->points }} درجة)</strong>
                    <div style="margin-top:8px;">{{ $qq->question_text_snapshot }}</div>
                    @include('exams.partials.question_image', ['imageUrl' => $qq->imageUrl()])
                    @if($qq->choices->count())
                        <ul>@foreach($qq->choices as $c)<li>{{ $c->is_correct ? '✓' : '○' }} {{ $c->choice_text }}</li>@endforeach</ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>
