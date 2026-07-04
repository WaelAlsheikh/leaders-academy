@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="doctor-portal-panel">
            <h3>{{ $exam->title }}</h3>
            <p>{{ $exam->registrableSubject?->name }} — شعبة {{ $exam->classSection?->name }}</p>
            <p>يبدأ: {{ $exam->starts_at?->format('Y-m-d H:i') }} | ينتهي: {{ $exam->ends_at?->format('Y-m-d H:i') }} | المدة: {{ $exam->duration_minutes }} دقيقة</p>
            @if($grade && $grade->isPublished())
                <div class="alert alert-success">نتيجتك المنشورة: {{ $grade->raw_score }} / {{ $grade->max_score }}</div>
            @elseif($attempt)
                <a href="{{ route('student.exams.attempt', $attempt) }}" class="btn btn-primary">متابعة الامتحان</a>
                @if($attempt->isSubmitted())
                    <a href="{{ route('student.exams.result', $attempt) }}" class="btn btn-secondary">عرض النتيجة</a>
                @endif
            @elseif($canStart)
                <form method="POST" action="{{ route('student.exams.start', $exam) }}">@csrf<button class="btn btn-primary">بدء الامتحان</button></form>
            @else
                <div class="alert alert-warning">الامتحان غير متاح للبدء حالياً.</div>
            @endif
        </section>
    </main>
</div>
@endsection
