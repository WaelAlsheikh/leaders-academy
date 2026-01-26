@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:900px;margin:auto;">

            <h3 style="margin-bottom:20px;">📝 تسجيل جديد</h3>

            <form method="POST" action="{{ route('student.registration.store') }}">
                @csrf

                {{-- اختيار الكلية --}}
                <div style="margin-bottom:20px;">
                    <label class="form-label">اختر الكلية</label>
                    <select id="collegeSelect" class="form-control" required>
                        <option value="">-- اختر --</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}">
                                {{ $college->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- المواد --}}
                <div id="subjectsBox" style="display:none;margin-top:25px;">
                    <h4 style="margin-bottom:15px;">
                        اختر المواد <small>(4 مواد على الأقل)</small>
                    </h4>

                    @foreach($colleges as $college)
                        <div class="college-subjects"
                             data-college="{{ $college->id }}"
                             style="display:none;padding:15px;border:1px solid #ddd;border-radius:6px;">

                            @foreach($college->subjects as $subject)
                                <label style="display:block;margin-bottom:10px;">
                                    <input type="checkbox"
                                           class="subject-checkbox"
                                           name="subjects[]"
                                           value="{{ $subject->id }}">
                                    {{ $subject->title }}
                                    <small class="text-muted">
                                        ({{ $subject->hours }} ساعات)
                                    </small>
                                </label>
                            @endforeach

                        </div>
                    @endforeach
                </div>

                {{-- تنبيه الحد الأدنى --}}
                <div id="minWarning"
                     style="margin-top:15px;color:red;display:none;">
                    ⚠️ يجب اختيار 4 مواد على الأقل
                </div>

                <button type="submit"
                        id="submitBtn"
                        class="btn btn-primary"
                        style="margin-top:25px;"
                        disabled>
                    متابعة
                </button>

            </form>

        </div>
    </main>
</div>

{{-- JavaScript --}}
<script>
    const collegeSelect = document.getElementById('collegeSelect');
    const subjectsBox = document.getElementById('subjectsBox');
    const submitBtn = document.getElementById('submitBtn');
    const minWarning = document.getElementById('minWarning');

    function updateSubmitState() {
        const checked = document.querySelectorAll('.subject-checkbox:checked').length;

        if (checked >= 4) {
            submitBtn.disabled = false;
            minWarning.style.display = 'none';
        } else {
            submitBtn.disabled = true;
            minWarning.style.display = 'block';
        }
    }

    collegeSelect.addEventListener('change', function () {
        document.querySelectorAll('.college-subjects')
            .forEach(el => el.style.display = 'none');

        document.querySelectorAll('.subject-checkbox')
            .forEach(cb => cb.checked = false);

        submitBtn.disabled = true;
        minWarning.style.display = 'none';

        if (this.value) {
            subjectsBox.style.display = 'block';
            const box = document.querySelector(
                `.college-subjects[data-college="${this.value}"]`
            );
            if (box) box.style.display = 'block';
        } else {
            subjectsBox.style.display = 'none';
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('subject-checkbox')) {
            updateSubmitState();
        }
    });
</script>
@endsection
