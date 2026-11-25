<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminVerifyMemberEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Filament::setCurrentPanel('admin');
    }

    public function test_admin_can_mark_member_email_as_verified(): void
    {
        $member = User::factory()->unverified()->create();

        Livewire::test(EditUser::class, ['record' => $member->getRouteKey()])
            ->assertActionVisible('verifyEmail')
            ->callAction('verifyEmail')
            ->assertNotified();

        $this->assertTrue($member->fresh()->hasVerifiedEmail());
    }

    public function test_action_is_hidden_when_email_is_already_verified(): void
    {
        $member = User::factory()->create();

        Livewire::test(EditUser::class, ['record' => $member->getRouteKey()])
            ->assertActionHidden('verifyEmail');
    }
}
