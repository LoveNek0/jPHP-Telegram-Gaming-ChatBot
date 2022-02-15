<?php


namespace Engine\Database\User\Bank;


use Engine\Database\User;

class BankVirtual
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
        return $this->user->getVar("bank_virtual");
    }

    public function set(float $value){
        $this->user->setVar("bank_virtual", $value);
    }

    public function add(float $value){
        $this->set($this->get() + $value);
    }

    public function sub(float $value){
        $this->set($this->get() - $value);
    }
}