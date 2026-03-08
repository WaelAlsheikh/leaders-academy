@extends('layouts.app')

@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')

    <main class="student-content student-dashboard">
        @if(!$hasRegistrations)
            <section class="student-dashboard-hero student-dashboard-empty">
                <h1 class="student-dashboard-title">مرحباً {{ $student->first_name }} {{ $student->last_name }}</h1>
                <p class="student-dashboard-username">اسم المستخدم: {{ $student->username }}</p>
                <p class="student-dashboard-subtitle">
                    لا توجد تسجيلات أكاديمية حتى الآن. ابدأ الآن وحدد البرنامج المناسب لك.
                </p>
                <div class="student-dashboard-actions">
                    <a href="{{ route('student.registration.create') }}" class="student-dashboard-btn student-dashboard-btn-primary">
                        تسجيل جديد
                    </a>
                    <a href="{{ route('colleges.index') }}" class="student-dashboard-btn student-dashboard-btn-light">
                        استعراض الكليات
                    </a>
                </div>
            </section>

            <section class="student-dashboard-steps">
                <h3>كيف تبدأ؟</h3>
                <div class="student-dashboard-step-grid">
                    <div class="student-dashboard-step-card">
                        <span class="student-dashboard-step-number">1</span>
                        <p>اختر نوع التسجيل</p>
                    </div>
                    <div class="student-dashboard-step-card">
                        <span class="student-dashboard-step-number">2</span>
                        <p>اختر المواد المناسبة</p>
                    </div>
                    <div class="student-dashboard-step-card">
                        <span class="student-dashboard-step-number">3</span>
                        <p>تابع حالة الطلب من صفحة تسجيلاتي</p>
                    </div>
                </div>
            </section>
        @else
            <section class="student-dashboard-hero">
                <h1 class="student-dashboard-title">أهلاً {{ $student->first_name }} {{ $student->last_name }}</h1>
                <p class="student-dashboard-username">اسم المستخدم: {{ $student->username }}</p>
                <p class="student-dashboard-subtitle">ملخص أكاديمي سريع لحسابك</p>
            </section>

            <section class="student-dashboard-kpis">
                <div class="student-dashboard-kpi-card">
                    <span class="student-dashboard-kpi-label">إجمالي التسجيلات</span>
                    <strong class="student-dashboard-kpi-value">{{ $stats['total_registrations'] }}</strong>
                </div>
                <div class="student-dashboard-kpi-card">
                    <span class="student-dashboard-kpi-label">تسجيلات مقبولة</span>
                    <strong class="student-dashboard-kpi-value student-dashboard-kpi-value-accepted">{{ $stats['accepted_registrations'] }}</strong>
                </div>
                <div class="student-dashboard-kpi-card">
                    <span class="student-dashboard-kpi-label">قيد المراجعة</span>
                    <strong class="student-dashboard-kpi-value student-dashboard-kpi-value-under-review">{{ $stats['under_review_registrations'] }}</strong>
                </div>
                <div class="student-dashboard-kpi-card">
                    <span class="student-dashboard-kpi-label">مرتبطة بفصل</span>
                    <strong class="student-dashboard-kpi-value">{{ $stats['semester_linked_registrations'] }}</strong>
                </div>
            </section>

            <section class="student-dashboard-panel">
                <div class="student-dashboard-panel-header">
                    <h3>آخر تسجيلاتك</h3>
                    <a href="{{ route('student.registrations.index') }}" class="student-dashboard-link">عرض تسجيلاتي</a>
                </div>
                <div class="student-dashboard-table-wrap">
                    <table class="student-dashboard-table">
                        <thead>
                            <tr>
                                <th>الكيان</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestRegistrations as $registration)
                                @php
                                    $entityTitle = $registration->registrableEntity?->display_title
                                        ?? $registration->college?->title
                                        ?? '—';
                                    $statusMap = [
                                        'accepted' => ['label' => 'مقبولة', 'class' => 'accepted'],
                                        'under_review' => ['label' => 'قيد المراجعة', 'class' => 'under_review'],
                                        'rejected' => ['label' => 'مرفوضة', 'class' => 'rejected'],
                                    ];
                                    $statusInfo = $statusMap[$registration->status] ?? ['label' => $registration->status, 'class' => 'under_review'];
                                @endphp
                                <tr>
                                    <td>{{ $entityTitle }}</td>
                                    <td>
                                        <span class="student-dashboard-status {{ $statusInfo['class'] }}">
                                            {{ $statusInfo['label'] }}
                                        </span>
                                    </td>
                                    <td>{{ optional($registration->created_at)->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="student-dashboard-panel">
                <h3>إجراءات سريعة</h3>
                <div class="student-dashboard-actions student-dashboard-actions-grid">
                    <a href="{{ route('student.registration.create') }}" class="student-dashboard-btn student-dashboard-btn-primary">تسجيل جديد</a>
                    <a href="{{ route('student.registrations.index') }}" class="student-dashboard-btn student-dashboard-btn-light">تسجيلاتي</a>
                    <a href="{{ route('student.schedule.index') }}" class="student-dashboard-btn student-dashboard-btn-light">الجدول</a>
                    <a href="{{ route('student.invoices.index') }}" class="student-dashboard-btn student-dashboard-btn-light">الفواتير</a>
                </div>
            </section>
        @endif
    </main>

</div>
@endsection
