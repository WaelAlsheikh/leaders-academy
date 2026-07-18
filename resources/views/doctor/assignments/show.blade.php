@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>{{ $assignment->title }}</h3>
                    <p class="exam-portal-subtitle">
                        {{ $assignment->registrableSubject?->name }} — شعبة {{ $assignment->classSection?->name }}
                    </p>
                </div>
                <div class="exam-portal-actions" style="margin-top:0;">
                    <a href="{{ route('doctor.assignments.edit', $assignment) }}" class="btn btn-secondary">تعديل</a>
                    <a href="{{ route('doctor.assignments.index') }}" class="btn btn-secondary">القائمة</a>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="exam-portal-panel">
                <div class="exam-portal-meta-grid">
                    <div class="exam-portal-meta-card">
                        <span>يبدأ</span>
                        <strong>{{ $assignment->starts_at?->format('Y-m-d H:i') }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>ينتهي</span>
                        <strong>{{ $assignment->ends_at?->format('Y-m-d H:i') }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>حالة النافذة</span>
                        <strong>{{ $assignment->windowStatusLabel() }}</strong>
                    </div>
                    <div class="exam-portal-meta-card">
                        <span>الحالة</span>
                        <strong>{{ $assignment->statusLabel() }}</strong>
                    </div>
                </div>

                @if($assignment->description)
                    <div style="margin-top:12px;line-height:1.8;">{{ $assignment->description }}</div>
                @endif

                <div class="exam-portal-actions">
                    @if($assignment->status === 'published')
                        <form method="POST" action="{{ route('doctor.assignments.close', $assignment) }}">@csrf<button class="btn btn-secondary btn-sm">إغلاق التسليم</button></form>
                    @endif
                    @if($assignment->status !== 'archived')
                        <form method="POST" action="{{ route('doctor.assignments.archive', $assignment) }}">@csrf<button class="btn btn-secondary btn-sm" onclick="return confirm('أرشفة هذه الوظيفة؟');">أرشفة</button></form>
                    @endif
                </div>
            </div>

            <div class="exam-portal-panel">
                <h4 style="margin:0 0 16px;color:#083b59;">تسليمات الطلاب ({{ $students->count() }})</h4>
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>الحالة</th>
                                <th>الملفات</th>
                                <th>ملاحظات الدكتور</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($students as $student)
                            @php $submission = $submissions->get($student->id); @endphp
                            <tr>
                                <td><strong>{{ $student->first_name }} {{ $student->last_name }}</strong></td>
                                <td>
                                    @if($submission && $submission->files->count())
                                        <span class="exam-badge exam-badge--success">تم التسليم</span>
                                        <div class="exam-portal-subtitle" style="margin-top:6px;">
                                            آخر رفع: {{ $submission->submitted_at?->format('Y-m-d H:i') }}
                                        </div>
                                    @else
                                        <span class="exam-badge exam-badge--muted">لم يرفع بعد</span>
                                    @endif
                                </td>
                                <td>
                                    @if($submission && $submission->files->count())
                                        <ul style="margin:0;padding-right:18px;">
                                            @foreach($submission->files as $file)
                                                <li style="margin-bottom:8px;">
                                                    {{ $file->original_name }}
                                                    <small>({{ $file->humanSize() }})</small>
                                                    <div style="margin-top:4px;">
                                                        @if($file->isPreviewableInline())
                                                            <a class="btn btn-secondary btn-sm" target="_blank" href="{{ route('doctor.assignment_files.download', $file) }}">معاينة</a>
                                                        @endif
                                                        <a class="btn btn-secondary btn-sm" href="{{ route('doctor.assignment_files.download', $file) }}?download=1">تحميل</a>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="exam-portal-subtitle">—</span>
                                    @endif
                                </td>
                                <td style="min-width:240px;">
                                    @php $submission = $submissions->get($student->id); @endphp
                                    @if($submission)
                                        <form method="POST" action="{{ route('doctor.assignment_submissions.notes', $submission) }}">
                                            @csrf
                                            <textarea name="doctor_notes" class="form-control" rows="3" placeholder="ملاحظات خاصة (للدكتور فقط)">{{ $submission->doctor_notes }}</textarea>
                                            <button class="btn btn-primary btn-sm" style="margin-top:8px;">حفظ الملاحظات</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><div class="exam-portal-empty">لا يوجد طلاب نشطون في هذه الشعبة.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
