@php
    $edit = !is_null($dataTypeContent->getKey());
    $add = is_null($dataTypeContent->getKey());
@endphp

@extends('voyager::master')

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('page_title',
    __('voyager::generic.' . ($edit ? 'edit' : 'add')) . ' ' .
    $dataType->getTranslatedAttribute('display_name_singular')
)

@section('page_header')
    <h1 class="page-title">
        <i class="{{ $dataType->icon }}"></i>
        {{ __('voyager::generic.' . ($edit ? 'edit' : 'add')) . ' ' . $dataType->getTranslatedAttribute('display_name_singular') }}
    </h1>
    @include('voyager::multilingual.language-selector')
@stop

@section('content')
<div class="page-content edit-add container-fluid">
    <div class="row">
        <div class="col-md-12">

            <div class="panel panel-bordered">
                <form role="form"
                      class="form-edit-add"
                      action="{{ $edit
                        ? route('voyager.' . $dataType->slug . '.update', $dataTypeContent->getKey())
                        : route('voyager.' . $dataType->slug . '.store') }}"
                      method="POST"
                      enctype="multipart/form-data">

                    @if ($edit)
                        {{ method_field('PUT') }}
                    @endif

                    {{ csrf_field() }}

                    @php
                        $collegeId = request('college_id');
                        if (!$collegeId && request('key') === 'college_id' && request('s')) {
                            $collegeId = request('s');
                        }
                    @endphp

                    {{-- 🔁 redirect بعد الحفظ --}}
                    @if ($collegeId)
                        <input type="hidden" name="redirect"
                               value="{{ route('voyager.subjects.index', [
                                    'key'    => 'college_id',
                                    'filter' => 'equals',
                                    's'      => $collegeId
                               ]) }}">
                        <input type="hidden" name="college_id" value="{{ $collegeId }}">
                        <input type="hidden" name="key" value="college_id">
                        <input type="hidden" name="filter" value="equals">
                        <input type="hidden" name="s" value="{{ $collegeId }}">
                    @endif

                    <div class="panel-body">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @php
                            $dataTypeRows = $dataType->{$edit ? 'editRows' : 'addRows'};
                        @endphp

                        @foreach ($dataTypeRows as $row)

                            {{-- 🟢 ربط المادة بالكلية تلقائيًا --}}
                            @if ($row->field === 'college_id' && $collegeId)
                                @continue
                            @endif

                            <div class="form-group col-md-12 {{ $errors->has($row->field) ? 'has-error' : '' }}">
                                <label>{{ $row->getTranslatedAttribute('display_name') }}</label>

                                @if ($row->type == 'relationship')
                                    @include('voyager::formfields.relationship')
                                @else
                                    {!! app('voyager')->formField($row, $dataType, $dataTypeContent) !!}
                                @endif

                                @if ($errors->has($row->field))
                                    <span class="help-block">{{ $errors->first($row->field) }}</span>
                                @endif
                            </div>

                        @endforeach

                    </div>

                    <div class="panel-footer">
                        <button type="submit" class="btn btn-primary save">
                            {{ __('voyager::generic.save') }}
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>
@stop
