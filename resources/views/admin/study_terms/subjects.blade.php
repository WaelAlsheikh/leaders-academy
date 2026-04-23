@extends($layout)

@if($hideNavbar)
    @section('hide-navbar', '1')
@endif
@section('body-class', $bodyClass)

@section('css')
    @if($portalContext !== 'employee')
        @include('admin.partials.voyager_custom_styles')
    @endif
@endsection

@section('content')
    @if($portalContext === 'employee')
        <div class="student-layout">
            @include('employee.partials.sidebar')

            <main class="student-content doctor-portal employee-portal">
                @include('admin.study_terms.partials.subjects_manager')
            </main>
        </div>
    @else
        <div class="page-content container-fluid">
            @include('admin.study_terms.partials.subjects_manager')
        </div>
    @endif
@endsection
