@extends('layouts.app')

@section('content')
<div class="student-layout">

    {{-- Sidebar --}}
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:900px;margin:auto;">

            <h3 style="margin-bottom:20px;">📝 تسجيل جديد</h3>

            @php
                $allEntitiesCount = ($entitiesByType['college']->count() ?? 0)
                    + ($entitiesByType['program_branch']->count() ?? 0)
                    + ($entitiesByType['training_program_branch']->count() ?? 0);
                $defaultType = 'college';
            @endphp

            @if($allEntitiesCount === 0)
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا توجد دورات تسجيل مفتوحة حالياً
                </div>
            @else
            <form method="POST" action="{{ route('student.registration.store') }}">
                @csrf

                <div class="registration-tabs">
                    <button type="button" class="reg-tab @if($defaultType === 'college') active @endif" data-type="college">الكليات</button>
                    <button type="button" class="reg-tab @if($defaultType === 'program_branch') active @endif" data-type="program_branch">البرامج الجامعية</button>
                    <button type="button" class="reg-tab @if($defaultType === 'training_program_branch') active @endif" data-type="training_program_branch">البرامج التدريبية</button>
                </div>

                <input type="hidden" name="entity_type" id="entityTypeInput" value="{{ $defaultType }}">

                {{-- اختيار الكيان --}}
                <div style="margin-bottom:20px;">
                    <label class="form-label">اختر خيار التسجيل</label>
                    <select id="entitySelect" name="registrable_entity_id" class="form-control" required>
                        <option value="">-- اختر --</option>
                        @foreach(['college', 'program_branch', 'training_program_branch'] as $type)
                            @foreach($entitiesByType[$type] as $entity)
                                <option
                                    value="{{ $entity->id }}"
                                    data-type="{{ $type }}"
                                    data-price="{{ $entity->price_per_credit_hour }}"
                                    style="display:none;">
                                    {{ $entity->title_snapshot }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>

                {{-- المواد --}}
                <div id="subjectsBox" style="display:none;margin-top:25px;">
                    <h4 style="margin-bottom:15px;">
                        اختر المواد <small>({{ $minSubjects }} مواد على الأقل)</small>
                    </h4>

                    @foreach(['college', 'program_branch', 'training_program_branch'] as $type)
                        @foreach($entitiesByType[$type] as $entity)
                        <div class="college-subjects"
                             data-entity="{{ $entity->id }}"
                             style="display:none;padding:15px;border:1px solid #ddd;border-radius:6px;">

                            @php
                                $subjectsForCollege = $entitySubjects[$entity->id] ?? collect();
                            @endphp

                            @if($subjectsForCollege->isEmpty())
                                <div style="color:#888;">لا توجد مواد متاحة لهذا الخيار حالياً</div>
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

<style>
    .registration-tabs {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .registration-tabs .reg-tab {
        border: 1px solid #d1d5db;
        background: #fff;
        color: #374151;
        border-radius: 999px;
        padding: 8px 16px;
        font-weight: 600;
        transition: all .15s ease-in-out;
    }

    .registration-tabs .reg-tab:hover {
        border-color: #f2b800;
        color: #111827;
    }

    .registration-tabs .reg-tab.active {
        background: #f2b800;
        border-color: #f2b800;
        color: #111827;
        box-shadow: 0 2px 8px rgba(242, 184, 0, .28);
    }

    @media (max-width: 700px) {
        .registration-tabs {
            gap: 8px;
        }

        .registration-tabs .reg-tab {
            flex: 1 1 100%;
            width: 100%;
            text-align: center;
        }
    }
</style>

{{-- JavaScript --}}
<script>
    const entitySelect = document.getElementById('entitySelect');
    const entityTypeInput = document.getElementById('entityTypeInput');
    const tabs = document.querySelectorAll('.reg-tab');
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
        const selectedOption = entitySelect.options[entitySelect.selectedIndex];
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

    function refreshEntityOptions(activeType) {
        entityTypeInput.value = activeType;
        Array.from(entitySelect.options).forEach(option => {
            if (!option.value) return;
            const visible = option.dataset.type === activeType;
            option.style.display = visible ? '' : 'none';
            if (!visible && option.selected) {
                option.selected = false;
            }
        });
        entitySelect.value = '';
        subjectsBox.style.display = 'none';
        document.querySelectorAll('.college-subjects').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
        submitBtn.disabled = true;
        minWarning.style.display = 'none';
        updatePricing();
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            refreshEntityOptions(this.dataset.type);
        });
    });

    if (entitySelect) {
        refreshEntityOptions('{{ $defaultType }}');

        entitySelect.addEventListener('change', function () {
            document.querySelectorAll('.college-subjects')
                .forEach(el => el.style.display = 'none');

            document.querySelectorAll('.subject-checkbox')
                .forEach(cb => cb.checked = false);

            submitBtn.disabled = true;
            minWarning.style.display = 'none';

            if (this.value) {
                subjectsBox.style.display = 'block';
                const box = document.querySelector(
                    `.college-subjects[data-entity="${this.value}"]`
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
