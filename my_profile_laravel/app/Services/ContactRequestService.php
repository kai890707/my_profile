<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendContactRequestEmail;
use App\Models\ContactEvent;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContactRequestService
{
    public function __construct(
        private readonly RateLimitService $rateLimitService
    ) {}

    /**
     * Create a contact request.
     *
     * @param  User  $user  The authenticated user (customer)
     * @param  int  $salespersonId  Target salesperson ID
     * @param  array{customer_phone?: string|null, message: string}  $data  Request data
     * @param  string|null  $ipAddress  IP address for tracking
     * @param  string|null  $userAgent  User agent for tracking
     * @return ContactRequest Created contact request
     *
     * @throws \Exception If rate limit is exceeded or salesperson is not approved
     */
    public function createRequest(
        User $user,
        int $salespersonId,
        array $data,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): ContactRequest {
        return DB::transaction(function () use ($user, $salespersonId, $data, $ipAddress, $userAgent) {
            // Check rate limits
            if (! $this->canContact($user->id, $salespersonId)) {
                throw new \Exception('已超出頻率限制');
            }

            // Create contact request
            $contactRequest = ContactRequest::create([
                'user_id' => $user->id,
                'salesperson_id' => $salespersonId,
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $data['customer_phone'] ?? null,
                'message' => strip_tags($data['message']), // XSS protection
                'status' => 'pending',
            ]);

            // Track event
            ContactEvent::track(
                eventType: 'contact_form_submission',
                salespersonId: $salespersonId,
                userId: $user->id,
                ipAddress: $ipAddress,
                userAgent: $userAgent
            );

            // Dispatch email notification job (async)
            SendContactRequestEmail::dispatch($contactRequest);

            // Record contact for rate limiting
            $this->rateLimitService->recordContact($user->id, $salespersonId);

            return $contactRequest;
        });
    }

    /**
     * Check if user can contact the salesperson.
     *
     * Validates:
     * - BR-RL-002: 24h內同業務員只能聯繫 1 次
     * - BR-RL-003: 每天最多提交 5 次聯繫請求
     */
    public function canContact(int $userId, int $salespersonId): bool
    {
        // Check 24h rate limit for same salesperson
        if (! $this->rateLimitService->canContactSalesperson($userId, $salespersonId)) {
            return false;
        }

        // Check daily submission limit (5 per day across all salespersons)
        if (! $this->rateLimitService->canSubmitToday($userId)) {
            return false;
        }

        return true;
    }

    /**
     * Get contact request statistics for a salesperson.
     *
     * @return array{total: int, pending: int, contacted: int, closed: int}
     */
    public function getStatistics(int $salespersonId): array
    {
        $total = ContactRequest::forSalesperson($salespersonId)->count();
        $pending = ContactRequest::forSalesperson($salespersonId)->withStatus('pending')->count();
        $contacted = ContactRequest::forSalesperson($salespersonId)->withStatus('contacted')->count();
        $closed = ContactRequest::forSalesperson($salespersonId)->withStatus('closed')->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'contacted' => $contacted,
            'closed' => $closed,
        ];
    }
}
