@extends($layout)

@if(($hideNavbar ?? false))
    @section('hide-navbar', '1')
@endif
@section('body-class', $bodyClass ?? '')

@section('css')
    @include('admin.partials.voyager_custom_styles')
@endsection

@section('content')
@php
    $contentMarkup = view()->make('admin.enrollment_cycles.partials.show_content', get_defined_vars())->render();
@endphp

@if(($portalContext ?? 'admin') === 'employee')
    <div class="student-layout">
        @include('employee.partials.sidebar')

        <main class="student-content doctor-portal employee-portal">
            {!! $contentMarkup !!}
        </main>
    </div>
@else
    {!! $contentMarkup !!}
@endif
@endsection
