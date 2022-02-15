<?php


namespace Engine\Database\User;


use Engine\Database\User;

class Name
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
        return $this->user->getVar("name");
    }

    public function set(string $name){
        $this->user->setVar("name", $name);
    }
}