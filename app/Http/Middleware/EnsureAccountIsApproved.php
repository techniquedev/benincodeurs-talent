<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Réserve une page aux comptes validés (et aux admins) ; les autres vont vers « compte en cours de validation ». */
class EnsureAccountIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ($user->isApproved() || $user->is_admin)) {
            return $next($request);
        }

        return redirect()->route('account.pending');
    }
}
