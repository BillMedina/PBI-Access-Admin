<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('pbi-access:make-password-hash')]
#[Description('Generates a secure hash for PBI_ADMIN_PASSWORD_HASH')]
class GenerateAdminPasswordHash extends Command
{
    public function handle(): int
    {
        $password = $this->secret('Contraseña del administrador');
        $passwordConfirmation = $this->secret('Confirme la contraseña');

        if (! is_string($password) || $password === '') {
            $this->error('La contraseña no puede estar vacía.');

            return self::FAILURE;
        }

        if (! is_string($passwordConfirmation) || ! hash_equals($password, $passwordConfirmation)) {
            $this->error('Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        $this->line(Hash::make($password));

        return self::SUCCESS;
    }
}
