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
            <p class="employee-cycle-subtitle">طريقة إنشاء الامتحانات ونسبة النجاح الافتراضية.</p>
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
                <div class="form-group" style="margin-top:16px;">
                    <label>نسبة النجاح (%) من 100</label>
                    <input type="number" name="pass_percentage" class="form-control" min="0" max="100" step="1"
                           value="{{ old('pass_percentage', $settings->pass_percentage ?? 60) }}" required>
                    <small class="text-muted" style="display:block;margin-top:8px;">
                        الطالب ناجح إذا كانت نسبته أكبر من أو تساوي هذه القيمة (الافتراضي 60). يظهر للطالب فور التسليم.
                    </small>
                    @error('pass_percentage')<div class="text-danger" style="margin-top:6px;">{{ $message }}</div>@enderror
                </div>
                <div class="employee-form-actions">
                    <button type="submit" class="employee-action-btn employee-action-btn--primary employee-action-btn--submit">حفظ الإعدادات</button>
                </div>
            </form>
        </div>
    </div>
</div>
