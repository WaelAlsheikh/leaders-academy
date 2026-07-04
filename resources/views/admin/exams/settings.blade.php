@extends($layout)
@if($hideNavbar ?? false) @section('hide-navbar', '1') @endif
@section('body-class', $bodyClass ?? '')
@section('content')
@php $content = view('admin.exams.partials.settings_content', get_defined_vars())->render(); @endphp
@if(($portalContext ?? 'admin') === 'employee')
<div class="student-layout">@include('employee.partials.sidebar')<main class="student-content employee-portal">{!! $content !!}</main></div>
@else {!! $content !!} @endif
@endsection
