<?php

namespace App\Service;


class RandomPasswordService
{

    private string $randomStrengthPassword;

    public function __construct()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+';

        $this->randomStrengthPassword = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 2);
        $this->randomStrengthPassword .= substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 2);
        $this->randomStrengthPassword .= substr(str_shuffle('0123456789'), 0, 1);
        $this->randomStrengthPassword .= substr(str_shuffle('!@#$%^&*()_+'), 0, 1);

        $this->randomStrengthPassword .= substr(str_shuffle($characters), 0, 2);

        $this->randomStrengthPassword = str_shuffle($this->randomStrengthPassword);
    }

    /**
     * @return mixed
     */
    public function getRandomStrenghPassword(): string
    {
        return $this->randomStrengthPassword;
    }
}