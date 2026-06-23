<?php

return [
    /*
    |--------------------------------------------------------------------------
    | المحاضرات المشتركة بين الكليات
    |--------------------------------------------------------------------------
    |
    | host_college_id: الكلية المستضيفة للشعبة والجلسات المباشرة
    | subject_name / subject_names: مطابقة اسم المادة (trim)
    |
    */

    'prep_program' => [
        'name' => 'البرنامج التحضيري — محاضرة موحدة',
        'subject_name' => 'البرنامج التحضيري',
        'host_college_id' => 15, // كلية العلوم الصحية
        'default_meeting' => [
            'day_of_week' => 0, // الأحد
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
        ],
    ],

    'biochemistry_health_medicine' => [
        'name' => 'الكيمياء الحيوية — مشتركة',
        'subject_name' => 'الكيمياء الحيوية',
        'college_ids' => [15, 5], // العلوم الصحية + الطب العام
        'host_college_id' => 15,
        'study_year_sort' => 1,
        'study_term_sort' => 2,
        'copy_subject_to_colleges' => [5],
        'source_college_id' => 15,
        'default_meeting' => [
            'day_of_week' => 2, // الثلاثاء
            'starts_at' => '14:00:00',
            'ends_at' => '16:00:00',
        ],
    ],

    'medical_terminology_health_medicine' => [
        'name' => 'اللغة الطبية (المصطلحات) — مشتركة',
        'subject_name' => 'اللغة الطبية (المصطلحات)',
        // أسماء بديلة قديمة (إن وُجدت في قاعدة البيانات قبل توحيد التسمية)
        'subject_names' => [
            'اللغة الطبية / المصطلحات',
        ],
        'college_ids' => [15, 5],
        'host_college_id' => 15,
        'study_year_sort' => 1,
        'study_term_sort' => 2,
        'copy_subject_to_colleges' => [5],
        'source_college_id' => 15,
        'default_meeting' => [
            'day_of_week' => 4, // الخميس
            'starts_at' => '10:00:00',
            'ends_at' => '12:00:00',
        ],
    ],
    'vital_signs_shared' => [

    'name' => 'العلامات الحيوية - مشتركة',

    'host_college_id' => 15,

    'college_ids' => [
        15,
        5,
    ],

    'subject_name' => 'العلامات الحيوية',
    ],
    'medical_and_professional_ethics' => [

    'name' => 'الأخلاقيات الطبية والمهنية - مشتركة',

    'host_college_id' => 15,

    'college_ids' => [
        15,
        5,
    ],

    'subject_name' => 'الأخلاقيات الطبية والمهنية',
    ],
    'medicine_legal' => [

    'name' => 'القانون الطبي - مشتركة',

    'host_college_id' => 15,

    'college_ids' => [
        15,
        5,
    ],

    'subject_name' => 'القانون الطبي',
    ],
];
