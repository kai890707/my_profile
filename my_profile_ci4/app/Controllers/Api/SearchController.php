<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SalespersonProfileModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "搜尋", description: "業務員搜尋相關 API")]
class SearchController extends BaseController
{
    protected $profileModel;
    protected $companyModel;
    protected $userModel;

    public function __construct()
    {
        $this->profileModel = new SalespersonProfileModel();
        $this->companyModel = new CompanyModel();
        $this->userModel = new UserModel();
    }

    #[OA\Get(
        path: "/api/search/salespersons",
        tags: ["搜尋"],
        summary: "搜尋業務員",
        description: "根據關鍵字、公司、產業、地區等條件搜尋業務員",
        parameters: [
            new OA\Parameter(name: "keyword", in: "query", required: false, description: "搜尋關鍵字（姓名、簡介、公司名稱）", schema: new OA\Schema(type: "string", example: "王小明")),
            new OA\Parameter(name: "company", in: "query", required: false, description: "公司名稱", schema: new OA\Schema(type: "string", example: "科技公司")),
            new OA\Parameter(name: "industry_id", in: "query", required: false, description: "產業 ID", schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "region_id", in: "query", required: false, description: "地區 ID", schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "page", in: "query", required: false, description: "頁碼", schema: new OA\Schema(type: "integer", example: 1)),
            new OA\Parameter(name: "per_page", in: "query", required: false, description: "每頁筆數", schema: new OA\Schema(type: "integer", example: 20))
        ],
        responses: [
            new OA\Response(response: 200, description: "搜尋成功"),
            new OA\Response(response: 500, description: "伺服器錯誤")
        ]
    )]
    public function search()
    {
        $keyword = $this->request->getGet('keyword');
        $company = $this->request->getGet('company');
        $industryId = $this->request->getGet('industry_id');
        $regionId = $this->request->getGet('region_id');
        $page = (int) ($this->request->getGet('page') ?? 1);
        $perPage = (int) ($this->request->getGet('per_page') ?? 20);

        $builder = $this->profileModel
            ->select('salesperson_profiles.*, companies.name as company_name, industries.name as industry_name, users.username, users.email')
            ->join('users', 'users.id = salesperson_profiles.user_id', 'left')
            ->join('companies', 'companies.id = salesperson_profiles.company_id', 'left')
            ->join('industries', 'industries.id = companies.industry_id', 'left')
            ->where('salesperson_profiles.approval_status', 'approved')
            ->where('users.status', 'active');

        // 關鍵字搜尋
        if ($keyword) {
            $builder->groupStart()
                ->like('salesperson_profiles.full_name', $keyword)
                ->orLike('salesperson_profiles.bio', $keyword)
                ->orLike('companies.name', $keyword)
                ->groupEnd();
        }

        // 公司名稱
        if ($company) {
            $builder->like('companies.name', $company);
        }

        // 產業類別
        if ($industryId) {
            $builder->where('companies.industry_id', $industryId);
        }

        // 地區 (JSON搜尋)
        if ($regionId) {
            // 簡化版本：假設 service_regions 是 JSON 陣列
            $builder->like('salesperson_profiles.service_regions', $regionId);
        }

        // 分頁
        $data = $builder->paginate($perPage, 'default', $page);
        $pager = $this->profileModel->pager;

        // 移除檔案資料
        foreach ($data as &$item) {
            unset($item['avatar_data']);
        }

        return $this->respondSuccess([
            'data' => $data,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'per_page' => $pager->getPerPage(),
                'total' => $pager->getTotal(),
                'last_page' => $pager->getLastPage(),
            ],
        ]);
    }

    #[OA\Get(
        path: "/api/search/salespersons/{id}",
        tags: ["搜尋"],
        summary: "查看業務員詳細資料",
        description: "根據業務員 ID 取得詳細資訊",
        parameters: [
            new OA\Parameter(name: "id", in: "path", required: true, description: "業務員 ID", schema: new OA\Schema(type: "integer", example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: "查詢成功"),
            new OA\Response(response: 404, description: "業務員不存在"),
            new OA\Response(response: 500, description: "伺服器錯誤")
        ]
    )]
    public function show($id = null)
    {
        $profile = $this->profileModel
            ->select('salesperson_profiles.*, companies.name as company_name, companies.tax_id, industries.name as industry_name, users.username, users.email')
            ->join('users', 'users.id = salesperson_profiles.user_id', 'left')
            ->join('companies', 'companies.id = salesperson_profiles.company_id', 'left')
            ->join('industries', 'industries.id = companies.industry_id', 'left')
            ->where('salesperson_profiles.id', $id)
            ->where('salesperson_profiles.approval_status', 'approved')
            ->first();

        if (!$profile) {
            return $this->respondNotFound('業務員不存在或尚未審核通過');
        }

        // 移除敏感資料
        unset($profile['avatar_data']);

        return $this->respondSuccess($profile);
    }
}
