<?php


namespace Engine\Database\Sessions;


use Engine\Database\Session;

class SessionGame
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get(): string {
        return $this->session->getVar("game");
    }

    public function set(string $value){
        $this->session->setVar("game", $value);
    }
}