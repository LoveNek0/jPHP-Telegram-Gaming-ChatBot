<?php


namespace Engine\Database\User;


use Engine\Database\User;

class Level
{
    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get(): float {
        return $this->user->getVar("level");
    }

    public function set(float $value){
        $this->user->setVar("level", $value);
    }

    public function add(float $value){
        $this->set($this->get() + $value);
    }

    public function sub(float $value){
        $this->set($this->get() - $value);
    }
}