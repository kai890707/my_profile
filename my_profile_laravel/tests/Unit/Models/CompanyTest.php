<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_fields(): void
    {
        $fillable = (new Company)->getFillable();

        $expectedFields = [
            'name',
            'tax_id',
            'is_personal',
            'created_by',
            'approval_status',
            'rejected_reason',
            'approved_by',
            'approved_at',
        ];

        foreach ($expectedFields as $field) {
            $this->assertContains($field, $fillable);
        }

        $this->assertCount(8, $fillable);
    }

    /** @test */
    public function scope_registered_filters_registered_companies(): void
    {
        // Create registered companies (with tax_id, not personal)
        $registered1 = Company::factory()->create([
            'tax_id' => '12345678',
            'is_personal' => false,
        ]);

        $registered2 = Company::factory()->create([
            'tax_id' => '87654321',
            'is_personal' => false,
        ]);

        // Create personal studio (no tax_id or is_personal=true)
        $personal = Company::factory()->create([
            'tax_id' => null,
            'is_personal' => true,
        ]);

        $registeredCompanies = Company::registered()->get();

        $this->assertCount(2, $registeredCompanies);
        $this->assertTrue($registeredCompanies->contains($registered1));
        $this->assertTrue($registeredCompanies->contains($registered2));
        $this->assertFalse($registeredCompanies->contains($personal));
    }

    /** @test */
    public function scope_personal_filters_personal_studios(): void
    {
        // Create registered companies
        $registered = Company::factory()->create([
            'tax_id' => '12345678',
            'is_personal' => false,
        ]);

        // Create personal studios
        $personal1 = Company::factory()->create([
            'tax_id' => null,
            'is_personal' => true,
        ]);

        $personal2 = Company::factory()->create([
            'tax_id' => null,
            'is_personal' => true,
        ]);

        $personalCompanies = Company::personal()->get();

        $this->assertCount(2, $personalCompanies);
        $this->assertTrue($personalCompanies->contains($personal1));
        $this->assertTrue($personalCompanies->contains($personal2));
        $this->assertFalse($personalCompanies->contains($registered));
    }

    /** @test */
    public function it_belongs_to_creator_user(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create(['created_by' => $user->id]);

        $this->assertInstanceOf(User::class, $company->creator);
        $this->assertEquals($user->id, $company->creator->id);
    }

    /** @test */
    public function tax_id_can_be_null_for_personal_studios(): void
    {
        $company = Company::factory()->create([
            'name' => 'John Personal Studio',
            'tax_id' => null,
            'is_personal' => true,
        ]);

        $this->assertNull($company->tax_id);
        $this->assertTrue($company->is_personal);
    }

    /** @test */
    public function tax_id_must_be_unique_when_not_null(): void
    {
        $user = User::factory()->create();

        Company::factory()->create([
            'tax_id' => '12345678',
            'is_personal' => false,
            'created_by' => $user->id,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Company::factory()->create([
            'tax_id' => '12345678',
            'is_personal' => false,
            'created_by' => $user->id,
        ]);
    }
}
