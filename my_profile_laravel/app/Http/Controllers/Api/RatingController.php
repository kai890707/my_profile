<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ModerateRatingRequest;
use App\Http\Requests\ReplyRatingRequest;
use App\Http\Requests\ReportRatingRequest;
use App\Http\Requests\StoreRatingRequest;
use App\Http\Requests\UpdateRatingRequest;
use App\Http\Resources\RatingResource;
use App\Http\Resources\RatingStatsResource;
use App\Models\Rating;
use App\Models\RatingReport;
use App\Models\User;
use App\Services\AnomalyDetector;
use App\Services\RatingStatsService;
use App\Services\RatingValidator;
use App\Services\SensitiveWordFilter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function __construct(
        private readonly RatingValidator $validator,
        private readonly SensitiveWordFilter $sensitiveWordFilter,
        private readonly AnomalyDetector $anomalyDetector,
        private readonly RatingStatsService $statsService
    ) {}

    /**
     * 提交評分與評論
     * POST /api/ratings
     */
    public function store(StoreRatingRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $salespersonId = $request->integer('salesperson_id');

        // 驗證評分資格
        if (!$this->validator->canUserRate($user, $salespersonId)) {
            $reason = $this->validator->getIneligibleReason($user, $salespersonId);
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => strtoupper($reason ?? 'UNKNOWN'),
                    'message' => $this->getErrorMessage($reason ?? 'unknown'),
                ],
            ], 403);
        }

        // 檢查敏感詞
        $comment = $request->string('comment')->toString();
        $hasSensitiveWords = !empty($comment) && $this->sensitiveWordFilter->check($comment);

        // 創建評分
        /** @var Rating $rating */
        $rating = DB::transaction(function () use ($request, $user, $hasSensitiveWords) {
            $rating = Rating::create([
                'user_id' => $user->id,
                'salesperson_id' => $request->integer('salesperson_id'),
                'contact_request_id' => $request->integer('contact_request_id'),
                'rating' => $request->float('rating'),
                'comment' => $request->string('comment')->toString(),
                'status' => $hasSensitiveWords ? Rating::STATUS_PENDING : Rating::STATUS_APPROVED,
                'ip_address' => $request->ip() ?? '',
            ]);

            return $rating->fresh(['user', 'salesperson']);
        });

        // 檢測異常
        if ($rating && $this->anomalyDetector->detect($rating)) {
            $rating->update(['status' => Rating::STATUS_PENDING]);
        }

        // 清除統計快取
        if ($rating) {
            $this->statsService->clearCache($rating->salesperson_id);
        }

        return response()->json([
            'success' => true,
            'data' => new RatingResource($rating),
        ], 201);
    }

    /**
     * 修改評分與評論
     * PUT /api/ratings/:id
     */
    public function update(UpdateRatingRequest $request, Rating $rating): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // 檢查權限
        if ($rating->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => '您沒有權限修改此評分',
                ],
            ], 403);
        }

        // 檢查修改時間限制（7 天）
        if ($rating->created_at->diffInDays(now()) > 7) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EDIT_EXPIRED',
                    'message' => '評分僅可在發布後 7 天內修改',
                ],
            ], 403);
        }

        // 檢查修改次數限制（1 次）
        if ($rating->edit_count >= 1) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'EDIT_LIMIT_REACHED',
                    'message' => '評分僅可修改一次',
                ],
            ], 403);
        }

        // 檢查敏感詞
        $comment = $request->string('comment')->toString();
        $hasSensitiveWords = !empty($comment) && $this->sensitiveWordFilter->check($comment);

        // 更新評分
        $rating->update([
            'rating' => $request->float('rating'),
            'comment' => $comment,
            'status' => $hasSensitiveWords ? Rating::STATUS_PENDING : Rating::STATUS_APPROVED,
            'edited_at' => now(),
        ]);

        // 增加編輯次數
        $rating->increment('edit_count');

        // 清除統計快取
        $this->statsService->clearCache($rating->salesperson_id);

        return response()->json([
            'success' => true,
            'data' => new RatingResource($rating->fresh(['user', 'salesperson'])),
        ]);
    }

    /**
     * 取得評分統計
     * GET /api/salespersons/:id/rating-stats
     */
    public function stats(int $salespersonId): JsonResponse
    {
        $stats = $this->statsService->getStats($salespersonId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * 取得評論列表
     * GET /api/salespersons/:id/ratings
     */
    public function index(Request $request, int $salespersonId): AnonymousResourceCollection
    {
        $query = Rating::query()
            ->forSalesperson($salespersonId)
            ->public()
            ->with(['user', 'salesperson']);

        // 排序
        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'highest' => $query->orderBy('rating', 'desc'),
            'lowest' => $query->orderBy('rating', 'asc'),
            default => $query->latest(),
        };

        // 篩選
        if ($request->has('filter')) {
            $filter = $request->query('filter');
            if ($filter === 'unreplied') {
                $query->whereNull('reply');
            }
        }

        $ratings = $query->paginate(10);

        return RatingResource::collection($ratings);
    }

    /**
     * 業務員回覆評論
     * POST /api/ratings/:id/reply
     */
    public function reply(ReplyRatingRequest $request, Rating $rating): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // 檢查權限（業務員本人）
        if ($rating->salesperson_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'FORBIDDEN',
                    'message' => '僅業務員本人可回覆',
                ],
            ], 403);
        }

        // 檢查是否已回覆
        if ($rating->reply !== null) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'ALREADY_REPLIED',
                    'message' => '此評論已回覆，無法再次回覆',
                ],
            ], 403);
        }

        // 檢查敏感詞
        $reply = $request->string('reply')->toString();
        if ($this->sensitiveWordFilter->check($reply)) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'SENSITIVE_WORDS_DETECTED',
                    'message' => '回覆內容包含敏感詞',
                ],
            ], 422);
        }

        // 儲存回覆
        $rating->update([
            'reply' => $reply,
            'replied_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => new RatingResource($rating->fresh(['user', 'salesperson'])),
        ]);
    }

    /**
     * 檢舉評論
     * POST /api/ratings/:id/report
     */
    public function report(ReportRatingRequest $request, Rating $rating): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        // 創建檢舉記錄
        $report = RatingReport::create([
            'rating_id' => $rating->id,
            'reporter_user_id' => $user->id,
            'reason' => $request->string('reason')->toString(),
            'description' => $request->string('description')->toString(),
            'status' => RatingReport::STATUS_PENDING,
        ]);

        // 標記評論為待審核
        $rating->update(['status' => Rating::STATUS_PENDING]);

        return response()->json([
            'success' => true,
            'message' => '檢舉已提交，我們將盡快處理',
        ], 201);
    }

    /**
     * 管理員審核評論
     * POST /api/admin/ratings/:id/moderate
     */
    public function moderate(ModerateRatingRequest $request, Rating $rating): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $action = $request->string('action')->toString();

        match ($action) {
            'approve' => $rating->update(['status' => Rating::STATUS_APPROVED, 'is_hidden' => false]),
            'hide' => $rating->update(['is_hidden' => true]),
            'delete' => $rating->delete(),
            default => null,
        };

        // 清除統計快取
        $this->statsService->clearCache($rating->salesperson_id);

        return response()->json([
            'success' => true,
            'message' => '審核完成',
        ]);
    }

    /**
     * 取得錯誤訊息
     */
    private function getErrorMessage(string $reason): string
    {
        return match ($reason) {
            'new_user_restriction' => '新用戶需註冊滿 7 天後才能評分',
            'no_completed_contact' => '您需要先與此業務員聯繫後才能評分',
            'already_rated' => '您已對此業務員評分，如需修改請編輯現有評分',
            default => '無法評分',
        };
    }
}
