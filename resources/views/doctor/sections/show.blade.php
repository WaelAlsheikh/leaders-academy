@extends('layouts.app')

@section('hide-navbar', '1')
@section('body-class', 'doctor-shell')

@section('content')
<div class="student-layout">
    @include('doctor.partials.sidebar')

    <main class="student-content doctor-portal">
        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <div>
                    <h3>{{ $section->registrableSubject?->name ?? 'المادة' }}</h3>
                    <p class="doctor-portal-meta">
                        {{ $section->semester?->enrollmentCycle?->registrableEntity?->display_title ?? $section->registrableSubject?->registrableEntity?->display_title ?? '—' }}
                        — {{ $section->semester?->enrollmentCycle?->name ?? '—' }}
                        — {{ $section->semester?->name ?? '—' }}
                        — الشعبة {{ $section->name }}
                    </p>
                </div>
                @if($section->semester?->enrollmentCycle?->is_archived)
                    <span class="doctor-status-badge archived">الدورة مؤرشفة</span>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            @include('doctor.partials.shared-lecture-badge', ['sharedLectureLabel' => $sharedLectureLabel ?? null])

            <div class="doctor-section-info-grid">
                <div><strong>الأستاذ:</strong> {{ $section->doctor?->full_name ?? '—' }}</div>
                <div><strong>طريقة الحضور:</strong> {{ $section->mode === 'online' ? 'أونلاين' : 'حضوري' }}</div>
                <div><strong>عدد الطلاب:</strong> {{ $section->students->count() }}</div>
            </div>

            <form method="POST" action="{{ route('doctor.sections.next_link', $section) }}" class="doctor-next-link-form">
                @csrf
                @method('PUT')
                <label>رابط الجلسة القادمة</label>
                <input type="url"
                       name="zoom_url"
                       value="{{ $section->zoom_url }}"
                       class="form-control"
                       placeholder="https://..."
                       @disabled($section->semester?->enrollmentCycle?->is_archived)>
                @if($section->semester?->enrollmentCycle?->is_archived)
                    <p style="margin:8px 0 0;color:#856404;">الدورة مؤرشفة، لذلك لا يمكن تعديل الرابط.</p>
                @else
                    <button type="submit" class="btn btn-primary" style="margin-top:10px;">حفظ الرابط</button>
                @endif
            </form>
        </section>

        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <h3>الجلسات</h3>
            </div>
            @if($section->meetings->isEmpty())
                <div class="doctor-portal-empty doctor-portal-empty-inline">
                    لا توجد جلسات مضبوطة لهذه الشعبة بعد.
                </div>
            @else
                <div class="doctor-meeting-list">
                    @foreach($section->meetings as $meeting)
                        <div class="doctor-meeting-item">
                            <strong>
                                @switch($meeting->day_of_week)
                                    @case(0) الأحد @break
                                    @case(1) الاثنين @break
                                    @case(2) الثلاثاء @break
                                    @case(3) الأربعاء @break
                                    @case(4) الخميس @break
                                    @case(5) الجمعة @break
                                    @case(6) السبت @break
                                @endswitch
                            </strong>
                            <span>{{ substr($meeting->starts_at, 0, 5) }} - {{ substr($meeting->ends_at, 0, 5) }}</span>
                            <span>{{ $meeting->start_date }} @if($meeting->end_date) حتى {{ $meeting->end_date }} @endif</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="doctor-portal-panel">
            <div class="doctor-portal-panel-head">
                <h3>الطلاب المسجلون</h3>
            </div>
            @if($section->students->isEmpty())
                <div class="doctor-portal-empty doctor-portal-empty-inline">
                    لا يوجد طلاب ضمن هذه الشعبة حالياً.
                </div>
            @else
                <div class="doctor-students-table-wrap">
                    <table class="doctor-students-table">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>اسم المستخدم</th>
                                <th>البريد الإلكتروني</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($section->students as $student)
                                <tr>
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td>{{ $student->username }}</td>
                                    <td>{{ $student->email }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
