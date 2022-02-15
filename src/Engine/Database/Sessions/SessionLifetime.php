<?php


namespace Engine\Database\Sessions;


use Engine\Database\Session;

class SessionLifetime
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get(): int {
        return $this->session->getVar("lifetime");
    }

    public function set(int $value){
        $this->session->setVar("lifetime", $value);
    }

    public function add(int $value){
        $this->set($this->get() + $value);
    }

    public function sub(int $value){
        $this->set($this->get() - $value);
    }
}