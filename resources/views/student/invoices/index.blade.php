@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:1000px;margin:auto;">
            <h3 style="margin-bottom:20px;">💳 الفواتير</h3>

            @if($registrations->isEmpty())
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا يوجد لديك فواتير
                </div>
            @else
                @foreach($registrations as $registration)
                    <div class="student-record-item">
                        <div class="student-record-head">
                            <div>
                                <strong>{{ $registration->registrableEntity?->display_title ?? $registration->college?->title ?? 'غير محدد' }}</strong>
                                <div style="font-size:12px;color:#666;">
                                    {{ $registration->created_at?->format('Y-m-d') }}
                                </div>
                            </div>
                            <div>
                                @php
                                    $statusLabel = [
                                        'under_review' => 'قيد المراجعة',
                                        'accepted' => 'مقبول',
                                        'rejected' => 'مرفوض',
                                    ][$registration->status] ?? $registration->status;

                                    $statusColor = [
                                        'under_review' => '#f59e0b',
                                        'accepted' => '#10b981',
                                        'rejected' => '#ef4444',
                                    ][$registration->status] ?? '#6b7280';
                                @endphp
                                <span style="background:{{ $statusColor }};color:#fff;padding:4px 10px;border-radius:12px;font-size:12px;">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        <div class="student-record-body">
                            <div style="margin-bottom:12px;">
                                <strong>الفصل:</strong>
                                {{ $registration->semester?->name ?? '—' }}
                            </div>
                            <div style="margin-bottom:12px;">
                                <strong>المواد:</strong>
                                <ul style="margin-top:8px;">
                                    @foreach($registration->registrableSubjects as $subject)
                                        <li style="margin-bottom:6px;">
                                            {{ $subject->name }}
                                            <small style="color:#666;">
                                                ({{ $subject->pivot->credit_hours }} ساعات × ${{ number_format($subject->pivot->price_per_hour, 2) }})
                                            </small>
                                            — <strong>${{ number_format($subject->pivot->total_price, 2) }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="student-record-meta-grid">
                                <div>عدد المواد: <strong>{{ $registration->subjects_count }}</strong></div>
                                <div>مجموع الساعات: <strong>{{ $registration->total_hours }}</strong></div>
                                <div>المجموع الجزئي: <strong>${{ number_format($registration->subtotal_amount, 2) }}</strong></div>
                                <div>رسم التسجيل: <strong>${{ number_format($registration->registration_fee, 2) }}</strong></div>
                                <div style="grid-column:span 2;">
                                    المجموع الكلي: <strong>${{ number_format($registration->total_amount, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </main>
</div>
@endsection
