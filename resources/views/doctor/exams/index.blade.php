@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <h3>امتحاناتي</h3>
            @if(\App\Models\ExamSetting::isManualMode())
                <a href="{{ route('doctor.exams.create') }}" class="btn btn-primary">إنشاء امتحان يدوي</a>
            @endif
            <table class="doctor-students-table" style="margin-top:16px;">
                <thead><tr><th>العنوان</th><th>المادة</th><th>الحالة</th><th>الموعد</th><th></th></tr></thead>
                <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td>{{ $exam->title }}</td>
                        <td>{{ $exam->registrableSubject?->name }}</td>
                        <td>{{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}</td>
                        <td>{{ $exam->starts_at?->format('Y-m-d H:i') }}</td>
                        <td>
                            <a href="{{ route('doctor.exams.show', $exam) }}" class="btn btn-secondary btn-sm">عرض</a>
                            <a href="{{ route('doctor.exams.grading.review', $exam) }}" class="btn btn-secondary btn-sm">تصحيح</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" style="text-align:center;">لا توجد امتحانات.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $exams->links() }}
        </section>
    </main>
</div>
@endsection
