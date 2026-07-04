<?php

return [
    'creation_modes' => ['random', 'manual'],

    'question_types' => [
        'single_choice' => 'اختيار من متعدد (إجابة واحدة)',
        'multiple_choice' => 'اختيار من متعدد (أكثر من إجابة)',
        'essay' => 'سؤال تحريري',
    ],

    'exam_statuses' => [
        'draft' => 'مسودة',
        'scheduled' => 'مجدول',
        'running' => 'جارٍ',
        'finished' => 'منتهٍ',
        'archived' => 'مؤرشف',
    ],

    'grade_statuses' => [
        'draft' => 'مسودة',
        'auto_corrected' => 'مصحح تلقائياً',
        'pending_review' => 'بانتظار المراجعة',
        'reviewed' => 'تمت المراجعة',
        'approved' => 'معتمد',
        'published' => 'منشور',
    ],

    'default_total_points' => 100,

    'autosave_interval_seconds' => (int) env('EXAM_AUTOSAVE_INTERVAL_SECONDS', 15),

    'preliminary_result_message' => 'هذه نتيجة أولية وتحتاج ما يقارب يومين حتى تتم مراجعتها واعتمادها بشكل نهائي.',
];
