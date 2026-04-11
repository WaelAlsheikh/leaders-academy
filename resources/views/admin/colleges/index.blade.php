@extends($layout)

@if($hideNavbar)
    @section('hide-navbar', '1')
@endif
@section('body-class', $bodyClass)

@section('content')
    @if($portalContext === 'employee')
        <div class="student-layout">
            @include('employee.partials.sidebar')

            <main class="student-content doctor-portal employee-portal">
                @include('admin.colleges.partials.manager')
            </main>
        </div>
    @else
        <div class="page-content container-fluid">
            @include('admin.colleges.partials.manager')
        </div>
    @endif
@endsection
