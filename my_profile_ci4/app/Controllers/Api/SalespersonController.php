<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\SalespersonProfileModel;
use App\Models\CompanyModel;
use App\Models\ExperienceModel;
use App\Models\CertificationModel;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "業務員", description: "業務員個人檔案管理 API")]
class SalespersonController extends BaseController
{
    protected $profileModel;
    protected $companyModel;
    protected $experienceModel;
    protected $certificationModel;

    public function __construct()
    {
        $this->profileModel = new SalespersonProfileModel();
        $this->companyModel = new CompanyModel();
        $this->experienceModel = new ExperienceModel();
        $this->certificationModel = new CertificationModel();
    }

    #[OA\Get(
        path: "/api/salesperson/profile",
        tags: ["業務員"],
        summary: "取得個人檔案",
        description: "取得當前業務員的個人檔案資訊",
        security: [["bearerAuth" => []]],
        responses: [
            new OA\Response(response: 200, description: "查詢成功"),
            new OA\Response(response: 401, description: "未認證"),
            new OA\Response(response: 404, description: "檔案不存在")
        ]
    )]
    public function getProfile()
    {
        $userId = $this->getCurrentUserId();
        $profile = $this->profileModel->getByUserId($userId);

        if (!$profile) {
            return $this->respondNotFound('尚未建立個人資料');
        }

        unset($profile['avatar_data']); // 移除大型檔案資料
        return $this->respondSuccess($profile);
    }

    #[OA\Put(
        path: "/api/salesperson/profile",
        tags: ["業務員"],
        summary: "更新個人檔案",
        security: [["bearerAuth" => []]],
        responses: [new OA\Response(response: 200, description: "更新成功"), new OA\Response(response: 401, description: "未認證")]
    )]
    public function updateProfile()
    {
        $userId = $this->getCurrentUserId();
        $profile = $this->profileModel->getByUserId($userId);

        if (!$profile) {
            return $this->respondNotFound('尚未建立個人資料');
        }

        $data = $this->request->getJSON(true);
        $updateData = [];

        // 一般欄位 (即時生效)
        if (isset($data['full_name'])) $updateData['full_name'] = $data['full_name'];
        if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
        if (isset($data['bio'])) $updateData['bio'] = $data['bio'];
        if (isset($data['specialties'])) $updateData['specialties'] = $data['specialties'];
        if (isset($data['service_regions'])) {
            // Model 的 $casts 會自動處理 JSON 轉換，不需要手動 json_encode
            $updateData['service_regions'] = $data['service_regions'];
        }

        // 檔案上傳 (需審核)
        if (isset($data['avatar']) && !empty($data['avatar'])) {
            $avatar = $this->processBase64File($data['avatar']);
            if ($avatar) {
                $updateData['avatar_data'] = $avatar['data'];
                $updateData['avatar_mime'] = $avatar['mime'];
                $updateData['avatar_size'] = $avatar['size'];
                $updateData['approval_status'] = 'pending'; // 需要重新審核
            }
        }

        if ($this->profileModel->update($profile['id'], $updateData)) {
            return $this->respondSuccess(null, '個人資料更新成功');
        }

        return $this->respondError('更新失敗');
    }

    #[OA\Post(path: "/api/salesperson/company", tags: ["業務員"], summary: "儲存公司資訊", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "儲存成功")])]
    public function saveCompany()
    {
        $userId = $this->getCurrentUserId();
        $data = $this->request->getJSON(true);

        $companyData = [
            'name' => $data['name'],
            'tax_id' => $data['tax_id'],
            'industry_id' => $data['industry_id'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'approval_status' => 'pending',
            'created_by' => $userId,
        ];

        if ($this->companyModel->validate($companyData)) {
            $companyId = $this->companyModel->insert($companyData);
            return $this->respondSuccess(['company_id' => $companyId], '公司資訊已提交，等待審核', 201);
        }

        return $this->respondValidationError($this->companyModel->errors());
    }

    #[OA\Get(path: "/api/salesperson/experiences", tags: ["業務員"], summary: "取得工作經驗清單", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功")])]
    public function getExperiences()
    {
        $userId = $this->getCurrentUserId();
        $experiences = $this->experienceModel->where('user_id', $userId)->findAll();
        return $this->respondSuccess($experiences);
    }

    #[OA\Post(path: "/api/salesperson/experiences", tags: ["業務員"], summary: "新增工作經驗", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 201, description: "新增成功"), new OA\Response(response: 422, description: "驗證失敗")])]
    public function createExperience()
    {
        $userId = $this->getCurrentUserId();
        $data = $this->request->getJSON(true);

        $experienceData = [
            'user_id' => $userId,
            'company' => $data['company'],
            'position' => $data['position'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'description' => $data['description'] ?? null,
            'approval_status' => 'approved', // 經歷一般不需審核
        ];

        if ($this->experienceModel->insert($experienceData)) {
            return $this->respondSuccess(null, '工作經歷新增成功', 201);
        }

        return $this->respondValidationError($this->experienceModel->errors());
    }

    #[OA\Delete(path: "/api/salesperson/experiences/{id}", tags: ["業務員"], summary: "刪除工作經驗", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "工作經驗 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "刪除成功"), new OA\Response(response: 404, description: "工作經驗不存在")])]
    public function deleteExperience($id = null)
    {
        $userId = $this->getCurrentUserId();
        $experience = $this->experienceModel->find($id);

        if (!$experience || $experience['user_id'] != $userId) {
            return $this->respondNotFound('工作經歷不存在');
        }

        if ($this->experienceModel->delete($id)) {
            return $this->respondSuccess(null, '工作經歷已刪除');
        }

        return $this->respondError('刪除失敗');
    }

    #[OA\Get(path: "/api/salesperson/certifications", tags: ["業務員"], summary: "取得證照清單", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功")])]
    public function getCertifications()
    {
        $userId = $this->getCurrentUserId();
        $certs = $this->certificationModel->getByUserId($userId);

        // 移除檔案資料
        foreach ($certs as &$cert) {
            unset($cert['file_data']);
        }

        return $this->respondSuccess($certs);
    }

    #[OA\Post(path: "/api/salesperson/certifications", tags: ["業務員"], summary: "上傳證照", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 201, description: "上傳成功，等待審核"), new OA\Response(response: 422, description: "驗證失敗")])]
    public function createCertification()
    {
        $userId = $this->getCurrentUserId();
        $data = $this->request->getJSON(true);

        $certData = [
            'user_id' => $userId,
            'name' => $data['name'],
            'issuer' => $data['issuer'],
            'issue_date' => $data['issue_date'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'approval_status' => 'pending',
        ];

        // 處理檔案
        if (isset($data['file']) && !empty($data['file'])) {
            $file = $this->processBase64File($data['file']);
            if ($file) {
                $certData['file_data'] = $file['data'];
                $certData['file_mime'] = $file['mime'];
                $certData['file_size'] = $file['size'];
            }
        }

        if ($this->certificationModel->insert($certData)) {
            return $this->respondSuccess(null, '證照已上傳，等待審核', 201);
        }

        return $this->respondValidationError($this->certificationModel->errors());
    }

    #[OA\Get(path: "/api/salesperson/approval-status", tags: ["業務員"], summary: "查詢審核狀態", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功")])]
    public function getApprovalStatus()
    {
        $userId = $this->getCurrentUserId();

        // 取得個人資料
        $profile = $this->profileModel->select('approval_status,company_id')->where('user_id', $userId)->first();

        // 取得公司審核狀態（如果有關聯公司）
        $companyStatus = null;
        if ($profile && $profile['company_id']) {
            $company = $this->companyModel->select('approval_status')->find($profile['company_id']);
            $companyStatus = $company ? $company['approval_status'] : null;
        }

        // 取得工作經驗
        $experiences = $this->experienceModel
            ->select('id,company,position,approval_status,rejected_reason')
            ->where('user_id', $userId)
            ->findAll();

        // 取得證照
        $certifications = $this->certificationModel
            ->select('id,name,approval_status,rejected_reason')
            ->where('user_id', $userId)
            ->findAll();

        return $this->respondSuccess([
            'profile_status' => $profile ? $profile['approval_status'] : 'pending',
            'company_status' => $companyStatus,
            'experiences' => $experiences,
            'certifications' => $certifications,
        ]);
    }

    /**
     * 處理 Base64 檔案
     */
    private function processBase64File($base64String)
    {
        if (preg_match('/^data:([^;]+);base64,(.+)$/', $base64String, $matches)) {
            $mime = $matches[1];
            $data = base64_decode($matches[2]);
            $size = strlen($data);

            // 檢查大小 (5MB)
            if ($size > 5 * 1024 * 1024) {
                return null;
            }

            return ['data' => $data, 'mime' => $mime, 'size' => $size];
        }

        return null;
    }
}
