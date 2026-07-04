@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <h3>{{ $exam->title }}</h3>
            <p>{{ $exam->registrableSubject?->name }} — {{ $exam->classSection?->name }}</p>
            <p>الحالة: {{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }} | {{ $exam->total_points }} درجة</p>
            <a href="{{ route('doctor.exams.grading.review', $exam) }}" class="btn btn-primary">تصحيح المحاولات</a>
        </section>
    </main>
</div>
@endsection
