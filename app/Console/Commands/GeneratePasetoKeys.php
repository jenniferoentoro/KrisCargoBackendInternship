<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ParagonIE\Paseto\Keys\AsymmetricSecretKey;

class GeneratePasetoKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keys:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $privateKey = new AsymmetricSecretKey(sodium_crypto_sign_keypair());
        $publicKey = $privateKey->getPublicKey();
        //to string
        $privateKey = $privateKey->encode();
        $publicKey = $publicKey->encode();

        $envFile = '.env';

        //get env content
        $envContents = file_get_contents($envFile);
        //find PASETO_PUBLIC_KEY, if found, replace it, if not, append it
        if (strpos($envContents, 'PASETO_PUBLIC_KEY') !== false) {
            $envContents = preg_replace('/PASETO_PUBLIC_KEY=.*/', "PASETO_PUBLIC_KEY=$publicKey", $envContents);
        } else {
            $envContents .= "PASETO_PUBLIC_KEY=$publicKey\n";
        }
        //find PASETO_PRIVATE_KEY, if found, replace it, if not, append it
        if (strpos($envContents, 'PASETO_PRIVATE_KEY') !== false) {
            $envContents = preg_replace('/PASETO_PRIVATE_KEY=.*/', "PASETO_PRIVATE_KEY=$privateKey", $envContents);
        } else {
            $envContents .= "PASETO_PRIVATE_KEY=$privateKey\n";
        }

        if (file_put_contents($envFile, $envContents) !== false) {
            return Command::SUCCESS;
        } else {
            return Command::FAILURE;
        }
    }
}
