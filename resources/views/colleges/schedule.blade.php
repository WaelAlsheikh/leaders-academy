@extends('layouts.app')

@section('content')
<div class="container" style="padding:60px 0;">
    <h2>جدول كلية {{ $college->title }}</h2>

    @if(empty($schedule))
        <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
            لا يوجد جدول حاليا
        </div>
    @else
        @foreach($schedule as $item)
            @php
                $section = $item['section'];
                $subjectName = $section->subject?->name ?? '—';
                $semesterName = $item['semester']?->name ?? '—';
                $dayName = $dayNames[$item['day_of_week']] ?? '';
            @endphp
            <div style="border:1px solid #e5e5e5;border-radius:8px;margin-bottom:12px;overflow:hidden;">
                <div style="background:#f8f8f8;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <strong>{{ $subjectName }}</strong>
                        <div style="font-size:12px;color:#666;">{{ $semesterName }} — شعبة {{ $section->name }}</div>
                    </div>
                    <div style="font-size:12px;">
                        {{ $dayName }} — {{ $item['starts_at'] }} إلى {{ $item['ends_at'] }}
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
