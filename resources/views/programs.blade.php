@extends('layouts.app')

@section('content')
<main class="section">
    <div class="container">
        <h1 class="page-title">جميع البرامج التدريبية</h1>
        <div class="grid">
            @foreach($programs as $program)
                <div class="card">
                    <img src="{{ asset('storage/' . $program->image) }}" alt="{{ $program->title }}">
                    <h3>{{ $program->title }}</h3>
                    <p>{{ $program->short_description }}</p>
                    <a href="#" class="btn-primary">اعرف المزيد</a>
                </div>
            @endforeach
        </div>
    </div>
</main>
@endsection
