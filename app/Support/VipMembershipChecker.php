<?php

namespace App\Support;

use App\Enums\VipStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Interroge l'API VIP du site principal (POST {email}, jeton Bearer).
 * Renvoie null quand l'API est injoignable, mal configurée ou répond de façon inattendue.
 */
class VipMembershipChecker
{
    public function check(string $email): ?VipMembership
    {
        $url = (string) config('services.talent_vip.url');
        $token = (string) config('services.talent_vip.token');

        if ($url === '' || $token === '') {
            Log::error('API VIP non configurée : TALENT_VIP_API_URL ou TALENT_VIP_API_TOKEN manquant.');

            return null;
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->withToken($token)
                ->connectTimeout((int) config('services.talent_vip.timeout'))
                ->timeout((int) config('services.talent_vip.timeout'))
                ->post($url, ['email' => $email]);
        } catch (Throwable $exception) {
            Log::warning('API VIP injoignable.', ['error' => $exception->getMessage()]);

            return null;
        }

        if (! $response->successful() || ! is_bool($response->json('found'))) {
            Log::warning('Réponse inattendue de l\'API VIP.', ['status' => $response->status()]);

            return null;
        }

        if (! $response->json('found')) {
            return new VipMembership(VipStatus::NotFound);
        }

        return new VipMembership(
            status: $response->json('active') === true ? VipStatus::Active : VipStatus::Expired,
            firstName: $this->cleanString($response->json('prenom')),
            whatsapp: $this->cleanString($response->json('whatsapp')),
        );
    }

    private function cleanString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
