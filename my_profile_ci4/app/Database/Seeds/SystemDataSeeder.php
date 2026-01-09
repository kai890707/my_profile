<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SystemDataSeeder extends Seeder
{
    public function run()
    {
        // 產業類別 (Industries)
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
            $industry['created_at'] = date('Y-m-d H:i:s');
            $industry['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('industries')->insert($industry);
        }

        echo "✓ 已新增 " . count($industries) . " 個產業類別\n";

        // 地區 (Regions)
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
            $region['created_at'] = date('Y-m-d H:i:s');
            $region['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('regions')->insert($region);
        }

        echo "✓ 已新增 " . count($regions) . " 個地區\n";

        // 測試 Admin 帳號 (Users)
        $adminData = [
            'username'          => 'admin',
            'email'             => 'admin@example.com',
            'password_hash'     => password_hash('admin123', PASSWORD_BCRYPT),
            'role'              => 'admin',
            'status'            => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($adminData);
        echo "✓ 已建立測試 Admin 帳號 (帳號: admin, 密碼: admin123)\n";

        // 測試業務員帳號
        $salespersonData = [
            'username'          => 'salesperson_test',
            'email'             => 'salesperson@example.com',
            'password_hash'     => password_hash('test123', PASSWORD_BCRYPT),
            'role'              => 'salesperson',
            'status'            => 'active',
            'email_verified_at' => date('Y-m-d H:i:s'),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($salespersonData);
        $salespersonUserId = $this->db->insertID();
        echo "✓ 已建立測試業務員帳號 (帳號: salesperson_test, 密碼: test123)\n";

        // 測試業務員資料
        $salespersonProfileData = [
            'user_id'          => $salespersonUserId,
            'company_id'       => null,
            'full_name'        => '測試業務員',
            'phone'            => '0912345678',
            'bio'              => '這是一個測試業務員帳號，用於開發與測試。',
            'specialties'      => '軟體銷售, 系統整合',
            'service_regions'  => json_encode(['台北市', '新北市']),
            'approval_status'  => 'approved',
            'approved_by'      => 1, // admin user id
            'approved_at'      => date('Y-m-d H:i:s'),
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        $this->db->table('salesperson_profiles')->insert($salespersonProfileData);
        echo "✓ 已建立測試業務員資料\n";

        echo "\n=== SystemDataSeeder 執行完成 ===\n";
        echo "產業類別: " . count($industries) . " 筆\n";
        echo "地區: " . count($regions) . " 筆\n";
        echo "測試帳號: 2 個 (admin, salesperson_test)\n";
    }
}
