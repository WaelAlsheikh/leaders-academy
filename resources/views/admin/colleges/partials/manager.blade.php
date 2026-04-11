<div class="{{ $portalContext === 'employee' ? '' : 'container-fluid' }}">
    <div class="employee-management-header" id="employee-colleges-anchor">
        <div>
            <h1 class="page-title">
                <i class="voyager-university"></i> إدارة الكليات
            </h1>
            <p class="doctor-portal-meta">إضافة الكليات وتحديث بياناتها مع متابعة المواد التابعة لكل كلية.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="panel panel-bordered employee-management-panel" style="padding:15px;">
        <h4>إضافة كلية جديدة</h4>
        <form method="POST" action="{{ route($routeBase . '.colleges.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <label>اسم الكلية</label>
                    <input name="title" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label>وصف مختصر</label>
                    <input name="short_description" class="form-control">
                </div>
                <div class="col-md-2">
                    <label>سعر الساعة المعتمدة</label>
                    <input name="price_per_credit_hour" type="number" min="0" step="0.01" class="form-control" value="0">
                </div>
                <div class="col-md-12" style="margin-top:12px;">
                    <label>وصف تفصيلي</label>
                    <textarea name="long_description" rows="3" class="form-control"></textarea>
                </div>
            </div>
            <button class="btn btn-primary" style="margin-top:12px;">إضافة الكلية</button>
        </form>
    </div>

    <div class="row" id="employee-subjects-anchor">
        @forelse($colleges as $college)
            <div class="col-md-6">
                <div class="panel panel-bordered employee-management-card">
                    <div class="panel-body">
                        <div class="employee-college-card-head">
                            <div>
                                <h4>{{ $college->title }}</h4>
                                <div style="margin:8px 0;color:#555;">
                                    المواد: <strong>{{ $college->subjects_count }}</strong>
                                    — سعر الساعة: <strong>${{ number_format((float) $college->price_per_credit_hour, 2) }}</strong>
                                </div>
                                @if($college->short_description)
                                    <p class="doctor-portal-meta" style="margin-bottom:0;">{{ $college->short_description }}</p>
                                @endif
                            </div>
                            <div class="employee-college-card-actions">
                                <a href="{{ route($routeBase . '.colleges.subjects', $college) }}" class="btn btn-primary">
                                    إدارة المواد
                                </a>
                                <button type="button" class="btn btn-info" data-toggle="collapse" data-target="#edit-college-{{ $college->id }}">
                                    تعديل
                                </button>
                                <form method="POST" action="{{ route($routeBase . '.colleges.destroy', $college) }}" onsubmit="return confirm('هل تريد حذف هذه الكلية؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger">حذف</button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-college-{{ $college->id }}" class="collapse employee-collapse-form">
                            <form method="POST" action="{{ route($routeBase . '.colleges.update', $college) }}">
                                @csrf
                                @method('PUT')
                                <div class="row">
                                    <div class="col-md-4">
                                        <label>اسم الكلية</label>
                                        <input name="title" class="form-control" value="{{ $college->title }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label>وصف مختصر</label>
                                        <input name="short_description" class="form-control" value="{{ $college->short_description }}">
                                    </div>
                                    <div class="col-md-2">
                                        <label>سعر الساعة المعتمدة</label>
                                        <input name="price_per_credit_hour" type="number" min="0" step="0.01" class="form-control" value="{{ $college->price_per_credit_hour }}">
                                    </div>
                                    <div class="col-md-12" style="margin-top:12px;">
                                        <label>وصف تفصيلي</label>
                                        <textarea name="long_description" rows="3" class="form-control">{{ $college->long_description }}</textarea>
                                    </div>
                                    <div class="col-md-2" style="margin-top:20px;">
                                        <button class="btn btn-success">حفظ التعديلات</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="doctor-portal-empty">لا توجد كليات حالياً.</div>
            </div>
        @endforelse
    </div>
</div>
