<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Industry;
use Illuminate\Database\Seeder;

class IndustrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $industries = [
            ['name' => '科技資訊', 'slug' => 'technology', 'description' => '軟體開發、IT服務、網路科技等'],
            ['name' => '金融服務', 'slug' => 'finance', 'description' => '銀行、保險、投資理財等'],
            ['name' => '製造業', 'slug' => 'manufacturing', 'description' => '電子、機械、化工等製造產業'],
            ['name' => '醫療健康', 'slug' => 'healthcare', 'description' => '醫療服務、生技製藥、健康照護等'],
            ['name' => '教育培訓', 'slug' => 'education', 'description' => '學校、補習班、線上教育等'],
            ['name' => '零售批發', 'slug' => 'retail', 'description' => '電商、零售門市、批發貿易等'],
            ['name' => '房地產', 'slug' => 'real-estate', 'description' => '房屋仲介、建設開發、物業管理等'],
            ['name' => '餐飲服務', 'slug' => 'food-beverage', 'description' => '餐廳、飲料店、食品加工等'],
            ['name' => '旅遊觀光', 'slug' => 'tourism', 'description' => '旅行社、飯店、觀光景點等'],
            ['name' => '媒體娛樂', 'slug' => 'media', 'description' => '廣告、出版、影視娛樂等'],
        ];

        foreach ($industries as $industry) {
            Industry::create($industry);
        }

        $this->command->info('✓ 已新增 '.count($industries).' 個產業類別');
    }
}
