<?php

declare(strict_types=1);

use App\Jobs\SendContactRequestEmail;
use App\Mail\ContactRequestReceived;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    // Create a salesperson for testing
    $this->salesperson = User::factory()->create([
        'role' => 'salesperson',
        'email' => 'salesperson@example.com',
    ]);

    $this->salesperson->salespersonProfile()->create([
        'full_name' => 'Test Salesperson',
        'phone' => '0912345678',
        'approval_status' => 'approved',
    ]);

    // Create a customer
    $this->customer = User::factory()->create([
        'role' => 'user',
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
    ]);
});

// Test: Email sent when contact request created
test('email sent when contact request created', function (): void {
    Mail::fake();

    $contactRequest = ContactRequest::create([
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_name' => $this->customer->name,
        'customer_email' => $this->customer->email,
        'customer_phone' => '0912345678',
        'message' => 'This is a test message with more than 10 characters',
    ]);

    // Dispatch the job
    SendContactRequestEmail::dispatch($contactRequest);

    // Assert that mail was sent
    Mail::assertSent(ContactRequestReceived::class, function ($mail) use ($contactRequest) {
        return $mail->contactRequest->id === $contactRequest->id;
    });
});

// Test: Email contains correct data
test('email contains correct data', function (): void {
    Mail::fake();

    $contactRequest = ContactRequest::create([
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_name' => $this->customer->name,
        'customer_email' => $this->customer->email,
        'customer_phone' => '0912345678',
        'message' => 'I want to inquire about insurance services',
    ]);

    // Dispatch the job
    SendContactRequestEmail::dispatch($contactRequest);

    // Assert mail was sent to correct recipient
    Mail::assertSent(ContactRequestReceived::class, function ($mail) {
        return $mail->hasTo($this->salesperson->email) &&
               $mail->contactRequest->customer_name === 'Test Customer' &&
               $mail->contactRequest->message === 'I want to inquire about insurance services';
    });
});

// Test: Email queued not sent immediately
test('email queued not sent immediately', function (): void {
    Queue::fake();

    // Create contact request directly
    $contactRequest = ContactRequest::create([
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_name' => $this->customer->name,
        'customer_email' => $this->customer->email,
        'customer_phone' => '0912345678',
        'message' => 'This is a test message with more than 10 characters',
    ]);

    // Dispatch job manually
    SendContactRequestEmail::dispatch($contactRequest);

    // Assert job was pushed to queue
    Queue::assertPushed(SendContactRequestEmail::class, function ($job) use ($contactRequest) {
        return $job->contactRequest->id === $contactRequest->id;
    });
});

// Test: Email has correct subject
test('email has correct subject', function (): void {
    $contactRequest = ContactRequest::create([
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_name' => $this->customer->name,
        'customer_email' => $this->customer->email,
        'message' => 'Test message',
    ]);

    $mailable = new ContactRequestReceived($contactRequest);
    $envelope = $mailable->envelope();

    expect($envelope->subject)->toBe('新的聯繫請求 - YAMU 業務員推廣平台');
});

// Test: Email has reply URL
test('email has reply url', function (): void {
    $contactRequest = ContactRequest::create([
        'user_id' => $this->customer->id,
        'salesperson_id' => $this->salesperson->id,
        'customer_name' => $this->customer->name,
        'customer_email' => $this->customer->email,
        'message' => 'Test message',
    ]);

    $mailable = new ContactRequestReceived($contactRequest);
    $content = $mailable->content();

    expect($content->with['replyUrl'])->toContain('mailto:');
    expect($content->with['replyUrl'])->toContain($this->customer->email);
});
