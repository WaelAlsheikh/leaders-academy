@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>الوظائف</h3>
                    <p class="exam-portal-subtitle">الوظائف المطلوبة من أساتذة الشعب المسجّل بها.</p>
                </div>
            </div>

            <div class="exam-portal-panel">
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>الوظيفة</th>
                                <th>المادة</th>
                                <th>الدكتور</th>
                                <th>الموعد</th>
                                <th>الحالة</th>
                                <th>تسليمك</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($assignments as $assignment)
                            @php $submission = $submissions->get($assignment->id); @endphp
                            <tr>
                                <td><strong>{{ $assignment->title }}</strong></td>
                                <td>{{ $assignment->registrableSubject?->name }}</td>
                                <td>{{ $assignment->doctor?->full_name }}</td>
                                <td>
                                    {{ $assignment->starts_at?->format('Y-m-d H:i') }}
                                    <br>
                                    {{ $assignment->ends_at?->format('Y-m-d H:i') }}
                                </td>
                                <td>
                                    <span class="exam-badge exam-badge--{{ $assignment->windowStatus() === 'open' ? 'success' : ($assignment->windowStatus() === 'upcoming' ? 'info' : 'warning') }}">
                                        {{ $assignment->windowStatusLabel() }}
                                    </span>
                                </td>
                                <td>
                                    @if($submission && $submission->files_count > 0)
                                        <span class="exam-badge exam-badge--success">{{ $submission->files_count }} ملف</span>
                                    @else
                                        <span class="exam-badge exam-badge--muted">لم تُرفع</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('student.assignments.show', $assignment) }}" class="btn btn-secondary btn-sm">عرض</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="exam-portal-empty">لا توجد وظائف حالياً.</div></td></tr>
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
