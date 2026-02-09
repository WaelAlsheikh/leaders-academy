@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:1000px;margin:auto;">
            <h3 style="margin-bottom:20px;">📚 تسجيلاتي</h3>

            @if($registrations->isEmpty())
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا يوجد لديك تسجيلات
                </div>
            @else
                @foreach($registrations as $registration)
                    <div style="border:1px solid #e5e5e5;border-radius:8px;margin-bottom:20px;overflow:hidden;">
                        <div style="background:#f8f8f8;padding:12px 16px;display:flex;justify-content:space-between;align-items:center;">
                            <div>
                                <strong>{{ $registration->college?->title ?? 'غير محدد' }}</strong>
                                <div style="font-size:12px;color:#666;">
                                    {{ $registration->created_at?->format('Y-m-d') }}
                                </div>
                            </div>
                            <div>
                                @php
                                    $statusLabel = [
                                        'pending' => 'قيد المراجعة',
                                        'approved' => 'معتمدة',
                                        'paid' => 'مدفوعة',
                                    ][$registration->status] ?? $registration->status;

                                    $statusColor = [
                                        'pending' => '#f59e0b',
                                        'approved' => '#3b82f6',
                                        'paid' => '#10b981',
                                    ][$registration->status] ?? '#6b7280';
                                @endphp
                                <span style="background:{{ $statusColor }};color:#fff;padding:4px 10px;border-radius:12px;font-size:12px;">
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>

                        <div style="padding:16px;">
                            <div style="margin-bottom:12px;">
                                <strong>المواد:</strong>
                                <ul style="margin-top:8px;">
                                    @foreach($registration->subjects as $subject)
                                        <li style="margin-bottom:6px;">
                                            {{ $subject->name }}
                                            <small style="color:#666;">
                                                ({{ $subject->pivot->credit_hours }} ساعات)
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;">
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
