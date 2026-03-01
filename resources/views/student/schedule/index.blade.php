@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:1000px;margin:auto;">
            <h3 style="margin-bottom:20px;">📅 جدول الطالب</h3>

            @if(empty($schedule))
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا يوجد جدول حاليا
                </div>
            @else
                @foreach($schedule as $item)
                    @php
                        $section = $item['section'];
                        $subjectName = $section->subject?->name ?? '—';
                        $semesterName = $section->semester?->name ?? '—';
                        $dayName = $dayNames[$item['day_of_week']] ?? '';
                    @endphp
                    <div style="border:1px solid #e5e5e5;border-radius:8px;margin-bottom:12px;overflow:hidden;">
                        <div style="background:#f8f8f8;padding:10px 14px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong>{{ $subjectName }}</strong>
                                <div style="font-size:12px;color:#666;">{{ $semesterName }}</div>
                            </div>
                            <div style="font-size:12px;">
                                {{ $dayName }} — {{ $item['starts_at'] }} إلى {{ $item['ends_at'] }}
                            </div>
                        </div>
                        <div style="padding:12px 14px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                @if($item['is_now'])
                                    <span style="background:#10b981;color:#fff;padding:3px 8px;border-radius:10px;font-size:12px;">الآن</span>
                                @endif
                            </div>
                            <div>
                                @if($section->mode === 'online' && $section->zoom_url && $item['can_join'])
                                    <a href="{{ $section->zoom_url }}" target="_blank" class="btn btn-primary">دخول المحاضرة</a>
                                @else
                                    <span style="color:#999;font-size:12px;">الرابط غير متاح الآن</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </main>
</div>
@endsection
