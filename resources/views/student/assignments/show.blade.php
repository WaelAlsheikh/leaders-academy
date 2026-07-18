@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>{{ $assignment->title }}</h3>
                    <p class="exam-portal-subtitle">
                        {{ $assignment->registrableSubject?->name }} — {{ $assignment->doctor?->full_name }}
                    </p>
                </div>
                <a href="{{ route('student.assignments.index') }}" class="btn btn-secondary">العودة للقائمة</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <div class="exam-portal-panel">
                <div class="exam-portal-meta-grid">
                    <div class="exam-portal-meta-card">
                        <span>يبدأ التسليم</span>
                        <strong>{{ $assignment->starts_at?->format('Y-m-d H:i') }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>ينتهي التسليم</span>
                        <strong>{{ $assignment->ends_at?->format('Y-m-d H:i') }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>حالة النافذة</span>
                        <strong>
                            <span class="exam-badge exam-badge--{{ $canSubmit ? 'success' : 'warning' }}">
                                {{ $assignment->windowStatusLabel() }}
                            </span>
                        </strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>الشعبة</span>
                        <strong>{{ $assignment->classSection?->name }}</strong>
                    </div>
                </div>

                @if($assignment->description)
                    <div style="margin-top:14px;line-height:1.8;white-space:pre-wrap;">{{ $assignment->description }}</div>
                @endif
            </div>

            <div class="exam-portal-panel">
                <h4 style="margin:0 0 14px;color:#083b59;">ملفات تسليمك</h4>

                @if($submission->files->isEmpty())
                    <div class="exam-portal-empty" style="padding:20px;">لم ترفع أي ملفات بعد.</div>
                @else
                    <div class="exam-portal-table-wrap">
                        <table class="exam-portal-table">
                            <thead>
                                <tr>
                                    <th>الملف</th>
                                    <th>الحجم</th>
                                    <th>تاريخ الرفع</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($submission->files as $file)
                                <tr>
                                    <td><strong>{{ $file->original_name }}</strong></td>
                                    <td>{{ $file->humanSize() }}</td>
                                    <td>{{ $file->created_at?->format('Y-m-d H:i') }}</td>
                                    <td>
                                        @if($file->isPreviewableInline())
                                            <a class="btn btn-secondary btn-sm" target="_blank" href="{{ route('student.assignment_files.download', $file) }}">معاينة</a>
                                        @endif
                                        <a class="btn btn-secondary btn-sm" href="{{ route('student.assignment_files.download', $file) }}?download=1">تحميل</a>
                                        @if($canSubmit)
                                            <form method="POST" action="{{ route('student.assignment_files.replace', $file) }}" enctype="multipart/form-data" style="display:inline-block;margin-top:6px;">
                                                @csrf
                                                <input type="file" name="upload" required accept=".{{ str_replace(',', ',.', $allowedMimes) }}">
                                                <button class="btn btn-secondary btn-sm">استبدال</button>
                                            </form>
                                            <form method="POST" action="{{ route('student.assignment_files.destroy', $file) }}" style="display:inline;" onsubmit="return confirm('حذف هذا الملف؟');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-secondary btn-sm">حذف</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                @if($canSubmit)
                    <form method="POST" action="{{ route('student.assignments.upload', $assignment) }}" enctype="multipart/form-data" class="exam-question-image-upload" style="margin-top:18px;">
                        @csrf
                        <label>رفع ملف جديد</label>
                        <p class="exam-portal-subtitle">الصيغ المسموحة: {{ $allowedMimes }} — الحد الأقصى {{ number_format($maxFileKb / 1024, 0) }}MB لكل ملف</p>
                        <input type="file" name="upload" class="form-control" required accept=".{{ str_replace(',', ',.', $allowedMimes) }}">
                        <div class="exam-portal-actions">
                            <button type="submit" class="btn btn-primary">رفع الملف</button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-warning" style="margin-top:16px;">
                        لا يمكن رفع أو تعديل الملفات حالياً. يجب التسليم ضمن الموعد المحدد من الدكتور.
                    </div>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection
