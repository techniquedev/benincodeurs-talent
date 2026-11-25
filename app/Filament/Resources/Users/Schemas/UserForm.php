<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserStatus;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Propaganistas\LaravelPhone\PhoneNumber;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')->label('Prénom')->required()->maxLength(100),
                        TextInput::make('last_name')->label('Nom')->required()->maxLength(100),
                        TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true),
                        TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->rule('phone:INTERNATIONAL,'.config('talent.phone_country'))
                            ->dehydrateStateUsing(fn (?string $state) => filled($state)
                                ? (new PhoneNumber($state, config('talent.phone_country')))->formatE164()
                                : null)
                            ->unique(ignoreRecord: true),
                    ]),
                Section::make('Compte')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')->label('Statut')->badge()
                            ->formatStateUsing(fn (UserStatus $state) => $state->label())
                            ->color(fn (UserStatus $state) => $state->color()),
                        TextEntry::make('email_verified_at')->label('E-mail vérifié le')->dateTime('d/m/Y H:i')->placeholder('Non vérifié'),
                        TextEntry::make('created_at')->label('Inscrit le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('approved_at')->label('Validé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('suspended_at')->label('Suspendu le')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('is_admin')->label('Administrateur')
                            ->formatStateUsing(fn (bool $state) => $state ? 'Oui' : 'Non'),
                    ])
                    ->visible(fn (?User $record) => $record !== null),
            ]);
    }
}
