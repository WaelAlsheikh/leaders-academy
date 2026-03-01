@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:900px;margin:auto;">

            <h3 style="margin-bottom:20px;">📝 تسجيل جديد</h3>

            @if($colleges->isEmpty())
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا توجد دورات تسجيل مفتوحة حالياً
                </div>
            @else
            <form method="POST" action="{{ route('student.registration.store') }}">
                @csrf

                {{-- اختيار الكلية --}}
                <div style="margin-bottom:20px;">
                    <label class="form-label">اختر الكلية</label>
                    <select id="collegeSelect" name="college_id" class="form-control" required>
                        <option value="">-- اختر --</option>
                        @foreach($colleges as $college)
                            <option value="{{ $college->id }}"
                                    data-price="{{ $college->price_per_credit_hour }}">
                                {{ $college->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- المواد --}}
                <div id="subjectsBox" style="display:none;margin-top:25px;">
                    <h4 style="margin-bottom:15px;">
                        اختر المواد <small>({{ $minSubjects }} مواد على الأقل)</small>
                    </h4>

                    @foreach($colleges as $college)
                        <div class="college-subjects"
                             data-college="{{ $college->id }}"
                             style="display:none;padding:15px;border:1px solid #ddd;border-radius:6px;">

                            @php
                                $subjectsForCollege = $collegeSubjects[$college->id] ?? collect();
                            @endphp

                            @if($subjectsForCollege->isEmpty())
                                <div style="color:#888;">لا توجد مواد متاحة لهذه الكلية حالياً</div>
                            @endif

                            @foreach($subjectsForCollege as $subject)
                                <label style="display:block;margin-bottom:10px;">
                                    <input type="checkbox"
                                           class="subject-checkbox"
                                           name="subjects[]"
                                           value="{{ $subject->id }}"
                                           data-hours="{{ $subject->credit_hours }}">
                                    {{ $subject->name }}
                                    <small class="text-muted">
                                        ({{ $subject->credit_hours }} ساعات)
                                    </small>
                                </label>
                            @endforeach

                        </div>
                    @endforeach
                </div>

                {{-- تنبيه الحد الأدنى --}}
                <div id="minWarning"
                     style="margin-top:15px;color:red;display:none;">
                    ⚠️ يجب اختيار {{ $minSubjects }} مواد على الأقل
                </div>

                {{-- ملخص التكلفة --}}
                <div id="pricingBox"
                     style="margin-top:20px;padding:15px;border:1px solid #eee;border-radius:6px;display:none;">
                    <div style="margin-bottom:8px;">
                        سعر الساعة: <strong id="pricePerHourText">$0.00</strong>
                    </div>
                    <div style="margin-bottom:8px;">
                        مجموع الساعات: <strong id="totalHoursText">0</strong>
                    </div>
                    <div style="margin-bottom:8px;">
                        المجموع الجزئي: <strong id="subtotalText">$0.00</strong>
                    </div>
                    <div style="margin-bottom:8px;">
                        رسم التسجيل: <strong id="registrationFeeText">$0.00</strong>
                    </div>
                    <div>
                        المجموع الكلي: <strong id="totalAmountText">$0.00</strong>
                    </div>
                </div>

                <button type="submit"
                        id="submitBtn"
                        class="btn btn-primary"
                        style="margin-top:25px;"
                        disabled>
                    متابعة
                </button>

            </form>
            @endif

        </div>
    </main>
</div>

{{-- JavaScript --}}
<script>
    const collegeSelect = document.getElementById('collegeSelect');
    const subjectsBox = document.getElementById('subjectsBox');
    const submitBtn = document.getElementById('submitBtn');
    const minWarning = document.getElementById('minWarning');

    const minSubjects = {{ $minSubjects }};
    const registrationFee = {{ $registrationFee }};
    const pricingBox = document.getElementById('pricingBox');
    const pricePerHourText = document.getElementById('pricePerHourText');
    const totalHoursText = document.getElementById('totalHoursText');
    const subtotalText = document.getElementById('subtotalText');
    const registrationFeeText = document.getElementById('registrationFeeText');
    const totalAmountText = document.getElementById('totalAmountText');

    function updateSubmitState() {
        const checked = document.querySelectorAll('.subject-checkbox:checked').length;

        if (checked >= minSubjects) {
            submitBtn.disabled = false;
            minWarning.style.display = 'none';
        } else {
            submitBtn.disabled = true;
            minWarning.style.display = 'block';
        }
    }

    function updatePricing() {
        const selectedOption = collegeSelect.options[collegeSelect.selectedIndex];
        const pricePerHour = selectedOption?.dataset?.price
            ? parseFloat(selectedOption.dataset.price)
            : 0;

        const checkedSubjects = document.querySelectorAll('.subject-checkbox:checked');
        let totalHours = 0;
        checkedSubjects.forEach(cb => {
            const hours = parseInt(cb.dataset.hours || '0', 10);
            totalHours += isNaN(hours) ? 0 : hours;
        });

        const subtotal = totalHours * pricePerHour;
        const total = subtotal + registrationFee;

        pricePerHourText.textContent = `$${pricePerHour.toFixed(2)}`;
        totalHoursText.textContent = totalHours;
        subtotalText.textContent = `$${subtotal.toFixed(2)}`;
        registrationFeeText.textContent = `$${registrationFee.toFixed(2)}`;
        totalAmountText.textContent = `$${total.toFixed(2)}`;

        pricingBox.style.display = selectedOption?.value ? 'block' : 'none';
    }

    if (collegeSelect) {
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

            updatePricing();
        });

        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('subject-checkbox')) {
                updateSubmitState();
                updatePricing();
            }
        });

        updatePricing();
    }
</script>
@endsection
