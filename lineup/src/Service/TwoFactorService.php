<?php

namespace App\Service;

use RobThree\Auth\TwoFactorAuth;

class TwoFactorService
{
    private TwoFactorAuth $tfa;

    public function __construct()
    {
        $this->tfa = new TwoFactorAuth('Lineup Barbershop');
    }

    public function generateSecret(): string
    {
        return $this->tfa->createSecret();
    }

    public function getQrCodeUri(string $secret, string $email): string
    {
        return $this->tfa->getQRCodeImageAsDataUri($email, $secret);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->tfa->verifyCode($secret, $code);
    }
}
