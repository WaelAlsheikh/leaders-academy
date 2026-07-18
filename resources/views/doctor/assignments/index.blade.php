@extends('layouts.app')
@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')
@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')
    <main class="student-content doctor-portal">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>الوظائف</h3>
                    <p class="exam-portal-subtitle">إنشاء ومتابعة وظائف الشعب وملفات تسليم الطلاب.</p>
                </div>
                <a href="{{ route('doctor.assignments.create') }}" class="btn btn-primary">وظيفة جديدة</a>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="exam-portal-panel">
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>العنوان</th>
                                <th>المادة</th>
                                <th>الشعبة</th>
                                <th>الموعد</th>
                                <th>الحالة</th>
                                <th>التسليمات</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $assignment)
                            <tr>
                                <td><strong>{{ $assignment->title }}</strong></td>
                                <td>{{ $assignment->registrableSubject?->name }}</td>
                                <td>{{ $assignment->classSection?->name }}</td>
                                <td>
                                    {{ $assignment->starts_at?->format('Y-m-d H:i') }}
                                    <br>
                                    {{ $assignment->ends_at?->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <span class="exam-badge exam-badge--{{ $assignment->windowStatus() === 'open' ? 'success' : ($assignment->windowStatus() === 'upcoming' ? 'info' : 'muted') }}">
                                        {{ $assignment->windowStatusLabel() }}
                                    </span>
                                </td>
                                <td>{{ $assignment->submissions_count }}</td>
                                <td>
                                    <a href="{{ route('doctor.assignments.show', $assignment) }}" class="btn btn-secondary btn-sm">عرض</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="exam-portal-empty">لا توجد وظائف بعد.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px;">{{ $assignments->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection
