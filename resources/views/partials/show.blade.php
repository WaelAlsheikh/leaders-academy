@extends('layouts.app')

@section('content')
  <h1 class="text-2xl font-bold mb-4">{{ $page->title }}</h1>
  <div class="prose">
    {!! $page->content !!}
  </div>
@endsection
