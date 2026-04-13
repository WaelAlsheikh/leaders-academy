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

    @php
        $resolveCollegeImage = function ($college) {
            $path = $college->image;
            $version = optional($college->updated_at)->timestamp;

            if (blank($path)) {
                return asset('assets/images/placeholder.png');
            }

            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://', '/'])) {
                return $path . ($version ? (str_contains($path, '?') ? '&' : '?') . 'v=' . $version : '');
            }

            return asset('storage/' . ltrim($path, '/')) . ($version ? '?v=' . $version : '');
        };
    @endphp

    <div class="panel panel-bordered employee-management-panel employee-management-form-panel">
        <h4 class="employee-management-form-title">إضافة كلية جديدة</h4>
        <form method="POST" action="{{ route($routeBase . '.colleges.store') }}" enctype="multipart/form-data">
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
                <div class="col-md-4">
                    <label>صورة الكلية</label>
                    <input name="image" type="file" accept="image/*" class="form-control employee-file-input">
                    <p class="employee-file-help">يمكن رفع صورة جديدة بصيغ `jpg`, `png`, `webp`, `gif`.</p>
                </div>
                <div class="col-md-12 employee-form-field-wide">
                    <label>وصف تفصيلي</label>
                    <textarea name="long_description" rows="3" class="form-control employee-rich-text-source"></textarea>
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
                            <div class="employee-college-card-summary">
                                <div class="employee-college-thumb">
                                    <img src="{{ $resolveCollegeImage($college) }}" alt="{{ $college->title }}">
                                </div>
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
                            <form method="POST" action="{{ route($routeBase . '.colleges.update', $college) }}" enctype="multipart/form-data">
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
                                    <div class="col-md-4">
                                        <label>صورة الكلية الحالية</label>
                                        <div class="employee-current-image">
                                            <img src="{{ $resolveCollegeImage($college) }}" alt="{{ $college->title }}">
                                        </div>
                                        <input name="image" type="file" accept="image/*" class="form-control employee-file-input">
                                        <p class="employee-file-help">اترك الحقل فارغًا إذا كنت لا تريد تغيير الصورة الحالية.</p>
                                    </div>
                                    <div class="col-md-12 employee-form-field-wide">
                                        <label>وصف تفصيلي</label>
                                        <textarea name="long_description" rows="3" class="form-control employee-rich-text-source">{{ $college->long_description }}</textarea>
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
@once
    @push('scripts')
        <script>
            (function () {
                const textareaSelector = 'textarea.employee-rich-text-source';
                const toolbarButtons = [
                    { command: 'bold', label: 'ع', title: 'عريض' },
                    { command: 'italic', label: 'م', title: 'مائل' },
                    { command: 'underline', label: 'ت', title: 'تحته خط' },
                    { command: 'insertUnorderedList', label: '• قائمة', title: 'قائمة نقطية' },
                    { command: 'insertOrderedList', label: '1. قائمة', title: 'قائمة رقمية' },
                    { command: 'removeFormat', label: 'مسح', title: 'إزالة التنسيق' }
                ];

                function syncEditor(wrapper) {
                    const editor = wrapper.querySelector('.employee-rich-editor-surface');
                    const textarea = wrapper.querySelector('textarea.employee-rich-text-source');

                    if (!editor || !textarea) {
                        return;
                    }

                    textarea.value = editor.innerHTML.trim();
                }

                function execCommand(wrapper, command, value = null) {
                    const editor = wrapper.querySelector('.employee-rich-editor-surface');

                    if (!editor) {
                        return;
                    }

                    editor.focus();
                    document.execCommand(command, false, value);
                    syncEditor(wrapper);
                }

                function buildToolbar(wrapper) {
                    const toolbar = document.createElement('div');
                    toolbar.className = 'employee-rich-editor-toolbar';

                    const blockSelect = document.createElement('select');
                    blockSelect.className = 'employee-rich-editor-select';
                    [
                        { value: 'P', label: 'فقرة' },
                        { value: 'H2', label: 'عنوان كبير' },
                        { value: 'H3', label: 'عنوان متوسط' }
                    ].forEach(function (optionData) {
                        const option = document.createElement('option');
                        option.value = optionData.value;
                        option.textContent = optionData.label;
                        blockSelect.appendChild(option);
                    });

                    blockSelect.addEventListener('change', function () {
                        execCommand(wrapper, 'formatBlock', blockSelect.value);
                    });

                    toolbar.appendChild(blockSelect);

                    toolbarButtons.forEach(function (buttonData) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'employee-rich-editor-btn';
                        button.textContent = buttonData.label;
                        button.title = buttonData.title;
                        button.addEventListener('click', function () {
                            execCommand(wrapper, buttonData.command);
                        });
                        toolbar.appendChild(button);
                    });

                    const linkButton = document.createElement('button');
                    linkButton.type = 'button';
                    linkButton.className = 'employee-rich-editor-btn';
                    linkButton.textContent = 'رابط';
                    linkButton.title = 'إضافة رابط';
                    linkButton.addEventListener('click', function () {
                        const url = window.prompt('أدخل رابط الصفحة:');
                        if (url) {
                            execCommand(wrapper, 'createLink', url);
                        }
                    });
                    toolbar.appendChild(linkButton);

                    return toolbar;
                }

                function initEditor(textarea) {
                    if (textarea.dataset.richEditorReady === '1') {
                        return;
                    }

                    textarea.dataset.richEditorReady = '1';

                    const wrapper = document.createElement('div');
                    wrapper.className = 'employee-rich-editor';

                    const toolbar = buildToolbar(wrapper);
                    const surface = document.createElement('div');
                    surface.className = 'employee-rich-editor-surface';
                    surface.contentEditable = 'true';
                    surface.innerHTML = textarea.value || '<p></p>';

                    surface.addEventListener('input', function () {
                        syncEditor(wrapper);
                    });

                    surface.addEventListener('blur', function () {
                        syncEditor(wrapper);
                    });

                    textarea.parentNode.insertBefore(wrapper, textarea);
                    wrapper.appendChild(toolbar);
                    wrapper.appendChild(surface);
                    wrapper.appendChild(textarea);
                }

                function initEditors() {
                    document.querySelectorAll(textareaSelector).forEach(function (textarea) {
                        initEditor(textarea);
                    });
                }

                function syncAllEditors() {
                    document.querySelectorAll('.employee-rich-editor').forEach(syncEditor);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initEditors);
                } else {
                    initEditors();
                }

                document.addEventListener('submit', function (event) {
                    if (event.target.closest('.employee-management-panel, .employee-management-card')) {
                        syncAllEditors();
                    }
                });
            })();
        </script>
    @endpush
@endonce
