<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\ContactRequestReceived;
use App\Models\ContactRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendContactRequestEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array<int, int>
     */
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ContactRequest $contactRequest
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Mail::to($this->contactRequest->salesperson->email)
                ->send(new ContactRequestReceived($this->contactRequest));

            Log::info('Contact request email sent', [
                'contact_request_id' => $this->contactRequest->id,
                'salesperson_id' => $this->contactRequest->salesperson_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send contact request email', [
                'contact_request_id' => $this->contactRequest->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Contact request email failed after 3 retries', [
            'contact_request_id' => $this->contactRequest->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
