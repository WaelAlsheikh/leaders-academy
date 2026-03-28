@extends('voyager::master')

@section('content')
<div class="container-fluid">
    <h1 class="page-title">
        <i class="voyager-archive"></i> الدورات المؤرشفة
    </h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div style="margin-bottom:15px;">
        <a href="{{ route('admin.enrollment_cycles.index') }}" class="btn btn-default">
            العودة إلى إدارة الدورات
        </a>
    </div>

    <div class="panel panel-bordered">
        <div class="panel-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>الكيان</th>
                        <th>النوع</th>
                        <th>الدورة</th>
                        <th>الحالة الحالية</th>
                        <th>تاريخ الأرشفة</th>
                        <th>أرشفت بواسطة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cycles as $cycle)
                        <tr>
                            <td>{{ $cycle->registrableEntity?->display_title }}</td>
                            <td>{{ $cycle->registrableEntity?->entity_type }}</td>
                            <td>{{ $cycle->name }}</td>
                            <td>{{ $cycle->status }}</td>
                            <td>{{ optional($cycle->archiveRecord?->archived_at)->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $cycle->archiveRecord?->archivedBy?->name ?? '—' }}</td>
                            <td>
                                <a href="{{ route('admin.archived_enrollment_cycles.show', $cycle) }}" class="btn btn-sm btn-primary">
                                    عرض التفاصيل
                                </a>
                                <form method="POST" action="{{ route('admin.archived_enrollment_cycles.restore', $cycle) }}" style="display:inline-block;" onsubmit="return confirm('هل تريد استعادة هذه الدورة إلى صفحة إدارة دورات التسجيل؟');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        استعادة الدورة
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.archived_enrollment_cycles.destroy', $cycle) }}" style="display:inline-block;" onsubmit="return confirm('سيتم حذف الدورة نهائياً مع كل التوابع المرتبطة بها. هل تريد المتابعة؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        حذف الدورة
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد دورات مؤرشفة</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
