@extends($layout ?? 'voyager::master')

@section('css')
    @include('admin.partials.voyager_custom_styles')
@stop

@section('content')
<div class="container-fluid employee-cycle-page custom-admin-page custom-admin-page--voyager">
    @include('email.partials.my_mailbox')
</div>
@endsection
