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
                <i class="voyager-settings"></i> إعدادات الامتحانات
            </h1>
            <p class="employee-cycle-subtitle">تحديد طريقة إنشاء الامتحانات الافتراضية للنظام.</p>
        </div>
        <a href="{{ route($routeBase . '.exams.index') }}" class="employee-action-btn employee-action-btn--neutral employee-cycle-header-link">العودة للامتحانات</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="panel panel-bordered employee-management-panel employee-cycle-create-panel">
        <div class="panel-body employee-management-form-panel">
            <form method="POST" action="{{ route($routeBase . '.exam_settings.update') }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label>طريقة إنشاء الامتحانات</label>
                    <select name="creation_mode" class="form-control">
                        <option value="random" @selected($settings->creation_mode === 'random')>عشوائي (Admin فقط)</option>
                        <option value="manual" @selected($settings->creation_mode === 'manual')>يدوي (الدكتور يختار الأسئلة)</option>
                    </select>
                    <small class="text-muted" style="display:block;margin-top:8px;">في الوضع العشوائي يُخفى إنشاء الامتحان من لوحة الدكتور.</small>
                </div>
                <div class="employee-form-actions">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--submit">حفظ الإعدادات</button>
                </div>
            </form>
        </div>
    </div>
</div>
