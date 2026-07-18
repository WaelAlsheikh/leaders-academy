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
                    <h3>بنك الأسئلة — التصنيفات</h3>
                    <p class="exam-portal-subtitle">نظّم أسئلتك في تصنيفات لتسهيل إنشاء الامتحانات.</p>
                </div>
                <a href="{{ route('doctor.exams.questions.index') }}" class="btn btn-secondary">الأسئلة</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="exam-portal-panel">
                <form method="POST" action="{{ route('doctor.exams.categories.store') }}" class="exam-portal-actions" style="margin-top:0;margin-bottom:18px;">
                    @csrf
                    <input type="text" name="name" class="form-control" placeholder="اسم التصنيف الجديد" required style="max-width:320px;">
                    <button class="btn btn-primary">إضافة تصنيف</button>
                </form>

                <ul class="exam-category-list">
                    @forelse($categories as $category)
                        <li class="exam-category-item">
                            <strong>{{ $category->name }}</strong>
                            <span class="exam-badge exam-badge--info">{{ $category->questions_count }} سؤال</span>
                        </li>
                    @empty
                        <li class="exam-portal-empty">لا توجد تصنيفات بعد. أضف تصنيفاً للبدء.</li>
                    @endforelse
                </ul>
            </div>
        </section>
    </main>
</div>
@endsection
