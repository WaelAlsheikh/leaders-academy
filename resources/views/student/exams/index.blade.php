@extends('layouts.app')
@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')
    <main class="student-content">
        <section class="exam-portal-page">
            <div class="exam-portal-header">
                <div>
                    <h3>الامتحانات</h3>
                    <p class="exam-portal-subtitle">الامتحانات المتاحة للشعب المسجل بها.</p>
                </div>
            </div>

            <div class="exam-portal-panel">
                <div class="exam-portal-table-wrap">
                    <table class="exam-portal-table">
                        <thead>
                            <tr>
                                <th>الامتحان</th>
                                <th>المادة</th>
                                <th>الموعد</th>
                                <th>الحالة</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($exams as $exam)
                            @php $grade = $grades[$exam->id] ?? null; @endphp
                            <tr>
                                <td><strong>{{ $exam->title }}</strong></td>
                                <td>{{ $exam->registrableSubject?->name }}</td>
                                <td>{{ $exam->starts_at?->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($grade)
                                        <span class="exam-badge exam-badge--{{ $grade->isPassed() ? 'success' : 'danger' }}">
                                            {{ $grade->resultLabel() }}
                                            — {{ number_format((float) $grade->percentage, 1) }}%
                                            ({{ number_format((float) $grade->raw_score, 1) }}/{{ number_format((float) $grade->max_score, 1) }})
                                        </span>
                                        @if($grade->isPublished())
                                            <span class="exam-badge exam-badge--info" style="margin-inline-start:4px;">منشورة</span>
                                        @endif
                                    @else
                                        <span class="exam-badge exam-badge--info">{{ config('exams.exam_statuses')[$exam->status] ?? $exam->status }}</span>
                                    @endif
                                </td>
                                <td><a href="{{ route('student.exams.show', $exam) }}" class="btn btn-secondary btn-sm">عرض</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="exam-portal-empty">لا توجد امتحانات متاحة حالياً.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div style="margin-top:16px;">{{ $exams->links() }}</div>
            </div>
        </section>
    </main>
</div>
@endsection
