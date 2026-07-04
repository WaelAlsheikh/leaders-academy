<div class="page-content container-fluid">
    <div class="employee-management-panel doctor-portal-panel">
        <h3>إعدادات الامتحانات</h3>
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        <form method="POST" action="{{ route($routeBase . '.exam_settings.update') }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>طريقة إنشاء الامتحانات</label>
                <select name="creation_mode" class="form-control">
                    <option value="random" @selected($settings->creation_mode === 'random')>عشوائي (Admin فقط)</option>
                    <option value="manual" @selected($settings->creation_mode === 'manual')>يدوي (الدكتور يختار الأسئلة)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">حفظ</button>
        </form>
    </div>
</div>
