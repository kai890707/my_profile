<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Certification;
use App\Models\Company;
use App\Models\Experience;
use App\Models\SalespersonProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SalespersonTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (! $admin) {
            $this->command->error('請先執行 DatabaseSeeder 建立 admin 帳號');

            return;
        }

        $salespersons = [
            [
                'user' => [
                    'username' => 'chen_ming',
                    'name' => '陳銘哲',
                    'email' => 'chen.ming@example.com',
                ],
                'profile' => [
                    'full_name' => '陳銘哲',
                    'phone' => '0912-345-001',
                    'bio' => '擁有10年保險銷售經驗，專精於壽險與投資型保單規劃。曾獲得多次年度最佳業務員獎項，善於為客戶量身打造最適合的保障方案。',
                    'specialties' => '壽險規劃, 投資型保單, 退休規劃',
                    'service_regions' => ['台北市', '新北市'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '國泰人壽',
                    'tax_id' => '12345678',
                ],
                'experiences' => [
                    ['company' => '國泰人壽', 'position' => '資深業務經理', 'start_date' => '2018-03-01', 'end_date' => null, 'description' => '帶領10人團隊，年度業績達成率150%'],
                    ['company' => '富邦人壽', 'position' => '業務專員', 'start_date' => '2014-06-01', 'end_date' => '2018-02-28', 'description' => '連續3年獲得年度最佳新人獎'],
                ],
                'certifications' => [
                    ['name' => '人身保險業務員資格', 'issuer' => '金融監督管理委員會', 'issue_date' => '2014-05-15'],
                    ['name' => 'CFP 國際認證理財規劃師', 'issuer' => '台灣理財規劃產業發展促進會', 'issue_date' => '2019-08-20'],
                ],
            ],
            [
                'user' => [
                    'username' => 'lin_mei',
                    'name' => '林美玲',
                    'email' => 'lin.mei@example.com',
                ],
                'profile' => [
                    'full_name' => '林美玲',
                    'phone' => '0923-456-002',
                    'bio' => '專注於企業保險與團體保險領域，服務超過50家中小企業。具備完整的風險管理顧問經驗，能為企業提供全方位的保障規劃。',
                    'specialties' => '企業保險, 團體保險, 風險管理',
                    'service_regions' => ['台中市', '彰化縣', '南投縣'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '新光人壽',
                    'tax_id' => '23456789',
                ],
                'experiences' => [
                    ['company' => '新光人壽', 'position' => '企業保險部主管', 'start_date' => '2016-01-15', 'end_date' => null, 'description' => '負責中部地區企業客戶開發與維護'],
                ],
                'certifications' => [
                    ['name' => '產物保險業務員資格', 'issuer' => '金融監督管理委員會', 'issue_date' => '2015-03-10'],
                    ['name' => '企業風險管理師', 'issuer' => '中華民國風險管理學會', 'issue_date' => '2020-11-05'],
                ],
            ],
            [
                'user' => [
                    'username' => 'wang_jian',
                    'name' => '王建宏',
                    'email' => 'wang.jian@example.com',
                ],
                'profile' => [
                    'full_name' => '王建宏',
                    'phone' => '0934-567-003',
                    'bio' => '汽車銷售專家，在賓士品牌服務超過8年。熱愛汽車，能為客戶提供專業的購車建議與完善的售後服務。',
                    'specialties' => '豪華汽車銷售, 企業購車方案, 租賃服務',
                    'service_regions' => ['台北市', '桃園市'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '台灣賓士',
                    'tax_id' => '34567890',
                ],
                'experiences' => [
                    ['company' => '台灣賓士', 'position' => '銷售顧問', 'start_date' => '2017-05-01', 'end_date' => null, 'description' => '年度銷售量達60台，連續5年達成銷售目標'],
                    ['company' => 'BMW 總代理', 'position' => '業務代表', 'start_date' => '2013-08-01', 'end_date' => '2017-04-30', 'description' => '負責企業客戶與VIP客戶服務'],
                ],
                'certifications' => [
                    ['name' => 'Mercedes-Benz 原廠銷售認證', 'issuer' => 'Mercedes-Benz AG', 'issue_date' => '2017-08-15'],
                ],
            ],
            [
                'user' => [
                    'username' => 'zhang_hui',
                    'name' => '張慧如',
                    'email' => 'zhang.hui@example.com',
                ],
                'profile' => [
                    'full_name' => '張慧如',
                    'phone' => '0945-678-004',
                    'bio' => '房地產銷售顧問，專精於預售屋與新成屋市場。對台北市各區域房市瞭若指掌，能為客戶找到最適合的房產投資標的。',
                    'specialties' => '預售屋銷售, 投資置產, 房市分析',
                    'service_regions' => ['台北市', '新北市', '基隆市'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '信義房屋',
                    'tax_id' => '45678901',
                ],
                'experiences' => [
                    ['company' => '信義房屋', 'position' => '資深經紀人', 'start_date' => '2015-02-01', 'end_date' => null, 'description' => '專精於信義區與大安區高端住宅市場'],
                ],
                'certifications' => [
                    ['name' => '不動產經紀人', 'issuer' => '內政部', 'issue_date' => '2014-11-20'],
                    ['name' => '不動產估價師', 'issuer' => '考選部', 'issue_date' => '2018-06-15'],
                ],
            ],
            [
                'user' => [
                    'username' => 'liu_wei',
                    'name' => '劉威志',
                    'email' => 'liu.wei@example.com',
                ],
                'profile' => [
                    'full_name' => '劉威志',
                    'phone' => '0956-789-005',
                    'bio' => '軟體解決方案銷售專家，具備深厚的技術背景。專注於企業數位轉型與雲端解決方案，協助客戶提升營運效率。',
                    'specialties' => '企業軟體, 雲端服務, 數位轉型',
                    'service_regions' => ['台北市', '新竹市', '新竹縣'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '微軟台灣',
                    'tax_id' => '56789012',
                ],
                'experiences' => [
                    ['company' => '微軟台灣', 'position' => '解決方案銷售經理', 'start_date' => '2019-04-01', 'end_date' => null, 'description' => '負責金融業與製造業大型客戶'],
                    ['company' => 'Oracle 台灣', 'position' => '業務代表', 'start_date' => '2015-07-01', 'end_date' => '2019-03-31', 'description' => '雲端資料庫解決方案銷售'],
                ],
                'certifications' => [
                    ['name' => 'Microsoft Certified: Azure Solutions Architect', 'issuer' => 'Microsoft', 'issue_date' => '2020-02-10'],
                    ['name' => 'AWS Certified Solutions Architect', 'issuer' => 'Amazon Web Services', 'issue_date' => '2021-05-22'],
                ],
            ],
            [
                'user' => [
                    'username' => 'huang_yi',
                    'name' => '黃怡萱',
                    'email' => 'huang.yi@example.com',
                ],
                'profile' => [
                    'full_name' => '黃怡萱',
                    'phone' => '0967-890-006',
                    'bio' => '珠寶銷售顧問，對寶石鑑定與珠寶設計有深入研究。服務於頂級珠寶品牌，為客戶提供專屬的珠寶選購體驗。',
                    'specialties' => '珠寶鑑定, 訂製珠寶, VIP服務',
                    'service_regions' => ['台北市'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => 'Cartier 台灣',
                    'tax_id' => '67890123',
                ],
                'experiences' => [
                    ['company' => 'Cartier 台灣', 'position' => '資深銷售顧問', 'start_date' => '2018-09-01', 'end_date' => null, 'description' => '負責VIP客戶關係維護與高端珠寶銷售'],
                ],
                'certifications' => [
                    ['name' => 'GIA Graduate Gemologist', 'issuer' => 'Gemological Institute of America', 'issue_date' => '2017-12-01'],
                ],
            ],
            [
                'user' => [
                    'username' => 'wu_cheng',
                    'name' => '吳承翰',
                    'email' => 'wu.cheng@example.com',
                ],
                'profile' => [
                    'full_name' => '吳承翰',
                    'phone' => '0978-901-007',
                    'bio' => '醫療器材銷售專員，具備醫學背景。專注於手術器械與醫療設備，服務各大醫療院所，提供專業的產品諮詢與技術支援。',
                    'specialties' => '醫療器材, 手術器械, 醫院採購',
                    'service_regions' => ['台北市', '新北市', '桃園市', '台中市'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '嬌生醫療器材',
                    'tax_id' => '78901234',
                ],
                'experiences' => [
                    ['company' => '嬌生醫療器材', 'position' => '產品專員', 'start_date' => '2020-01-15', 'end_date' => null, 'description' => '負責北部地區醫學中心業務開發'],
                ],
                'certifications' => [
                    ['name' => '醫療器材技術人員', 'issuer' => '衛生福利部', 'issue_date' => '2019-08-30'],
                ],
            ],
            [
                'user' => [
                    'username' => 'yang_xin',
                    'name' => '楊欣怡',
                    'email' => 'yang.xin@example.com',
                ],
                'profile' => [
                    'full_name' => '楊欣怡',
                    'phone' => '0989-012-008',
                    'bio' => '旅遊業務專家，擁有豐富的國內外旅遊規劃經驗。專精於客製化行程設計，為客戶打造獨一無二的旅行體驗。',
                    'specialties' => '客製旅遊, 蜜月規劃, 企業旅遊',
                    'service_regions' => ['高雄市', '台南市', '屏東縣'],
                    'approval_status' => 'approved',
                ],
                'company' => [
                    'name' => '雄獅旅遊',
                    'tax_id' => '89012345',
                ],
                'experiences' => [
                    ['company' => '雄獅旅遊', 'position' => '旅遊顧問', 'start_date' => '2016-06-01', 'end_date' => null, 'description' => '專精日本與歐洲客製行程規劃'],
                    ['company' => '可樂旅遊', 'position' => '業務專員', 'start_date' => '2012-03-01', 'end_date' => '2016-05-31', 'description' => '團體旅遊銷售與客戶服務'],
                ],
                'certifications' => [
                    ['name' => '旅行業經理人', 'issuer' => '交通部觀光署', 'issue_date' => '2015-09-10'],
                    ['name' => '導遊人員執業證', 'issuer' => '交通部觀光署', 'issue_date' => '2013-04-20'],
                ],
            ],
            [
                'user' => [
                    'username' => 'cai_jun',
                    'name' => '蔡俊豪',
                    'email' => 'cai.jun@example.com',
                ],
                'profile' => [
                    'full_name' => '蔡俊豪',
                    'phone' => '0990-123-009',
                    'bio' => '工業設備銷售工程師，具備機械工程背景。專注於自動化設備與機械手臂，協助製造業客戶提升生產效率。',
                    'specialties' => '工業自動化, 機械手臂, 生產線規劃',
                    'service_regions' => ['台中市', '彰化縣', '雲林縣', '嘉義縣'],
                    'approval_status' => 'pending',
                ],
                'company' => [
                    'name' => '發那科台灣',
                    'tax_id' => '90123456',
                ],
                'experiences' => [
                    ['company' => '發那科台灣', 'position' => '銷售工程師', 'start_date' => '2021-03-01', 'end_date' => null, 'description' => '負責中部地區製造業客戶'],
                ],
                'certifications' => [
                    ['name' => 'FANUC 機器人認證工程師', 'issuer' => 'FANUC Corporation', 'issue_date' => '2021-06-15'],
                ],
            ],
            [
                'user' => [
                    'username' => 'xu_ting',
                    'name' => '徐婷婷',
                    'email' => 'xu.ting@example.com',
                ],
                'profile' => [
                    'full_name' => '徐婷婷',
                    'phone' => '0901-234-010',
                    'bio' => '金融理財顧問，專精於基金與股票投資。提供客戶全方位的資產配置建議，協助達成財富增值目標。',
                    'specialties' => '基金投資, 股票分析, 資產配置',
                    'service_regions' => ['台北市', '新北市'],
                    'approval_status' => 'rejected',
                    'rejected_reason' => '證照資料不完整，請補齊後重新申請',
                ],
                'company' => null,
                'experiences' => [
                    ['company' => '元大證券', 'position' => '理財專員', 'start_date' => '2022-01-10', 'end_date' => null, 'description' => '財富管理部門，服務高資產客戶'],
                ],
                'certifications' => [],
            ],
        ];

        foreach ($salespersons as $index => $data) {
            // Check if user already exists
            $existingUser = User::where('email', $data['user']['email'])->first();
            if ($existingUser) {
                $this->command->info('⏭ 跳過已存在的業務員: '.$data['profile']['full_name']);

                continue;
            }

            // Create User
            $user = User::create([
                'username' => $data['user']['username'],
                'name' => $data['user']['name'],
                'email' => $data['user']['email'],
                'password_hash' => Hash::make('password123'),
                'role' => 'salesperson',
                'status' => 'active',
                'email_verified_at' => now(),
                'salesperson_status' => $data['profile']['approval_status'],
                'salesperson_approved_at' => $data['profile']['approval_status'] === 'approved' ? now() : null,
            ]);

            // Create Company if exists
            $companyId = null;
            if ($data['company']) {
                $company = Company::firstOrCreate(
                    ['name' => $data['company']['name']],
                    [
                        'tax_id' => $data['company']['tax_id'],
                        'is_personal' => false,
                        'created_by' => $user->id,
                    ]
                );
                $companyId = $company->id;
            }

            // Create Profile
            $profileData = [
                'user_id' => $user->id,
                'company_id' => $companyId,
                'full_name' => $data['profile']['full_name'],
                'phone' => $data['profile']['phone'],
                'bio' => $data['profile']['bio'],
                'specialties' => $data['profile']['specialties'],
                'service_regions' => $data['profile']['service_regions'],
                'approval_status' => $data['profile']['approval_status'],
            ];

            if ($data['profile']['approval_status'] === 'approved') {
                $profileData['approved_by'] = $admin->id;
                $profileData['approved_at'] = now();
            }

            if (isset($data['profile']['rejected_reason'])) {
                $profileData['rejected_reason'] = $data['profile']['rejected_reason'];
            }

            SalespersonProfile::create($profileData);

            // Create Experiences
            foreach ($data['experiences'] as $exp) {
                Experience::create([
                    'user_id' => $user->id,
                    'company' => $exp['company'],
                    'position' => $exp['position'],
                    'start_date' => $exp['start_date'],
                    'end_date' => $exp['end_date'],
                    'description' => $exp['description'],
                    'approval_status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]);
            }

            // Create Certifications
            foreach ($data['certifications'] as $cert) {
                Certification::create([
                    'user_id' => $user->id,
                    'name' => $cert['name'],
                    'issuer' => $cert['issuer'],
                    'issue_date' => $cert['issue_date'],
                    'expiry_date' => null,
                    'approval_status' => 'approved',
                    'approved_by' => $admin->id,
                    'approved_at' => now(),
                ]);
            }

            $this->command->info('✓ 已建立業務員: '.$data['profile']['full_name']);
        }

        $this->command->info('');
        $this->command->info('=== 已成功建立 '.count($salespersons).' 筆業務員測試資料 ===');
        $this->command->info('測試帳號密碼皆為: password123');
    }
}
