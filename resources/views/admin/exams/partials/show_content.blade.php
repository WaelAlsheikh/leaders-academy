<div class="page-content container-fluid">
    <div class="employee-management-panel doctor-portal-panel">
        <h3>{{ $exam->title }}</h3>
        <p>{{ $exam->registrableSubject?->name }} — شعبة {{ $exam->classSection?->name }} — {{ $exam->doctor?->full_name }}</p>
        <p>الحالة: <strong>{{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}</strong>
            | الدرجة الكاملة: {{ $exam->total_points }}
            | {{ $exam->questions_locked ? 'الأسئلة معتمدة' : 'مسودة أسئلة' }}</p>
        @if($exam->category_ids || $exam->question_types_filter)
            <p class="doctor-portal-meta">
                فلاتر التوليد:
                @if($exam->category_ids) تصنيفات محددة @else كل التصنيفات @endif
                |
                @if($exam->question_types_filter)
                    @foreach($exam->question_types_filter as $typeKey)
                        {{ config('exams.question_types')[$typeKey] ?? $typeKey }}@if(!$loop->last)، @endif
                    @endforeach
                @else
                    كل الأنواع
                @endif
            </p>
        @endif

        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

        <div class="doctor-live-actions">
            @if(!$exam->questions_locked)
                <form method="POST" action="{{ route($routeBase . '.exams.regenerate', $exam) }}">@csrf<button class="btn btn-secondary">إعادة التوليد العشوائي</button></form>
                <form method="POST" action="{{ route($routeBase . '.exams.approve', $exam) }}">@csrf<button class="btn btn-primary">اعتماد الأسئلة</button></form>
            @endif
            <form method="POST" action="{{ route($routeBase . '.exams.sync_choices', $exam) }}">@csrf<button class="btn btn-secondary">مزامنة الخيارات من البنك</button></form>
            @if($exam->questions_locked && $exam->status === 'draft')
                <form method="POST" action="{{ route($routeBase . '.exams.schedule', $exam) }}">@csrf<button class="btn btn-primary">جدولة الامتحان</button></form>
            @endif
            @if($exam->status !== 'archived')
                <form method="POST" action="{{ route($routeBase . '.exams.archive', $exam) }}">@csrf<button class="btn btn-secondary">أرشفة</button></form>
            @endif
        </div>

        <h4>أسئلة الامتحان ({{ $exam->quizQuestions->count() }})</h4>
        @foreach($exam->quizQuestions as $index => $qq)
            <div class="live-session-comment" style="margin-bottom:12px;">
                <strong>{{ $index + 1 }}. [{{ config('exams.question_types')[$qq->type_snapshot] ?? $qq->type_snapshot }}] ({{ $qq->points }} درجة)</strong>
                <div>{{ $qq->question_text_snapshot }}</div>
                @if($qq->choices->count())
                    <ul>@foreach($qq->choices as $c)<li>{{ $c->is_correct ? '✓' : '○' }} {{ $c->choice_text }}</li>@endforeach</ul>
                @endif
            </div>
        @endforeach
    </div>
</div>
