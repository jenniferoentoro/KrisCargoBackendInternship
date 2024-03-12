<?php

namespace App\Http\Controllers;

use App\Models\User;
use DateInterval;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use ParagonIE\Paseto\Builder;
use ParagonIE\Paseto\Exception\InvalidVersionException;
use ParagonIE\Paseto\Exception\PasetoException;
use ParagonIE\Paseto\JsonToken;
use ParagonIE\Paseto\Keys\AsymmetricPublicKey;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;
use ParagonIE\Paseto\Keys\SymmetricKey;
use ParagonIE\Paseto\Parser;
use ParagonIE\Paseto\Protocol\Version4;
use ParagonIE\Paseto\ProtocolCollection;
use ParagonIE\Paseto\Purpose;
use ParagonIE\Paseto\Rules\IssuedBy;
use ParagonIE\Paseto\Rules\ValidAt;

class AuthController extends Controller
{

    public function isLoggedIn(Request $request)
    {
        //get token from bearer token
        $providedToken = $request->bearerToken();


        if (!$providedToken) {
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
            return response()->json(['success' => 'Logged in'], 200);
        } catch (InvalidVersionException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        } catch (PasetoException $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


    public function login(Request $request)
    {

        //get email, if email is not found, return error
        $email = trim($request->email);
        $user = User::where('EMAIL', $email)->first();
        if (!$user) {
            return response()->json([
                'error' => 'Wrong email or password!'
            ], 404);
        }
        //get password, if password is not correct (bcrypt), return error
        $password = $request->password;
        if (Hash::check($password, $user->PASSWORD) == false) {
            return response()->json([
                'error' => 'Wrong email or password!'
            ], 401);
        }
        //get user id
        $userId = $user->KODE;
        //get user name
        $userName = $user->NAMA;
        //generate token
        $privateKey = AsymmetricSecretKey::fromEncodedString(env('PASETO_PRIVATE_KEY'));
        $token = Builder::getPublic($privateKey, new Version4);

        $token = (new Builder())
            ->setKey($privateKey)
            ->setVersion(new Version4)
            ->setPurpose(Purpose::public())
            // Set it to expire in one day
            ->setIssuedAt()
            ->setIssuer('kriscargo')
            ->setNotBefore()
            ->setExpiration(
                (new DateTime())->add(new DateInterval('P01D'))
            )
            ->setClaims([
                'user_id' => $userId
            ]);

        //return token with status 200
        return response()->json([
            'token' => $token->toString(),
            'user_name' => $userName
        ], 200);
    }

    public function registerAdmin()
    {
        //register the admin and hash it's password
        $password = 'password';
        $hashedPassword = bcrypt($password);
        //store the hashed password in database with model user
        $user = new User();

        $user->email = 'admin@gmail.com';
        $user->password = $hashedPassword;
        $user->name = 'admin';
        $user->save();
        return response()->json([
            'message' => 'success'
        ]);
    }







    public function coba(Request $request)
    {

        $privateKey = AsymmetricSecretKey::fromEncodedString(env('PASETO_PRIVATE_KEY'));
        $publicKey = AsymmetricPublicKey::fromEncodedString(env('PASETO_PUBLIC_KEY'));

        $token = Builder::getPublic($privateKey, new Version4);

        $token = (new Builder())
            ->setKey($privateKey)
            ->setVersion(new Version4)
            ->setPurpose(Purpose::public())
            // Set it to expire in one day
            ->setIssuedAt()
            ->setIssuer('kriscargo')
            ->setNotBefore()
            ->setExpiration(
                (new DateTime())->add(new DateInterval('P01D'))
            )
            // Store arbitrary data
            ->setClaims([
                'example' => 'Hello world',
                'security' => 'Now as easy as PIE',
                'email' => 'eric@gmail.com',
            ]);

        $parser = Parser::getPublic($publicKey, ProtocolCollection::v4())
            ->addRule(new ValidAt)
            ->addRule(new IssuedBy('kriscargo'));

        $providedToken = $token->toString();
        try {
            $token = $parser->parse($providedToken);
            dd($token->getClaims());
        } catch (PasetoException $ex) {
            /* Handle invalid token cases here. */
            dd($ex);
        }
    }
}
