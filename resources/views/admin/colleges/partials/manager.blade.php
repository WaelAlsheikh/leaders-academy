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

    <div class="panel panel-bordered employee-management-panel employee-management-form-panel">
        <h4 class="employee-management-form-title">إضافة كلية جديدة</h4>
        <form method="POST" action="{{ route($routeBase . '.colleges.store') }}">
            @csrf
            <div class="row employee-form-grid">
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
                <div class="col-md-12 employee-form-field-wide">
                    <label>وصف تفصيلي</label>
                    <textarea name="long_description" rows="3" class="form-control employee-rich-text"></textarea>
                </div>
            </div>
            <button class="btn employee-action-btn employee-action-btn--primary employee-action-btn--submit">إضافة الكلية</button>
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
                                <a href="{{ route($routeBase . '.colleges.subjects', $college) }}" class="btn employee-action-btn employee-action-btn--primary">
                                    إدارة المواد
                                </a>
                                <button type="button" class="btn employee-action-btn employee-action-btn--edit" data-toggle="collapse" data-target="#edit-college-{{ $college->id }}">
                                    تعديل
                                </button>
                                <form method="POST" action="{{ route($routeBase . '.colleges.destroy', $college) }}" class="employee-inline-form" onsubmit="return confirm('هل تريد حذف هذه الكلية؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn employee-action-btn employee-action-btn--danger">حذف</button>
                                </form>
                            </div>
                        </div>

                        <div id="edit-college-{{ $college->id }}" class="collapse employee-collapse-form">
                            <form method="POST" action="{{ route($routeBase . '.colleges.update', $college) }}">
                                @csrf
                                @method('PUT')
                                <div class="row employee-form-grid">
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
                                    <div class="col-md-12 employee-form-field-wide">
                                        <label>وصف تفصيلي</label>
                                        <textarea name="long_description" rows="3" class="form-control employee-rich-text">{{ $college->long_description }}</textarea>
                                    </div>
                                    <div class="col-md-12 employee-form-actions">
                                        <button class="btn employee-action-btn employee-action-btn--success">حفظ التعديلات</button>
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

@if($portalContext === 'employee')
    @once
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.6/tinymce.min.js" referrerpolicy="origin"></script>
            <script>
                (function () {
                    const selector = 'textarea.employee-rich-text';
                    let editorCounter = 0;

                    function ensureEditorId(element) {
                        if (!element.id) {
                            editorCounter += 1;
                            element.id = `employee-rich-text-${editorCounter}`;
                        }

                        return element.id;
                    }

                    function initEditorFor(element) {
                        if (typeof tinymce === 'undefined') {
                            return;
                        }

                        const editorId = ensureEditorId(element);

                        if (tinymce.get(editorId)) {
                            return;
                        }

                        tinymce.init({
                            selector: `#${editorId}`,
                            menubar: false,
                            branding: false,
                            promotion: false,
                            directionality: 'rtl',
                            height: 260,
                            plugins: 'lists link table code autoresize',
                            toolbar: 'undo redo | blocks | bold italic underline | forecolor backcolor | alignright aligncenter alignleft | bullist numlist | link table | removeformat code',
                            content_style: "body { font-family: Tajawal, sans-serif; font-size: 16px; line-height: 1.9; direction: rtl; text-align: right; } p { margin: 0 0 12px; }",
                            setup: function (editor) {
                                editor.on('change keyup undo redo', function () {
                                    editor.save();
                                });
                            }
                        });
                    }

                    function initVisibleEditors() {
                        document.querySelectorAll(selector).forEach(function (textarea) {
                            if (textarea.offsetParent !== null) {
                                initEditorFor(textarea);
                            }
                        });
                    }

                    function initEmployeeRichText() {
                        initVisibleEditors();
                    }

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', initEmployeeRichText);
                    } else {
                        initEmployeeRichText();
                    }

                    document.addEventListener('shown.bs.collapse', function (event) {
                        event.target.querySelectorAll(selector).forEach(function (textarea) {
                            initEditorFor(textarea);
                        });
                    });
                })();
            </script>
        @endpush
    @endonce
@endif
