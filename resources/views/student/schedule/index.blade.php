@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:1000px;margin:auto;">
            <h3 style="margin-bottom:20px;">📅 جدول الطالب</h3>

            @if(!empty($hasArchivedCycles))
                <div class="student-archived-notice" style="margin-bottom:16px;">
                    هذه الدورة أغلقت وتمت أرشفتها، وتبقى بياناتها محفوظة للعرض فقط.
                </div>
            @endif

            @if(empty($schedule))
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا يوجد جدول حاليا
                </div>
            @else
                @foreach($schedule as $item)
                    @php
                        $section = $item['section'];
                        $subjectName = $section->registrableSubject?->name ?? $section->subject?->name ?? '—';
                        $semesterName = $section->semester?->name ?? '—';
                        $dayName = $dayNames[$item['day_of_week']] ?? '';
                        $doctorName = $section->doctor?->full_name ?? 'لم يُحدَّد المدرّس بعد';
                    @endphp
                    <div class="student-schedule-item">
                        <div class="student-schedule-head">
                            <div>
                                <strong>{{ $subjectName }}</strong>
                                <div style="font-size:12px;color:#666;">{{ $semesterName }}</div>
                                <div style="font-size:12px;color:#666;">المدرّس: {{ $doctorName }}</div>
                                @if($section->semester?->enrollmentCycle?->is_archived)
                                    <div class="student-schedule-archived-label">الدورة مؤرشفة</div>
                                @endif
                            </div>
                            <div style="font-size:12px;">
                                {{ $dayName }} — {{ $item['starts_at'] }} إلى {{ $item['ends_at'] }}
                            </div>
                        </div>
                        <div class="student-schedule-body">
                            <div>
                                @if($item['is_now'])
                                    <span style="background:#10b981;color:#fff;padding:3px 8px;border-radius:10px;font-size:12px;">الآن</span>
                                @endif
                            </div>
                            <div>
                                @if($section->mode !== 'online')
                                    <span style="color:#999;font-size:12px;">المحاضرة حضورية</span>
                                @elseif(!$section->zoom_url)
                                    <span style="color:#999;font-size:12px;">لا يوجد رابط للمحاضرة بعد</span>
                                @elseif($item['can_join'])
                                    <a href="{{ $section->zoom_url }}" target="_blank" class="btn btn-primary">دخول المحاضرة</a>
                                @else
                                    <span style="color:#999;font-size:12px;">يتاح الرابط قبل 5 دقائق من الموعد</span>
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
