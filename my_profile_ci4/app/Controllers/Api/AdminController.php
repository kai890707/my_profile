<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\SalespersonProfileModel;
use App\Models\CompanyModel;
use App\Models\CertificationModel;
use App\Models\IndustryModel;
use App\Models\RegionModel;
use App\Models\ApprovalLogModel;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "管理員", description: "管理員後台管理 API")]
class AdminController extends BaseController
{
    protected $userModel;
    protected $profileModel;
    protected $companyModel;
    protected $certificationModel;
    protected $industryModel;
    protected $regionModel;
    protected $approvalLogModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->profileModel = new SalespersonProfileModel();
        $this->companyModel = new CompanyModel();
        $this->certificationModel = new CertificationModel();
        $this->industryModel = new IndustryModel();
        $this->regionModel = new RegionModel();
        $this->approvalLogModel = new ApprovalLogModel();
    }

    #[OA\Get(path: "/api/admin/pending-approvals", tags: ["管理員"], summary: "取得待審核項目", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功"), new OA\Response(response: 401, description: "未認證")])]
    public function getPendingApprovals()
    {
        $certifications = $this->certificationModel->getPendingCertifications();

        // 為每個證照添加圖片 URL
        foreach ($certifications as &$cert) {
            $cert['file_url'] = $this->getCertificationImageUrl($cert['id']);
        }

        $profiles = $this->profileModel->getPendingProfiles();

        // 為每個個人檔案添加頭像 URL
        foreach ($profiles as &$profile) {
            $profile['avatar_url'] = $this->getProfileAvatarUrl($profile['id']);
        }

        return $this->respondSuccess([
            'users' => $this->userModel->where('status', 'pending')->where('role', 'salesperson')->findAll(),
            'profiles' => $profiles,
            'companies' => $this->companyModel->getPendingCompanies(),
            'certifications' => $certifications,
        ]);
    }

    /**
     * 取得證照圖片 URL
     */
    private function getCertificationImageUrl(int $certId): ?string
    {
        $cert = $this->certificationModel->select('file_data, file_mime')->find($certId);

        if (!$cert || !$cert['file_data']) {
            return null;
        }

        // 轉換為 Base64 Data URL
        return 'data:' . $cert['file_mime'] . ';base64,' . base64_encode($cert['file_data']);
    }

    /**
     * 取得個人檔案頭像 URL
     */
    private function getProfileAvatarUrl(int $profileId): ?string
    {
        $profile = $this->profileModel->select('avatar_data, avatar_mime')->find($profileId);

        if (!$profile || !$profile['avatar_data']) {
            return null;
        }

        // 轉換為 Base64 Data URL
        return 'data:' . $profile['avatar_mime'] . ';base64,' . base64_encode($profile['avatar_data']);
    }

    #[OA\Post(path: "/api/admin/approve-user/{id}", tags: ["管理員"], summary: "審核通過業務員註冊", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "使用者 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "審核成功"), new OA\Response(response: 401, description: "未認證")])]
    public function approveUser($id = null)
    {
        $adminId = $this->getCurrentUserId();

        if ($this->userModel->updateStatus($id, 'active')) {
            $this->approvalLogModel->logApproval('user', $id, 'approved', $adminId);
            return $this->respondSuccess(null, '業務員註冊已審核通過');
        }

        return $this->respondError('審核失敗');
    }

    #[OA\Post(path: "/api/admin/reject-user/{id}", tags: ["管理員"], summary: "拒絕業務員註冊", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "使用者 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "操作成功"), new OA\Response(response: 401, description: "未認證")])]
    public function rejectUser($id = null)
    {
        $adminId = $this->getCurrentUserId();
        $data = $this->request->getJSON(true);
        $reason = $data['reason'] ?? null;

        if ($this->userModel->updateStatus($id, 'inactive')) {
            $this->approvalLogModel->logApproval('user', $id, 'rejected', $adminId, $reason);
            return $this->respondSuccess(null, '業務員註冊已拒絕');
        }

        return $this->respondError('操作失敗');
    }

    #[OA\Post(path: "/api/admin/approve-company/{id}", tags: ["管理員"], summary: "審核通過公司資訊", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "公司 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "審核成功"), new OA\Response(response: 401, description: "未認證")])]
    public function approveCompany($id = null)
    {
        $adminId = $this->getCurrentUserId();

        if ($this->companyModel->updateApprovalStatus($id, 'approved', $adminId)) {
            $this->approvalLogModel->logApproval('company', $id, 'approved', $adminId);
            return $this->respondSuccess(null, '公司資訊已審核通過');
        }

        return $this->respondError('審核失敗');
    }

    #[OA\Post(path: "/api/admin/reject-company/{id}", tags: ["管理員"], summary: "拒絕公司資訊", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "公司 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "操作成功"), new OA\Response(response: 401, description: "未認證")])]
    public function rejectCompany($id = null)
    {
        $adminId = $this->getCurrentUserId();
        $data = $this->request->getJSON(true);
        $reason = $data['reason'] ?? null;

        if ($this->companyModel->updateApprovalStatus($id, 'rejected', $adminId)) {
            $this->approvalLogModel->logApproval('company', $id, 'rejected', $adminId, $reason);
            return $this->respondSuccess(null, '公司資訊已拒絕');
        }

        return $this->respondError('操作失敗');
    }

    #[OA\Post(path: "/api/admin/approve-certification/{id}", tags: ["管理員"], summary: "審核通過證照", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "證照 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "審核成功"), new OA\Response(response: 401, description: "未認證")])]
    public function approveCertification($id = null)
    {
        $adminId = $this->getCurrentUserId();

        if ($this->certificationModel->updateApprovalStatus($id, 'approved', $adminId)) {
            $this->approvalLogModel->logApproval('certification', $id, 'approved', $adminId);
            return $this->respondSuccess(null, '證照已審核通過');
        }

        return $this->respondError('審核失敗');
    }

    #[OA\Post(path: "/api/admin/reject-certification/{id}", tags: ["管理員"], summary: "拒絕證照", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "證照 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "操作成功"), new OA\Response(response: 401, description: "未認證")])]
    public function rejectCertification($id = null)
    {
        $adminId = $this->getCurrentUserId();
        $data = $this->request->getJSON(true);
        $reason = $data['reason'] ?? null;

        if ($this->certificationModel->updateApprovalStatus($id, 'rejected', $adminId)) {
            $this->approvalLogModel->logApproval('certification', $id, 'rejected', $adminId, $reason);
            return $this->respondSuccess(null, '證照已拒絕');
        }

        return $this->respondError('操作失敗');
    }

    #[OA\Get(path: "/api/admin/users", tags: ["管理員"], summary: "取得使用者清單", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "role", in: "query", required: false, description: "使用者角色", schema: new OA\Schema(type: "string", example: "salesperson")), new OA\Parameter(name: "status", in: "query", required: false, description: "使用者狀態", schema: new OA\Schema(type: "string", example: "active"))], responses: [new OA\Response(response: 200, description: "查詢成功"), new OA\Response(response: 401, description: "未認證")])]
    public function getUsers()
    {
        $role = $this->request->getGet('role');
        $status = $this->request->getGet('status');

        $builder = $this->userModel;

        if ($role) {
            $builder->where('role', $role);
        }

        if ($status) {
            $builder->where('status', $status);
        }

        $users = $builder->findAll();

        // 移除密碼
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }

        return $this->respondSuccess($users);
    }

    #[OA\Put(path: "/api/admin/users/{id}/status", tags: ["管理員"], summary: "更新使用者狀態", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "使用者 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "更新成功"), new OA\Response(response: 400, description: "無效的狀態"), new OA\Response(response: 401, description: "未認證")])]
    public function updateUserStatus($id = null)
    {
        $data = $this->request->getJSON(true);
        $status = $data['status'] ?? null;

        if (!in_array($status, ['active', 'inactive'], true)) {
            return $this->respondError('無效的狀態', 400);
        }

        if ($this->userModel->updateStatus($id, $status)) {
            return $this->respondSuccess(null, '使用者狀態已更新');
        }

        return $this->respondError('更新失敗');
    }

    #[OA\Delete(path: "/api/admin/users/{id}", tags: ["管理員"], summary: "刪除使用者", security: [["bearerAuth" => []]], parameters: [new OA\Parameter(name: "id", in: "path", required: true, description: "使用者 ID", schema: new OA\Schema(type: "integer", example: 1))], responses: [new OA\Response(response: 200, description: "刪除成功"), new OA\Response(response: 401, description: "未認證")])]
    public function deleteUser($id = null)
    {
        if ($this->userModel->delete($id)) {
            return $this->respondSuccess(null, '使用者已刪除');
        }

        return $this->respondError('刪除失敗');
    }

    #[OA\Get(path: "/api/admin/settings/industries", tags: ["管理員"], summary: "取得產業類別清單", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功"), new OA\Response(response: 401, description: "未認證")])]
    public function getIndustries()
    {
        return $this->respondSuccess($this->industryModel->findAll());
    }

    #[OA\Post(path: "/api/admin/settings/industries", tags: ["管理員"], summary: "新增產業類別", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 201, description: "新增成功"), new OA\Response(response: 422, description: "驗證失敗"), new OA\Response(response: 401, description: "未認證")])]
    public function createIndustry()
    {
        $data = $this->request->getJSON(true);

        if ($this->industryModel->insert($data)) {
            return $this->respondSuccess(null, '產業類別已新增', 201);
        }

        return $this->respondValidationError($this->industryModel->errors());
    }

    #[OA\Get(path: "/api/admin/settings/regions", tags: ["管理員"], summary: "取得地區清單", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功"), new OA\Response(response: 401, description: "未認證")])]
    public function getRegions()
    {
        return $this->respondSuccess($this->regionModel->findAll());
    }

    #[OA\Post(path: "/api/admin/settings/regions", tags: ["管理員"], summary: "新增地區", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 201, description: "新增成功"), new OA\Response(response: 422, description: "驗證失敗"), new OA\Response(response: 401, description: "未認證")])]
    public function createRegion()
    {
        $data = $this->request->getJSON(true);

        if ($this->regionModel->insert($data)) {
            return $this->respondSuccess(null, '地區已新增', 201);
        }

        return $this->respondValidationError($this->regionModel->errors());
    }

    #[OA\Get(path: "/api/admin/statistics", tags: ["管理員"], summary: "取得統計資料", security: [["bearerAuth" => []]], responses: [new OA\Response(response: 200, description: "查詢成功"), new OA\Response(response: 401, description: "未認證")])]
    public function getStatistics()
    {
        $totalSalespersons = $this->userModel->where('role', 'salesperson')->countAllResults();
        $activeSalespersons = $this->userModel->where('role', 'salesperson')->where('status', 'active')->countAllResults();
        $pendingSalespersons = $this->userModel->where('role', 'salesperson')->where('status', 'pending')->countAllResults();
        $totalCompanies = $this->companyModel->where('approval_status', 'approved')->countAllResults();
        $pendingApprovals = $this->profileModel->where('approval_status', 'pending')->countAllResults()
            + $this->companyModel->where('approval_status', 'pending')->countAllResults()
            + $this->certificationModel->where('approval_status', 'pending')->countAllResults();

        return $this->respondSuccess([
            'total_salespersons' => $totalSalespersons,
            'active_salespersons' => $activeSalespersons,
            'pending_salespersons' => $pendingSalespersons,
            'total_companies' => $totalCompanies,
            'pending_approvals' => $pendingApprovals,
        ]);
    }
}
