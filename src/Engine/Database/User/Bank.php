<?php


namespace Engine\Database\User;


use Engine\Database\User;
use Engine\Database\User\Bank\BankVirtual;

class Bank
{
    const
        VIRTUAL = 0;
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function virtual(): BankVirtual{
        return new BankVirtual($this->user);
    }
}