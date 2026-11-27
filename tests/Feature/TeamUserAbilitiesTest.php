<?php

namespace Tests\Feature;

use App\Filament\Resources\TeamUsers\Pages\ManageTeamUsers;
use App\Models\User;
use App\Support\Permissions;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TeamUserAbilitiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['talent.admin.email' => 'super@example.com']);
        $this->actingAs(User::factory()->create(['is_admin' => true, 'email' => 'super@example.com']));
        Filament::setCurrentPanel('admin');
    }

    public function test_creating_team_user_keeps_locked_view_ability(): void
    {
        Livewire::test(ManageTeamUsers::class)
            ->callAction('create', [
                'first_name' => 'Awa',
                'last_name' => 'Dossou',
                'email' => 'awa@example.com',
                'password' => 'un-mot-de-passe-long',
                'abilities' => ['view', 'export'],
            ])
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'awa@example.com')->firstOrFail();

        $this->assertSame(['view', 'export'], Permissions::abilitiesOf($user));
    }

    public function test_updating_team_user_always_stores_view_ability(): void
    {
        $teamUser = User::factory()->create(['is_admin' => true]);
        Permissions::sync($teamUser, ['create']);

        Livewire::test(ManageTeamUsers::class)
            ->callAction(TestAction::make('edit')->table($teamUser), ['abilities' => ['update', 'delete']])
            ->assertHasNoFormErrors();

        $this->assertSame(['view', 'update', 'delete'], Permissions::abilitiesOf($teamUser->fresh()));
    }

    public function test_unknown_ability_is_rejected_with_french_message(): void
    {
        Livewire::test(ManageTeamUsers::class)
            ->callAction('create', [
                'first_name' => 'Awa',
                'last_name' => 'Dossou',
                'email' => 'awa@example.com',
                'password' => 'un-mot-de-passe-long',
                'abilities' => ['view', 'hack'],
            ])
            ->assertHasFormErrors(['abilities.1']);

        $this->assertDatabaseMissing('users', ['email' => 'awa@example.com']);
    }
}
