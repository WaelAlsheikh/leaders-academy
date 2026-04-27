@php
    $portalMode = ($portalContext ?? 'admin') === 'employee' ? 'employee' : 'voyager';
    $entityTypeLabels = [
        'college' => 'الكليات',
        'program_branch' => 'البرامج الجامعية',
        'training_program_branch' => 'البرامج التدريبية',
    ];
@endphp

<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--{{ $portalMode }}" data-portal-context="{{ $portalMode }}">
    <div class="employee-cycle-header">
        <div>
            <h1 class="page-title employee-cycle-title">
                <i class="voyager-calendar"></i> الدورات الفصلية العامة
            </h1>
            <p class="employee-cycle-subtitle">أنشئ دورة عامة واحدة، ثم افتح بداخلها الكليات والبرامج التي تريد إتاحتها للتسجيل.</p>
        </div>

        <a href="{{ route($routeBase . '.archived_enrollment_cycles.index') }}" class="employee-action-btn employee-action-btn--neutral employee-cycle-header-link">
            الدورات المؤرشفة القديمة
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-create-panel">
        <div class="employee-management-form-panel">
            <h4 class="employee-cycle-section-title">إنشاء دورة فصلية عامة جديدة</h4>
            <form method="POST" action="{{ route($routeBase . '.enrollment_cycles.store') }}">
                @csrf
                <div class="row employee-form-grid">
                    <div class="col-md-3">
                        <label>اسم الدورة</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="مثال: ربيع 2026" required>
                    </div>
                    <div class="col-md-2">
                        <label>رمز الدورة</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="مثال: S26">
                    </div>
                    <div class="col-md-3">
                        <label>بداية التسجيل</label>
                        <input type="datetime-local" name="registration_starts_at" class="form-control" value="{{ old('registration_starts_at') }}">
                    </div>
                    <div class="col-md-3">
                        <label>نهاية التسجيل</label>
                        <input type="datetime-local" name="registration_ends_at" class="form-control" value="{{ old('registration_ends_at') }}">
                    </div>
                </div>

                <div style="margin-top:18px;">
                    <label style="display:block;margin-bottom:10px;font-weight:700;">الكيانات المفتوحة داخل الدورة</label>
                    <div class="row">
                        @foreach($entityTypeLabels as $type => $label)
                            <div class="col-md-4" style="margin-bottom:15px;">
                                <div style="border:1px solid #e5edf2;border-radius:12px;padding:14px;background:#fff;">
                                    <div style="font-weight:700;color:#083b59;margin-bottom:10px;">{{ $label }}</div>
                                    @forelse(($registrableEntities[$type] ?? collect()) as $entity)
                                        <label style="display:flex;gap:8px;align-items:flex-start;margin-bottom:8px;">
                                            <input type="checkbox" name="entity_ids[]" value="{{ $entity->id }}" checked>
                                            <span>{{ $entity->display_title }}</span>
                                        </label>
                                    @empty
                                        <div class="text-muted">لا توجد عناصر متاحة.</div>
                                    @endforelse
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="employee-form-actions">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--submit">
                        تأكيد بدء الفصل
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="panel panel-bordered employee-management-panel employee-cycle-table-panel" style="margin-top:20px;">
        <div class="panel-body">
            <div class="employee-cycle-table-wrap">
                <table class="table table-striped employee-cycle-table">
                    <thead>
                        <tr>
                            <th>الدورة</th>
                            <th>الرمز</th>
                            <th>الفترة</th>
                            <th>الحالة</th>
                            <th>الكيانات المفتوحة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($seasons as $season)
                            <tr>
                                <td>{{ $season->name }}</td>
                                <td>{{ $season->code ?: '—' }}</td>
                                <td>
                                    {{ optional($season->registration_starts_at)->format('Y-m-d H:i') ?? '—' }}
                                    -
                                    {{ optional($season->registration_ends_at)->format('Y-m-d H:i') ?? '—' }}
                                </td>
                                <td>
                                    <span class="employee-cycle-status employee-cycle-status--{{ \Illuminate\Support\Str::slug($season->status, '-') }}">
                                        {{ $season->status === 'open' ? 'مفتوحة' : 'مغلقة' }}
                                    </span>
                                </td>
                                <td>{{ $season->enabled_enrollment_cycles_count }}</td>
                                <td>
                                    <div class="employee-cycle-actions">
                                        <a href="{{ route($routeBase . '.registration_seasons.show', $season) }}" class="employee-action-btn employee-action-btn--primary employee-action-btn--sm">
                                            إدارة الدورة
                                        </a>
                                        <form method="POST" action="{{ route($routeBase . '.registration_seasons.archive', $season) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد أرشفة هذه الدورة العامة؟ ستنتقل إلى صفحة الدورات المؤرشفة مع الاحتفاظ بكل بياناتها.');">
                                            @csrf
                                            <button type="submit" class="employee-action-btn employee-action-btn--warning employee-action-btn--sm">
                                                أرشفة
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">لا توجد دورات فصلية عامة بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
