<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingProgram;

class TrainingProgramsSeeder extends Seeder
{
    public function run()
    {
        TrainingProgram::create([
            'title' => 'كورسات تنمية بشرية - تطوير الذات والمهارات',
            'slug' => 'self-development',
            'short_description' => 'مجموعة من الدورات المتخصصة في تطوير الذات والمهارات الشخصية والقيادية.',
            'long_description' => '<p>تقدم كورسات التنمية البشرية ... (المحتوى التفصيلي الذي زودتك به)</p>',
            'category' => 'تنمية بشرية',
            'duration' => '4 أسابيع',
            'certificate' => 'شهادة معتمدة من معهد ليدرز',
            'image' => null,
        ]);

        TrainingProgram::create([
            'title' => 'كورسات مهنية - مهارات سوق العمل',
            'slug' => 'professional-skills',
            'short_description' => 'دورات عملية تركز على المهارات المهنية المطلوبة في سوق العمل.',
            'long_description' => '<p>دورات مهنية عملية تغطي ...</p>',
            'category' => 'مهنية',
            'duration' => '6 أسابيع',
            'certificate' => 'شهادة معتمدة',
            'image' => null,
        ]);
    }
}
