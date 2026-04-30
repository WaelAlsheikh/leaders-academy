@extends('layouts.app')

@section('content')
<div class="student-layout">
    @include('student.partials.sidebar')

    <main class="student-content">
        <div class="card" style="padding:30px;max-width:1100px;margin:auto;">
            <h3 style="margin-bottom:10px;">📁 ملفات المواد</h3>
            <p style="margin-bottom:20px;color:#6b7280;">يمكنك هنا الوصول إلى الفيديوهات والملفات التي رفعها دكاترة موادك الحالية.</p>

            @if($entities->isEmpty())
                <div style="padding:20px;border:1px dashed #ccc;border-radius:6px;text-align:center;">
                    لا توجد مواد حالية تحتوي على ملفات أو فيديوهات متاحة لك الآن.
                </div>
            @else
                <form method="GET" action="{{ route('student.materials.index') }}" style="margin-bottom:24px;">
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;align-items:end;">
                        <div>
                            <label class="form-label">البرنامج</label>
                            <select id="entitySelect" name="entity" class="form-control">
                                <option value="">اختر البرنامج</option>
                                @foreach($entities as $entity)
                                    <option value="{{ $entity->id }}" @selected($selectedEntity?->id === $entity->id)>
                                        {{ $entity->display_title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">المادة</label>
                            <select id="subjectSelect" name="subject" class="form-control">
                                <option value="">اختر المادة</option>
                                @foreach($subjectsByEntity as $entityId => $subjects)
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}"
                                                data-entity="{{ $entityId }}"
                                                @selected($selectedSubject?->id === $subject->id)>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary">عرض المحتوى</button>
                        </div>
                    </div>
                </form>

                @if($selectedSubject)
                    <div style="margin-bottom:16px;padding:14px 18px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                        <strong>{{ $selectedSubject->name }}</strong>
                        <div style="margin-top:6px;color:#6b7280;">
                            {{ $selectedEntity?->display_title ?? '—' }}
                        </div>
                    </div>

                    <section style="margin-bottom:28px;">
                        <h4 style="margin-bottom:14px;">فيديوهات المادة</h4>
                        @forelse($videos as $material)
                            <div style="border:1px solid #e5e7eb;border-radius:12px;padding:18px;margin-bottom:16px;background:#fff;">
                                <div style="display:flex;justify-content:space-between;gap:12px;align-items:start;flex-wrap:wrap;margin-bottom:14px;">
                                    <div>
                                        <strong>{{ $material->title }}</strong>
                                        <div style="font-size:13px;color:#6b7280;margin-top:6px;">
                                            الدكتور: {{ $material->doctor?->full_name ?? '—' }}
                                        </div>
                                        @if($material->description)
                                            <div style="font-size:14px;color:#4b5563;margin-top:6px;">{{ $material->description }}</div>
                                        @endif
                                    </div>
                                    <a href="{{ route('student.materials.download', ['material' => $material, 'download' => 1]) }}" class="btn btn-default">
                                        تنزيل
                                    </a>
                                </div>
                                <video controls style="width:100%;max-height:420px;background:#111;border-radius:10px;">
                                    <source src="{{ route('student.materials.download', $material) }}" type="{{ $material->mime_type }}">
                                </video>
                            </div>
                        @empty
                            <div style="padding:16px;border:1px dashed #d1d5db;border-radius:8px;color:#6b7280;">
                                لا توجد فيديوهات متاحة لهذه المادة حاليًا.
                            </div>
                        @endforelse
                    </section>

                    <section>
                        <h4 style="margin-bottom:14px;">ملفات المادة</h4>
                        @forelse($files as $material)
                            <div style="display:flex;justify-content:space-between;gap:12px;align-items:start;flex-wrap:wrap;border:1px solid #e5e7eb;border-radius:12px;padding:18px;margin-bottom:14px;background:#fff;">
                                <div>
                                    <strong>{{ $material->title }}</strong>
                                    <div style="font-size:13px;color:#6b7280;margin-top:6px;">
                                        الدكتور: {{ $material->doctor?->full_name ?? '—' }}
                                    </div>
                                    <div style="font-size:13px;color:#6b7280;margin-top:4px;">
                                        {{ $material->original_name }}
                                    </div>
                                    @if($material->description)
                                        <div style="font-size:14px;color:#4b5563;margin-top:6px;">{{ $material->description }}</div>
                                    @endif
                                </div>
                                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                    <a href="{{ route('student.materials.download', $material) }}" class="btn btn-default" target="_blank">عرض</a>
                                    <a href="{{ route('student.materials.download', ['material' => $material, 'download' => 1]) }}" class="btn btn-primary">تنزيل</a>
                                </div>
                            </div>
                        @empty
                            <div style="padding:16px;border:1px dashed #d1d5db;border-radius:8px;color:#6b7280;">
                                لا توجد ملفات متاحة لهذه المادة حاليًا.
                            </div>
                        @endforelse
                    </section>
                @endif
            @endif
        </div>
    </main>
</div>

<script>
    const entitySelect = document.getElementById('entitySelect');
    const subjectSelect = document.getElementById('subjectSelect');

    function refreshSubjectOptions() {
        if (!entitySelect || !subjectSelect) {
            return;
        }

        const entityId = entitySelect.value;

        Array.from(subjectSelect.options).forEach((option, index) => {
            if (index === 0) {
                option.hidden = false;
                return;
            }

            const visible = !entityId || option.dataset.entity === entityId;
            option.hidden = !visible;

            if (!visible && option.selected) {
                option.selected = false;
            }
        });
    }

    if (entitySelect && subjectSelect) {
        entitySelect.addEventListener('change', refreshSubjectOptions);
        refreshSubjectOptions();
    }
</script>
@endsection
