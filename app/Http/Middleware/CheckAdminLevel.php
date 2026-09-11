<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdminLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $requiredLevel): Response
    {
        if (!auth('admin')->check()) {
            abort(401, 'Não autenticado.');
        }
        if (auth('admin')->user()->level > $requiredLevel) {
            abort(403, 'Acesso Negado. Nível de permissão insuficiente.');
        }

        return $next($request);
    }
}
