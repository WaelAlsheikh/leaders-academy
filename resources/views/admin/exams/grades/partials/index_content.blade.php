<div class="page-content container-fluid">
    <div class="employee-management-panel doctor-portal-panel">
        <h3>درجات الامتحانات</h3>
        <table class="doctor-students-table">
            <thead><tr><th>الطالب</th><th>الامتحان</th><th>الدرجة</th><th>الحالة</th><th></th></tr></thead>
            <tbody>
            @forelse($grades as $grade)
                <tr>
                    <td>{{ $grade->student?->first_name }} {{ $grade->student?->last_name }}</td>
                    <td>{{ $grade->exam?->title }}</td>
                    <td>{{ $grade->raw_score }} / {{ $grade->max_score }}</td>
                    <td>{{ config('exams.grade_statuses')[$grade->status] ?? $grade->status }}</td>
                    <td>
                        @if($grade->status !== 'published')
                            <form method="POST" action="{{ route($routeBase . '.exam_grades.approve', $grade) }}" style="display:inline">@csrf<button class="btn btn-secondary btn-sm">اعتماد</button></form>
                            <form method="POST" action="{{ route($routeBase . '.exam_grades.publish', $grade) }}" style="display:inline">@csrf<button class="btn btn-primary btn-sm">نشر</button></form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;">لا توجد درجات.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $grades->links() }}
    </div>
</div>
