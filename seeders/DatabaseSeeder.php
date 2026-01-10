<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Program;
use App\Models\Accreditation;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Gallery;
use App\Models\Setting;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // صفحة عن المعهد
        Page::updateOrCreate(['slug'=>'about'],[
            'title'=>'عن معهد ليدرز',
            'content'=> '<p>معهد ليدرز هو مركز متخصص في تطوير القيادات... (نص تجريبي)</p>',
            'meta_title'=>'عن معهد ليدرز',
            'meta_description'=>'نبذة عن معهد ليدرز للتدريب والتطوير.'
        ]);

        // صفحة الأكاديمية / أكسفورد
        Page::updateOrCreate(['slug'=>'oxford-partnership'],[
            'title'=>'شراكة مع جامعة أكسفورد',
            'content'=>'<p>تفاصيل الشراكة مع جامعة أكسفورد ...</p>'
        ]);

        // برامج تجريبية
        Program::create([
            'title'=>'برنامج تطوير القيادة',
            'slug'=>'leadership-development',
            'short_description'=>'برنامج مكثف لتطوير المهارات القيادية.',
            'long_description'=>'<p>وصف تفصيلي لبرنامج تطوير القيادة ...</p>',
            'duration'=>'6 أسابيع',
            'certificate'=>'شهادة معتمدة',
            'image'=>'assets/images/programs/leadership.jpg'
        ]);

        Program::create([
            'title'=>'التخطيط الاستراتيجي',
            'slug'=>'strategic-planning',
            'short_description'=>'أساسيات التخطيط الاستراتيجي.',
            'long_description'=>'<p>وصف تفصيلي لبرنامج التخطيط الاستراتيجي ...</p>',
            'duration'=>'4 أسابيع',
            'certificate'=>'شهادة معتمدة',
            'image'=>'assets/images/programs/strategic.jpg'
        ]);

        // اعتمادات
        Accreditation::create([
            'title'=>'جامعة أكسفورد البريطانية',
            'logo'=>'assets/images/accreditations/oxford.png',
            'description'=>'شراكة أكاديمية مع جامعة أكسفورد.',
            'link'=>'https://www.ox.ac.uk'
        ]);

        Accreditation::create([
            'title'=>'مركز الاعتماد المحلي',
            'logo'=>'assets/images/accreditations/local.png',
            'description'=>'اعتماد وطني للمؤهلات.',
            'link'=>'#'
        ]);

        // شركاء
        Partner::create([
            'name'=>'أكسفورد - Oxford',
            'logo'=>'assets/images/partners/oxford.png',
            'description'=>'شريك أكاديمي دولي',
            'link'=>'https://www.ox.ac.uk'
        ]);

        // معرض صور تجريبي
        Gallery::create(['title'=>'قاعة محاضرات 1','file'=>'assets/images/gallery/room1.jpg','alt'=>'قاعة 1']);
        Gallery::create(['title'=>'قاعة محاضرات 2','file'=>'assets/images/gallery/room2.jpg','alt'=>'قاعة 2']);

        // إعدادات أساسية
        Setting::set('whatsapp','+963XXXXXXXXX');
        Setting::set('phone','+963-11-XXXXXXX');
        Setting::set('address','دمشق - الكسوة');
    }
}
