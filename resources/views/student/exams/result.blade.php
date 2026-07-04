@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="doctor-portal-panel">
            <h3>نتيجة الامتحان</h3>
            @if($grade)
                <div style="font-size:2rem;font-weight:bold;margin:16px 0;">{{ $grade->raw_score }} / {{ $grade->max_score }}</div>
                <p>الحالة: {{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}</p>
                @if(in_array($grade->status, ['auto_corrected', 'pending_review', 'reviewed', 'approved']))
                    <div class="alert alert-info">{{ $preliminaryMessage }}</div>
                @endif
                @if($grade->isPublished())
                    <div class="alert alert-success">تم اعتماد ونشر نتيجتك النهائية.</div>
                @endif
            @else
                <p>جارٍ معالجة نتيجتك...</p>
            @endif
            <a href="{{ route('student.exams.index') }}" class="btn btn-secondary">العودة للامتحانات</a>
        </section>
    </main>
</div>
@endsection
