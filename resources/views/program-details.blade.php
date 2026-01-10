@extends('layouts.app')

@section('content')
<section class="section">
    <div class="container">
        <div class="program-detail">
            <img src="{{ asset('storage/' . $program->image) }}" alt="{{ $program->title }}">
            <h1>{{ $program->title }}</h1>
            <p>{!! $program->description !!}</p>

            <a href="{{ route('contact') }}" class="btn-primary">📩 أرسل طلب تسجيل</a>
        </div>
    </div>
</section>
@endsection
