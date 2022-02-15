<?php


namespace Engine\Database\User;


use Engine\Database\User;

class Login
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get(): string {
        return $this->user->getVar("login");
    }

    public function set(string $login){
        $this->user->setVar("login", $login);
    }
}