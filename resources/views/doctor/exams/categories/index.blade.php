@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <h3>بنك الأسئلة — التصنيفات</h3>
            @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
            <form method="POST" action="{{ route('doctor.exams.categories.store') }}" class="doctor-live-actions">
                @csrf
                <input type="text" name="name" class="form-control" placeholder="اسم التصنيف" required>
                <button class="btn btn-primary">إضافة</button>
            </form>
            <ul class="college-features-list" style="margin-top:16px;">
                @foreach($categories as $category)
                    <li>{{ $category->name }} ({{ $category->questions_count }} سؤال)</li>
                @endforeach
            </ul>
            <a href="{{ route('doctor.exams.questions.index') }}" class="btn btn-secondary">الأسئلة</a>
        </section>
    </main>
</div>
@endsection
