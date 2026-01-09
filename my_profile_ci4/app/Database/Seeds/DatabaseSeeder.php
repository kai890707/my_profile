<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Profile
        $this->db->table('profiles')->insert([
            'name'       => '王小明',
            'title'      => '全端工程師 | 熱愛打造優質的網路應用程式',
            'bio'        => "您好！我是王小明，一位擁有 5 年軟體開發經驗的全端工程師。\n\n我專精於 Web 應用程式開發，從前端介面設計到後端系統架構都有豐富的實戰經驗。熱衷於學習新技術，追求程式碼品質與使用者體驗的完美平衡。\n\n在工作之餘，我喜歡參與開源專案、撰寫技術文章，並持續精進自己的技術能力。",
            'email'      => 'wangxiaoming@example.com',
            'github'     => 'https://github.com/xiaoming-wang',
            'linkedin'   => 'https://linkedin.com/in/xiaoming-wang',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Skills
        $skills = [
            ['category' => '前端開發', 'name' => 'HTML5', 'sort_order' => 1],
            ['category' => '前端開發', 'name' => 'CSS3', 'sort_order' => 2],
            ['category' => '前端開發', 'name' => 'JavaScript', 'sort_order' => 3],
            ['category' => '前端開發', 'name' => 'TypeScript', 'sort_order' => 4],
            ['category' => '前端開發', 'name' => 'React', 'sort_order' => 5],
            ['category' => '前端開發', 'name' => 'Vue.js', 'sort_order' => 6],
            ['category' => '後端開發', 'name' => 'Node.js', 'sort_order' => 1],
            ['category' => '後端開發', 'name' => 'Python', 'sort_order' => 2],
            ['category' => '後端開發', 'name' => 'Java', 'sort_order' => 3],
            ['category' => '後端開發', 'name' => 'Express', 'sort_order' => 4],
            ['category' => '後端開發', 'name' => 'Django', 'sort_order' => 5],
            ['category' => '資料庫', 'name' => 'MySQL', 'sort_order' => 1],
            ['category' => '資料庫', 'name' => 'PostgreSQL', 'sort_order' => 2],
            ['category' => '資料庫', 'name' => 'MongoDB', 'sort_order' => 3],
            ['category' => '資料庫', 'name' => 'Redis', 'sort_order' => 4],
            ['category' => '開發工具', 'name' => 'Git', 'sort_order' => 1],
            ['category' => '開發工具', 'name' => 'Docker', 'sort_order' => 2],
            ['category' => '開發工具', 'name' => 'AWS', 'sort_order' => 3],
            ['category' => '開發工具', 'name' => 'Linux', 'sort_order' => 4],
            ['category' => '開發工具', 'name' => 'CI/CD', 'sort_order' => 5],
        ];

        foreach ($skills as $skill) {
            $skill['created_at'] = date('Y-m-d H:i:s');
            $skill['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('skills')->insert($skill);
        }

        // Experiences
        $experiences = [
            [
                'company'     => 'ABC 科技股份有限公司',
                'position'    => '資深全端工程師',
                'start_date'  => '2022 年',
                'end_date'    => '現在',
                'description' => '負責公司核心產品的前後端開發與維護，帶領 3 人小組完成多項重要專案。優化系統效能，將 API 回應時間縮短 40%。',
                'sort_order'  => 1,
            ],
            [
                'company'     => 'XYZ 網路科技有限公司',
                'position'    => '軟體工程師',
                'start_date'  => '2019 年',
                'end_date'    => '2022 年',
                'description' => '參與電商平台開發，負責購物車、結帳流程等核心功能。使用 React 重構前端架構，提升使用者體驗與開發效率。',
                'sort_order'  => 2,
            ],
            [
                'company'     => 'DEF 軟體工作室',
                'position'    => '初級工程師',
                'start_date'  => '2018 年',
                'end_date'    => '2019 年',
                'description' => '參與多個網站開發專案，學習前後端技術，建立軟體開發的基礎能力。',
                'sort_order'  => 3,
            ],
        ];

        foreach ($experiences as $exp) {
            $exp['created_at'] = date('Y-m-d H:i:s');
            $exp['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('experiences')->insert($exp);
        }

        // Education
        $education = [
            [
                'institution' => '國立台灣大學',
                'degree'      => '資訊工程學系 學士',
                'start_date'  => '2014 年',
                'end_date'    => '2018 年',
                'description' => '主修軟體工程、資料結構與演算法，參與實驗室專題研究。',
                'type'        => 'education',
                'sort_order'  => 1,
            ],
            [
                'institution' => 'AWS Certified Solutions Architect',
                'degree'      => null,
                'start_date'  => '2023 年取得',
                'end_date'    => null,
                'description' => 'Amazon Web Services 雲端架構師認證',
                'type'        => 'certification',
                'sort_order'  => 2,
            ],
            [
                'institution' => 'Google Cloud Professional Developer',
                'degree'      => null,
                'start_date'  => '2022 年取得',
                'end_date'    => null,
                'description' => 'Google Cloud Platform 專業開發人員認證',
                'type'        => 'certification',
                'sort_order'  => 3,
            ],
        ];

        foreach ($education as $edu) {
            $edu['created_at'] = date('Y-m-d H:i:s');
            $edu['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('education')->insert($edu);
        }

        // Projects
        $projects = [
            [
                'title'       => '電商平台系統',
                'description' => '開發完整的電子商務平台，包含商品管理、購物車、結帳流程、訂單追蹤等功能。支援多種付款方式與物流整合。',
                'tech_tags'   => 'React, Node.js, PostgreSQL, Redis, AWS',
                'link'        => '#',
                'category'    => 'project',
                'sort_order'  => 1,
            ],
            [
                'title'       => '即時通訊應用',
                'description' => '建立支援即時訊息、群組聊天、檔案傳輸的通訊應用程式。採用 WebSocket 技術實現低延遲的即時通訊體驗。',
                'tech_tags'   => 'Vue.js, Socket.io, Express, MongoDB',
                'link'        => '#',
                'category'    => 'project',
                'sort_order'  => 2,
            ],
            [
                'title'       => '專案管理工具',
                'description' => '開發類似 Trello 的專案管理工具，支援看板式任務管理、拖放排序、團隊協作、進度追蹤等功能。',
                'tech_tags'   => 'React, TypeScript, Python, Django, PostgreSQL',
                'link'        => '#',
                'category'    => 'project',
                'sort_order'  => 3,
            ],
            [
                'title'       => '數據分析儀表板',
                'description' => '建立視覺化數據分析平台，整合多種數據來源，提供即時圖表、報表生成、自定義儀表板等功能。',
                'tech_tags'   => 'React, D3.js, Python, FastAPI, MySQL',
                'link'        => '#',
                'category'    => 'project',
                'sort_order'  => 4,
            ],
            [
                'title'       => 'Vue UI 組件庫',
                'description' => '開發並維護一套 Vue.js UI 組件庫，提供常用的表單元件、對話框、通知等組件，在 GitHub 獲得 500+ stars。',
                'tech_tags'   => 'Vue.js, TypeScript, Sass',
                'link'        => '#',
                'category'    => 'opensource',
                'sort_order'  => 5,
            ],
            [
                'title'       => 'Node.js 工具套件',
                'description' => '開發用於簡化 Node.js 開發的工具套件，包含日誌管理、錯誤處理、API 請求封裝等實用功能。',
                'tech_tags'   => 'Node.js, TypeScript, npm',
                'link'        => '#',
                'category'    => 'opensource',
                'sort_order'  => 6,
            ],
        ];

        foreach ($projects as $project) {
            $project['created_at'] = date('Y-m-d H:i:s');
            $project['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('projects')->insert($project);
        }
    }
}
