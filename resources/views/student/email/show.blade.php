@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        @include('email.partials.my_mailbox')
    </main>
</div>
@endsection
