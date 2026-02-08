<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Track;

class TrackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tracks = [
            [
                'name'            => 'مسار الحفظ والمراجعة',
                'target_group'    => 'لجميع المستويات',
                'marketing_value' => 'الأكثر طلباً',
                'description'     => 'برنامج مخصص لحفظ القرآن الكريم كاملاً أو أجزاء منه، مع خطة مراجعة دورية لضمان تثبيت الحفظ وعدم النسيان.',
                'status'          => 'active',
            ],
            [
                'name'            => 'مسار تصحيح التلاوة',
                'target_group'    => 'المبتدئين',
                'marketing_value' => 'تحسين الأداء',
                'description'     => 'جلسات عملية للتركيز على مخارج الحروف الصحيحة وتطبيق أحكام التجويد أثناء القراءة من المصحف.',
                'status'          => 'active',
            ],
            [
                'name'            => 'مسار الإجازة بالسند',
                'target_group'    => 'الحفاظ المتقنين',
                'marketing_value' => 'شهادة عالمية',
                'description'     => 'قراءة ختمة كاملة غيباً على شيخ مجاز بالسند المتصل إلى النبي ﷺ، ومنح الطالب الإجازة بعد الإتقان.',
                'status'          => 'active',
            ],
            [
                'name'            => 'مسار البراعم (نور البيان)',
                'target_group'    => 'الأطفال (5 - 12 سنة)',
                'marketing_value' => 'تأسيس لغوي',
                'description'     => 'تعليم القراءة العربية الصحيحة (القاعدة النورانية) وتحفيظ قصار السور بأسلوب تفاعلي محبب للأطفال.',
                'status'          => 'active',
            ],
            [
                'name'            => 'مسار المقامات الصوتية',
                'target_group'    => 'أصحاب الأصوات الحسنة',
                'marketing_value' => 'تحسين الصوت',
                'description'     => 'تعلم المقامات الصوتية العربية لتحسين الأداء الصوتي في تلاوة القرآن الكريم وتزيين الصوت بها.',
                'status'          => 'active',
            ],
        ];

        foreach ($tracks as $track) {
            Track::create($track);
        }
    }
}
