<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->organization_id && !$user->hasRole('Super Admin')) {
            $organization = $user->organization;
            if ($organization && $organization->status === 'inactive') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Organisasi Anda sedang dinonaktifkan oleh Super Admin. Akses dashboard dan manajemen internal tidak tersedia.',
                ], 403);
            }
        }

        return $next($request);
    }
}
