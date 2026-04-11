@extends('layouts.app')

@section('content')
<div class="student-layout">

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
                        $liveSession = $item['live_session'];
                        $subjectName = $section->registrableSubject?->name ?? $section->subject?->name ?? '—';
                        $semesterName = $section->semester?->name ?? '—';
                        $dayName = $dayNames[$item['day_of_week']] ?? '';
                        $doctorName = $section->doctor?->full_name ?? 'لم يُحدَّد المدرّس بعد';
                        $statusCode = $item['status']['code'] ?? 'not_started';
                        $statusLabel = $item['status']['label'] ?? 'لم تبدأ الجلسة';
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
                                {{ $dayName }} — {{ substr($item['starts_at'], 0, 5) }} إلى {{ substr($item['ends_at'], 0, 5) }}
                            </div>
                        </div>
                        <div class="student-schedule-body">
                            <div>
                                @if($item['is_now'])
                                    <span style="background:#10b981;color:#fff;padding:3px 8px;border-radius:10px;font-size:12px;">الآن</span>
                                @endif
                            </div>
                            <div class="student-schedule-action-wrap">
                                @if($section->mode !== 'online')
                                    <span style="color:#999;font-size:12px;">المحاضرة حضورية</span>
                                @elseif($item['can_join'] && $liveSession)
                                    <a href="{{ route('student.live_sessions.show', $liveSession) }}" class="btn btn-primary">بدأت الجلسة — انقر للدخول</a>
                                @else
                                    <span class="student-live-status student-live-status-{{ $statusCode }}">{{ $statusLabel }}</span>
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
