<?php

namespace App\Http\Middleware;

use Closure;

class VerifyGithubSignature
{
    /**
     * Securing github webhooks. @see https://developer.github.com/webhooks/securing/
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $signature = 'sha1=' . hash_hmac(
            'sha1',
            $request->getContent(),
            config('github.secret')
        );

        if (!hash_equals($signature, $request->header('X-Hub-Signature'))) {
            return response(['error' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
