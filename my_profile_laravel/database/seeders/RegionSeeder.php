<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            // 北部
            ['name' => '台北市', 'slug' => 'taipei-city', 'parent_id' => null],
            ['name' => '新北市', 'slug' => 'new-taipei-city', 'parent_id' => null],
            ['name' => '基隆市', 'slug' => 'keelung-city', 'parent_id' => null],
            ['name' => '桃園市', 'slug' => 'taoyuan-city', 'parent_id' => null],
            ['name' => '新竹市', 'slug' => 'hsinchu-city', 'parent_id' => null],
            ['name' => '新竹縣', 'slug' => 'hsinchu-county', 'parent_id' => null],

            // 中部
            ['name' => '苗栗縣', 'slug' => 'miaoli-county', 'parent_id' => null],
            ['name' => '台中市', 'slug' => 'taichung-city', 'parent_id' => null],
            ['name' => '彰化縣', 'slug' => 'changhua-county', 'parent_id' => null],
            ['name' => '南投縣', 'slug' => 'nantou-county', 'parent_id' => null],
            ['name' => '雲林縣', 'slug' => 'yunlin-county', 'parent_id' => null],

            // 南部
            ['name' => '嘉義市', 'slug' => 'chiayi-city', 'parent_id' => null],
            ['name' => '嘉義縣', 'slug' => 'chiayi-county', 'parent_id' => null],
            ['name' => '台南市', 'slug' => 'tainan-city', 'parent_id' => null],
            ['name' => '高雄市', 'slug' => 'kaohsiung-city', 'parent_id' => null],
            ['name' => '屏東縣', 'slug' => 'pingtung-county', 'parent_id' => null],

            // 東部
            ['name' => '宜蘭縣', 'slug' => 'yilan-county', 'parent_id' => null],
            ['name' => '花蓮縣', 'slug' => 'hualien-county', 'parent_id' => null],
            ['name' => '台東縣', 'slug' => 'taitung-county', 'parent_id' => null],

            // 離島
            ['name' => '澎湖縣', 'slug' => 'penghu-county', 'parent_id' => null],
            ['name' => '金門縣', 'slug' => 'kinmen-county', 'parent_id' => null],
            ['name' => '連江縣', 'slug' => 'lienchiang-county', 'parent_id' => null],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }

        $this->command->info('✓ 已新增 ' . count($regions) . ' 個地區');
    }
}
