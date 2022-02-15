<?php


namespace Engine\Database\User;


use Engine\Database\User;

class Spouse
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get(): int {
        return $this->user->getVar("spouse");
    }

    public function set(int $spouse){
        $this->user->setVar("spouse", $spouse);
    }
}