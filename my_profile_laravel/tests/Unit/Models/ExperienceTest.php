<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Experience;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExperienceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function experience_scopes_work_correctly(): void
    {
        $user = User::factory()->create();

        // Create experiences with different approval statuses
        $approved = Experience::factory()->approved()->create(['user_id' => $user->id]);
        $pending = Experience::factory()->pending()->create(['user_id' => $user->id]);
        $rejected = Experience::factory()->rejected()->create(['user_id' => $user->id]);

        // Test approved scope
        $approvedResults = Experience::approved()->get();
        $this->assertCount(1, $approvedResults);
        $this->assertEquals($approved->id, $approvedResults->first()->id);
        $this->assertEquals('approved', $approvedResults->first()->approval_status);

        // Test pending scope
        $pendingResults = Experience::pending()->get();
        $this->assertCount(1, $pendingResults);
        $this->assertEquals($pending->id, $pendingResults->first()->id);
        $this->assertEquals('pending', $pendingResults->first()->approval_status);

        // Test rejected scope
        $rejectedResults = Experience::rejected()->get();
        $this->assertCount(1, $rejectedResults);
        $this->assertEquals($rejected->id, $rejectedResults->first()->id);
        $this->assertEquals('rejected', $rejectedResults->first()->approval_status);
    }

    /** @test */
    public function experience_ordered_scope_sorts_by_sort_order_and_start_date(): void
    {
        $user = User::factory()->create();

        // Create experiences with different sort orders and dates
        $exp1 = Experience::factory()->create([
            'user_id' => $user->id,
            'sort_order' => 2,
            'start_date' => '2020-01-01',
        ]);

        $exp2 = Experience::factory()->create([
            'user_id' => $user->id,
            'sort_order' => 1,
            'start_date' => '2022-01-01',
        ]);

        $exp3 = Experience::factory()->create([
            'user_id' => $user->id,
            'sort_order' => 1,
            'start_date' => '2021-01-01',
        ]);

        // Test ordered scope
        $orderedResults = Experience::ordered()->get();

        // Should be ordered by sort_order ASC, then start_date DESC
        $this->assertEquals($exp2->id, $orderedResults[0]->id); // sort_order=1, start_date=2022
        $this->assertEquals($exp3->id, $orderedResults[1]->id); // sort_order=1, start_date=2021
        $this->assertEquals($exp1->id, $orderedResults[2]->id); // sort_order=2, start_date=2020
    }

    /** @test */
    public function experience_helper_methods_work_correctly(): void
    {
        $approved = Experience::factory()->approved()->create();
        $pending = Experience::factory()->pending()->create();
        $rejected = Experience::factory()->rejected()->create();

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
    public function experience_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $approver = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $experience = Experience::factory()->create([
            'user_id' => $user->id,
            'approved_by' => $approver->id,
        ]);

        // Test user relationship
        $this->assertInstanceOf(User::class, $experience->user);
        $this->assertEquals($user->id, $experience->user->id);

        // Test approver relationship
        $this->assertInstanceOf(User::class, $experience->approver);
        $this->assertEquals($approver->id, $experience->approver->id);
    }
}
