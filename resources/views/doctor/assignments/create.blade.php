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
                    <h3>وظيفة جديدة</h3>
                    <p class="exam-portal-subtitle">اختر الشعبة وحدد موعد فتح وإغلاق التسليم.</p>
                </div>
                <a href="{{ route('doctor.assignments.index') }}" class="btn btn-secondary">العودة</a>
            </div>

            <div class="exam-portal-panel">
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('doctor.assignments.store') }}">
                    @csrf
                    <div class="exam-portal-meta-grid">
                        <div class="form-group">
                            <label>الشعبة / المادة</label>
                            <select name="class_section_id" class="form-control" required>
                                <option value="">— اختر —</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" @selected(old('class_section_id') == $section->id)>
                                        {{ $section->registrableSubject?->name }} — {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>عنوان الوظيفة</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                        </div>
                        <div class="form-group">
                            <label>يبدأ</label>
                            <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at') }}" required>
                        </div>
                        <div class="form-group">
                            <label>ينتهي</label>
                            <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at') }}" required>
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:14px;">
                        <label>الوصف / التعليمات</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                    </div>
                    <div class="exam-portal-actions">
                        <button type="submit" class="btn btn-primary">إنشاء ونشر</button>
                    </div>
                </form>
            </div>
        </section>
    </main>
</div>
@endsection
