<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\FaiMemberResource;
use App\Models\FaiMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FaiMemberResourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'email' => 'admin@dfnprenotazioni.it',
        ]);
    }

    public function test_can_render_fai_member_list_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(FaiMemberResource::getUrl('index'));

        $response->assertSuccessful();
    }

    public function test_can_search_fai_member_by_card_number(): void
    {
        $member1 = FaiMember::factory()->create([
            'card_number' => 'FAI-998877',
            'first_name' => 'Marco',
            'last_name' => 'Rossi',
        ]);

        $member2 = FaiMember::factory()->create([
            'card_number' => 'FAI-112233',
            'first_name' => 'Giulia',
            'last_name' => 'Bianchi',
        ]);

        Livewire::actingAs($this->admin)
            ->test(FaiMemberResource\Pages\ListFaiMembers::class)
            ->searchTable('FAI-998877')
            ->assertCanSeeTableRecords([$member1])
            ->assertCanNotSeeTableRecords([$member2]);
    }

    public function test_can_render_fai_member_create_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(FaiMemberResource::getUrl('create'));

        $response->assertSuccessful();
    }
}
