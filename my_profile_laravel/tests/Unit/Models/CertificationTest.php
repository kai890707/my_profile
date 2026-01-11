<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function certification_has_file_method_works_correctly(): void
    {
        // Certification with file
        $certWithFile = Certification::factory()->withFile()->create();

        // Certification without file
        $certWithoutFile = Certification::factory()->create([
            'file_data' => null,
            'file_mime' => null,
            'file_size' => null,
        ]);

        $this->assertTrue($certWithFile->hasFile());
        $this->assertFalse($certWithoutFile->hasFile());
    }

    /** @test */
    public function certification_get_file_extension_method_works_correctly(): void
    {
        $pdfCert = Certification::factory()->create(['file_mime' => 'application/pdf']);
        $jpegCert = Certification::factory()->create(['file_mime' => 'image/jpeg']);
        $jpgCert = Certification::factory()->create(['file_mime' => 'image/jpg']);
        $pngCert = Certification::factory()->create(['file_mime' => 'image/png']);
        $noCert = Certification::factory()->create(['file_mime' => null]);

        $this->assertEquals('pdf', $pdfCert->getFileExtension());
        $this->assertEquals('jpg', $jpegCert->getFileExtension());
        $this->assertEquals('jpg', $jpgCert->getFileExtension());
        $this->assertEquals('png', $pngCert->getFileExtension());
        $this->assertNull($noCert->getFileExtension());
    }

    /** @test */
    public function certification_get_file_size_in_mb_method_works_correctly(): void
    {
        // 1 MB = 1024 * 1024 bytes = 1,048,576 bytes
        $cert1MB = Certification::factory()->create(['file_size' => 1048576]);
        $cert5MB = Certification::factory()->create(['file_size' => 5242880]);
        $certNoSize = Certification::factory()->create(['file_size' => null]);

        $this->assertEquals(1.0, $cert1MB->getFileSizeInMB());
        $this->assertEquals(5.0, $cert5MB->getFileSizeInMB());
        $this->assertNull($certNoSize->getFileSizeInMB());
    }

    /** @test */
    public function certification_scopes_work_correctly(): void
    {
        $user = User::factory()->create();

        // Create certifications with different approval statuses
        $approved = Certification::factory()->approved()->create(['user_id' => $user->id]);
        $pending = Certification::factory()->pending()->create(['user_id' => $user->id]);
        $rejected = Certification::factory()->rejected()->create(['user_id' => $user->id]);

        // Test approved scope
        $approvedResults = Certification::approved()->get();
        $this->assertCount(1, $approvedResults);
        $this->assertEquals($approved->id, $approvedResults->first()->id);
        $this->assertEquals('approved', $approvedResults->first()->approval_status);

        // Test pending scope
        $pendingResults = Certification::pending()->get();
        $this->assertCount(1, $pendingResults);
        $this->assertEquals($pending->id, $pendingResults->first()->id);
        $this->assertEquals('pending', $pendingResults->first()->approval_status);

        // Test rejected scope
        $rejectedResults = Certification::rejected()->get();
        $this->assertCount(1, $rejectedResults);
        $this->assertEquals($rejected->id, $rejectedResults->first()->id);
        $this->assertEquals('rejected', $rejectedResults->first()->approval_status);
    }

    /** @test */
    public function certification_helper_methods_work_correctly(): void
    {
        $approved = Certification::factory()->approved()->create();
        $pending = Certification::factory()->pending()->create();
        $rejected = Certification::factory()->rejected()->create();

        // Test isApproved()
        $this->assertTrue($approved->isApproved());
        $this->assertFalse($pending->isApproved());
        $this->assertFalse($rejected->isApproved());

        // Test isPending()
        $this->assertFalse($approved->isPending());
        $this->assertTrue($pending->isPending());
        $this->assertFalse($rejected->isPending());

        // Test isRejected()
        $this->assertFalse($approved->isRejected());
        $this->assertFalse($pending->isRejected());
        $this->assertTrue($rejected->isRejected());
    }

    /** @test */
    public function certification_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $approver = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $certification = Certification::factory()->create([
            'user_id' => $user->id,
            'approved_by' => $approver->id,
        ]);

        // Test user relationship
        $this->assertInstanceOf(User::class, $certification->user);
        $this->assertEquals($user->id, $certification->user->id);

        // Test approver relationship
        $this->assertInstanceOf(User::class, $certification->approver);
        $this->assertEquals($approver->id, $certification->approver->id);
    }
}
