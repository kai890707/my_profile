<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 評論檢舉 Model
 *
 * @property int $id
 * @property int $rating_id
 * @property int $reporter_user_id
 * @property string $reason
 * @property string|null $description
 * @property string $status
 * @property int|null $reviewed_by_admin_id
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 * @property \Illuminate\Support\Carbon $created_at
 *
 * @property-read Rating $rating
 * @property-read User $reporter
 * @property-read User|null $reviewer
 */
class RatingReport extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rating_reports';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'rating_id',
        'reporter_user_id',
        'reason',
        'description',
        'status',
        'reviewed_by_admin_id',
        'reviewed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * 檢舉原因常數
     */
    public const REASON_FALSE_INFO = 'false_info';
    public const REASON_MALICIOUS = 'malicious';
    public const REASON_SENSITIVE = 'sensitive';
    public const REASON_SPAM = 'spam';
    public const REASON_OTHER = 'other';

    /**
     * 審核狀態常數
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    /**
     * 檢舉原因標籤
     *
     * @var array<string, string>
     */
    public const REASON_LABELS = [
        self::REASON_FALSE_INFO => '不實評論',
        self::REASON_MALICIOUS => '惡意攻擊',
        self::REASON_SENSITIVE => '包含敏感詞',
        self::REASON_SPAM => '廣告spam',
        self::REASON_OTHER => '其他',
    ];

    /**
     * 關聯：評分
     */
    public function rating(): BelongsTo
    {
        return $this->belongsTo(Rating::class, 'rating_id');
    }

    /**
     * 關聯：檢舉者
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /**
     * 關聯：審核管理員
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_admin_id');
    }

    /**
     * Scope: 待審核
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: 已審核（通過或駁回）
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReviewed($query)
    {
        return $query->whereIn('status', [self::STATUS_APPROVED, self::STATUS_REJECTED]);
    }

    /**
     * 取得檢舉原因標籤
     */
    public function getReasonLabel(): string
    {
        return self::REASON_LABELS[$this->reason] ?? $this->reason;
    }

    /**
     * 檢查是否待審核
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * 檢查是否已審核
     */
    public function isReviewed(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_REJECTED], true);
    }
}
