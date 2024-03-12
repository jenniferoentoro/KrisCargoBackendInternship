<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Exception\InvalidVersionException;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\JsonToken;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Rules\IssuedBy;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use ParagonIE\Paseto\Rules\ValidAt;


class CheckTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $providedToken = $request->bearerToken();

        if (!$providedToken) {
            //return response()->json(['error' => 'Unauthorized'], 401);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Verify the token
        $parser = new Parser();
        $publicKey = AsymmetricPublicKey::fromEncodedString(env('PASETO_PUBLIC_KEY'));
        $parser = Parser::getPublic($publicKey, ProtocolCollection::v4())
            ->addRule(new ValidAt)
            ->addRule(new IssuedBy('kriscargo'));


        try {
            $token = $parser->parse($providedToken);
            return $next($request);
        } catch (InvalidVersionException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (PasetoException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
