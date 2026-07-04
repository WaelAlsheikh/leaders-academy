@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <h3>درجات الامتحانات</h3>
            <table class="doctor-students-table">
                <thead><tr><th>الطالب</th><th>الامتحان</th><th>المادة</th><th>الدرجة</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                @forelse($grades as $grade)
                    <tr>
                        <td>{{ $grade->student?->first_name }} {{ $grade->student?->last_name }}</td>
                        <td>{{ $grade->exam?->title }}</td>
                        <td>{{ $grade->exam?->registrableSubject?->name }}</td>
                        <td>{{ $grade->raw_score }} / {{ $grade->max_score }}</td>
                        <td>{{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}</td>
                        <td>
                            @if($grade->exam)
                                <a href="{{ route('doctor.exams.grading.review', $grade->exam) }}" class="btn btn-secondary btn-sm">تصحيح</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="text-align:center;">لا توجد درجات بعد.</td></tr>
                @endforelse
                </tbody>
            </table>
            {{ $grades->links() }}
        </section>
    </main>
</div>
@endsection
