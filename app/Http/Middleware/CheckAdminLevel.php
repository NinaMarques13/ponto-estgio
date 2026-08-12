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
        if(!auth()->check()){
            abort(401);
        }
        if (auth()->user()->level> $requiredLevel){
            abort(403,'Acesso Negado.');
        }

        return $next($request);
    }
}
