@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    {{-- Main Content --}}
    <main class="student-content">
        <div class="card" style="padding:30px;">
            <h2 style="color:var(--primary);margin-bottom:10px;">
                أهلاً بك 👋
            </h2>
            <p>
                مرحباً بك في منصة الطلاب الخاصة بمعهد ليدرز  
                <br>اختر من القائمة الجانبية للبدء
            </p>
        </div>
    </main>

</div>
@endsection
