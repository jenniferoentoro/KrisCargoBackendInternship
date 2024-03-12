<?php

namespace App\Helpers;

use App\Helpers\IntAndDateFormatter;
use App\Models\User;
use Exception;
use ParagonIE\Paseto\Exception\InvalidVersionException;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Rules\IssuedBy;
use ParagonIE\Paseto\Rules\ValidAt;


class GetUserFromToken
{
    public static function getUser($bearerToken)
    {
        $parser = new Parser();
        $publicKey = AsymmetricPublicKey::fromEncodedString(env('PASETO_PUBLIC_KEY'));
        $parser = Parser::getPublic($publicKey, ProtocolCollection::v4())
            ->addRule(new ValidAt())
            ->addRule(new IssuedBy('kriscargo'));

        try {
            $token = $parser->parse($bearerToken);
            //get user id from token
            $userId = $token->get('user_id');
            // get user from db
            $user = User::findOrFail($userId);
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }
}
