@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-university"></i> إدارة الكليات
    </h1>

    <div class="row">
        @foreach($colleges as $college)
            <div class="col-md-4">
                <div class="panel panel-bordered">
                    <div class="panel-body text-center">
                        <h4>{{ $college->title }}</h4>
                        <div style="margin:8px 0;color:#555;">
                            سعر الساعة: <strong>${{ number_format($college->price_per_credit_hour, 2) }}</strong>
                        </div>
                        <a href="{{ route('admin.colleges.subjects', $college) }}"
                           class="btn btn-primary">
                            إدارة المواد
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
