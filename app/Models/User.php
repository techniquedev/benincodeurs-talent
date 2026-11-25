<?php

namespace App\Models;

use App\Enums\UserStatus;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Support\Media;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['first_name', 'last_name', 'email', 'whatsapp', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasName, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = [
        'status' => 'approved',
        'is_admin' => false,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
            'vip_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_admin' => 'boolean',
        ];
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn () => trim($this->first_name.' '.$this->last_name));
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /** Profil du membre, créé vide à la première visite du formulaire. */
    public function ensureProfile(): Profile
    {
        return $this->profile ?? tap($this->profile()->create(), fn (Profile $profile) => $this->setRelation('profile', $profile));
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin && $this->status !== UserStatus::Suspended;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->status === UserStatus::Approved;
    }

    public function isSuspended(): bool
    {
        return $this->status === UserStatus::Suspended;
    }

    /** Réactive un compte suspendu (statut « validé »). */
    public function reactivate(): void
    {
        $this->forceFill([
            'status' => UserStatus::Approved,
            'approved_at' => $this->approved_at ?? now(),
            'suspended_at' => null,
        ])->save();
    }

    /** Supprime le compte, son profil, ses réalisations et leurs fichiers (photo, visuels). */
    public function deleteWithMedia(): void
    {
        $profile = $this->profile;

        $mediaPaths = $profile
            ? $profile->projects()->pluck('image_path')->push($profile->photo_path)->filter()->all()
            : [];

        $this->delete();

        foreach ($mediaPaths as $path) {
            Media::delete($path);
        }
    }

    public function suspend(): void
    {
        $this->forceFill([
            'status' => UserStatus::Suspended,
            'suspended_at' => now(),
        ])->save();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
