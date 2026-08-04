@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'employee-shell')
@section('content')
<div class="student-layout">
    @include('employee.partials.sidebar')
    <main class="student-content doctor-portal employee-portal">
        @include('email.partials.my_mailbox')
    </main>
</div>
@endsection
