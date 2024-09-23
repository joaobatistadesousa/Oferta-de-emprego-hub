<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');
        //palavra chave de autenticação ApiHubDevIIT
        // Verifica se o token está presente e se é válido
        if ($token !== 'QXBpSHViRGV2SUlU') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
