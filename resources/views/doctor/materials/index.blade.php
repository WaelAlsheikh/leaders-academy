@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')

@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')

    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>ملفات الجلسات</h3>
                    <p class="doctor-portal-meta">اختر مادة من موادك الحالية ثم أدر الفيديوهات والملفات الخاصة بها.</p>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="GET" action="{{ route('doctor.materials.index') }}" class="doctor-next-link-form" style="margin-top:18px;">
                <label>المادة</label>
                <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end;">
                    <select name="subject" class="form-control" style="max-width:420px;">
                        <option value="">اختر مادة</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected($selectedSubject?->id === $subject->id)>
                                {{ $subject->name }} - {{ $subject->registrableEntity?->display_title ?? '—' }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">عرض المحتوى</button>
                </div>
            </form>

            @if($subjects->isEmpty())
                <div class="doctor-portal-empty doctor-portal-empty-inline" style="margin-top:20px;">
                    لا توجد لديك مواد فعالة مسندة إليك حاليًا.
                </div>
            @endif
        </section>

        @if($selectedSubject)
            <section class="doctor-portal-panel">
                <div class="doctor-portal-panel-head">
                    <div>
                        <h3>فيديوهات الجلسات</h3>
                        <p class="doctor-portal-meta">{{ $selectedSubject->name }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('doctor.materials.videos.store') }}" enctype="multipart/form-data" class="doctor-next-link-form">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    <div class="doctor-section-info-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                        <div>
                            <label>العنوان</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div>
                            <label>الترتيب</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                        <div>
                            <label>ملف الفيديو</label>
                            <input type="file" name="upload" class="form-control" accept=".mp4,.mov,.webm,.m4v" required>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <label>وصف مختصر</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div style="margin-top:12px;">
                        <label style="display:flex;gap:8px;align-items:center;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            مفعل للطلاب
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;">رفع فيديو</button>
                </form>

                <div style="margin-top:20px;">
                    @forelse($videos as $material)
                        <div class="doctor-portal-panel" style="background:#fff;border:1px solid #e5e7eb;margin-bottom:16px;">
                            <div class="doctor-portal-panel-head">
                                <div>
                                    <h3 style="font-size:18px;">{{ $material->title }}</h3>
                                    <p class="doctor-portal-meta">
                                        {{ $material->original_name }}
                                        - {{ number_format($material->file_size / 1024 / 1024, 2) }} MB
                                        - {{ $material->is_active ? 'مفعل' : 'مخفي' }}
                                    </p>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a href="{{ route('doctor.materials.download', $material) }}" class="btn btn-default" target="_blank">عرض</a>
                                    <a href="{{ route('doctor.materials.download', ['material' => $material, 'download' => 1]) }}" class="btn btn-default">تنزيل</a>
                                </div>
                            </div>

                            <video controls style="width:100%;max-height:360px;background:#111;border-radius:10px;margin-bottom:14px;">
                                <source src="{{ route('doctor.materials.download', $material) }}" type="{{ $material->mime_type }}">
                            </video>

                            <form method="POST" action="{{ route('doctor.materials.update', $material) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="doctor-section-info-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                                    <div>
                                        <label>العنوان</label>
                                        <input type="text" name="title" class="form-control" value="{{ $material->title }}" required>
                                    </div>
                                    <div>
                                        <label>الترتيب</label>
                                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ $material->sort_order }}">
                                    </div>
                                    <div>
                                        <label>استبدال الفيديو</label>
                                        <input type="file" name="upload" class="form-control" accept=".mp4,.mov,.webm,.m4v">
                                    </div>
                                </div>
                                <div style="margin-top:12px;">
                                    <label>الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ $material->description }}</textarea>
                                </div>
                                <div style="margin-top:12px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                                    <label style="display:flex;gap:8px;align-items:center;">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($material->is_active)>
                                        مفعل للطلاب
                                    </label>
                                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('doctor.materials.destroy', $material) }}" onsubmit="return confirm('هل تريد حذف هذا الفيديو؟');" style="margin-top:12px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف الفيديو</button>
                            </form>
                        </div>
                    @empty
                        <div class="doctor-portal-empty doctor-portal-empty-inline">لا توجد فيديوهات مرفوعة لهذه المادة بعد.</div>
                    @endforelse
                </div>
            </section>

            <section class="doctor-portal-panel">
                <div class="doctor-portal-panel-head">
                    <div>
                        <h3>ملفات الجلسات</h3>
                        <p class="doctor-portal-meta">{{ $selectedSubject->name }}</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('doctor.materials.files.store') }}" enctype="multipart/form-data" class="doctor-next-link-form">
                    @csrf
                    <input type="hidden" name="subject_id" value="{{ $selectedSubject->id }}">
                    <div class="doctor-section-info-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                        <div>
                            <label>العنوان</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div>
                            <label>الترتيب</label>
                            <input type="number" name="sort_order" class="form-control" min="0" value="0">
                        </div>
                        <div>
                            <label>الملف</label>
                            <input type="file" name="upload" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png,.webp" required>
                        </div>
                    </div>
                    <div style="margin-top:12px;">
                        <label>وصف مختصر</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div style="margin-top:12px;">
                        <label style="display:flex;gap:8px;align-items:center;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            مفعل للطلاب
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:12px;">رفع ملف</button>
                </form>

                <div style="margin-top:20px;">
                    @forelse($files as $material)
                        <div class="doctor-portal-panel" style="background:#fff;border:1px solid #e5e7eb;margin-bottom:16px;">
                            <div class="doctor-portal-panel-head">
                                <div>
                                    <h3 style="font-size:18px;">{{ $material->title }}</h3>
                                    <p class="doctor-portal-meta">
                                        {{ $material->original_name }}
                                        - {{ number_format($material->file_size / 1024, 2) }} KB
                                        - {{ $material->is_active ? 'مفعل' : 'مخفي' }}
                                    </p>
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a href="{{ route('doctor.materials.download', $material) }}" class="btn btn-default" target="_blank">عرض</a>
                                    <a href="{{ route('doctor.materials.download', ['material' => $material, 'download' => 1]) }}" class="btn btn-default">تنزيل</a>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('doctor.materials.update', $material) }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="doctor-section-info-grid" style="grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                                    <div>
                                        <label>العنوان</label>
                                        <input type="text" name="title" class="form-control" value="{{ $material->title }}" required>
                                    </div>
                                    <div>
                                        <label>الترتيب</label>
                                        <input type="number" name="sort_order" class="form-control" min="0" value="{{ $material->sort_order }}">
                                    </div>
                                    <div>
                                        <label>استبدال الملف</label>
                                        <input type="file" name="upload" class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png,.webp">
                                    </div>
                                </div>
                                <div style="margin-top:12px;">
                                    <label>الوصف</label>
                                    <textarea name="description" class="form-control" rows="3">{{ $material->description }}</textarea>
                                </div>
                                <div style="margin-top:12px;display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
                                    <label style="display:flex;gap:8px;align-items:center;">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($material->is_active)>
                                        مفعل للطلاب
                                    </label>
                                    <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
                                </div>
                            </form>

                            <form method="POST" action="{{ route('doctor.materials.destroy', $material) }}" onsubmit="return confirm('هل تريد حذف هذا الملف؟');" style="margin-top:12px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">حذف الملف</button>
                            </form>
                        </div>
                    @empty
                        <div class="doctor-portal-empty doctor-portal-empty-inline">لا توجد ملفات مرفوعة لهذه المادة بعد.</div>
                    @endforelse
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
