<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 評分與評論 Model
 *
 * @property int $id
 * @property int $user_id
 * @property int $salesperson_id
 * @property int $contact_request_id
 * @property float $rating
 * @property string|null $comment
 * @property string|null $reply
 * @property \Illuminate\Support\Carbon|null $replied_at
 * @property string $status
 * @property bool $is_hidden
 * @property \Illuminate\Support\Carbon|null $edited_at
 * @property int $edit_count
 * @property string $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read User $user
 * @property-read User $salesperson
 * @property-read ContactRequest $contactRequest
 */
class Rating extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ratings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'salesperson_id',
        'contact_request_id',
        'rating',
        'comment',
        'reply',
        'replied_at',
        'status',
        'is_hidden',
        'edited_at',
        'edit_count',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'float',
        'is_hidden' => 'boolean',
        'edit_count' => 'integer',
        'replied_at' => 'datetime',
        'edited_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 評分狀態常數
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * 評分範圍常數
     */
    public const RATING_MIN = 1.0;
    public const RATING_MAX = 5.0;

    /**
     * 評論字數限制
     */
    public const COMMENT_MIN_LENGTH = 10;
    public const COMMENT_MAX_LENGTH = 500;

    /**
     * 回覆字數限制
     */
    public const REPLY_MIN_LENGTH = 10;
    public const REPLY_MAX_LENGTH = 300;

    /**
     * 評分修改期限（天數）
     */
    public const EDIT_ALLOWED_DAYS = 7;

    /**
     * 最大編輯次數
     */
    public const MAX_EDIT_COUNT = 1;

    /**
     * 關聯：評分者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 關聯：業務員
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'salesperson_id');
    }

    /**
     * 關聯：聯繫記錄
     */
    public function contactRequest(): BelongsTo
    {
        return $this->belongsTo(ContactRequest::class, 'contact_request_id');
    }

    /**
     * Scope: 已審核通過
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope: 未隱藏
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotHidden($query)
    {
        return $query->where('is_hidden', false);
    }

    /**
     * Scope: 公開顯示（已審核且未隱藏）
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        return $query->approved()->notHidden();
    }

    /**
     * Scope: 特定業務員的評分
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $salespersonId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSalesperson($query, int $salespersonId)
    {
        return $query->where('salesperson_id', $salespersonId);
    }

    /**
     * Scope: 按評分排序
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByRating($query, string $direction = 'desc')
    {
        return $query->orderBy('rating', $direction);
    }

    /**
     * Scope: 按時間排序（最新優先）
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * 檢查是否可以編輯
     */
    public function canEdit(): bool
    {
        // 超過 7 天
        if ($this->created_at->diffInDays(now()) > self::EDIT_ALLOWED_DAYS) {
            return false;
        }

        // 超過編輯次數
        if ($this->edit_count >= self::MAX_EDIT_COUNT) {
            return false;
        }

        return true;
    }

    /**
     * 檢查是否可以回覆
     */
    public function canReply(): bool
    {
        return $this->reply === null;
    }

    /**
     * 檢查是否已編輯
     */
    public function isEdited(): bool
    {
        return $this->edited_at !== null;
    }

    /**
     * 檢查是否已回覆
     */
    public function hasReply(): bool
    {
        return $this->reply !== null;
    }
}
