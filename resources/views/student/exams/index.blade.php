@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="doctor-portal-panel">
            <h3>الامتحانات</h3>
            <table class="doctor-students-table">
                <thead><tr><th>الامتحان</th><th>المادة</th><th>الموعد</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                @forelse($exams as $exam)
                    <tr>
                        <td>{{ $exam->title }}</td>
                        <td>{{ $exam->registrableSubject?->name }}</td>
                        <td>{{ $exam->starts_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}</td>
                        <td><a href="{{ route('student.exams.show', $exam) }}" class="btn btn-secondary btn-sm">عرض</a></td>
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
