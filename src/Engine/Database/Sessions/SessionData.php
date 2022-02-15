<?php


namespace Engine\Database\Sessions;


use Engine\Database\Session;

class SessionData
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function get() {
        return $this->session->getVar("data");
    }

    public function set($value){
        $this->session->setVar("data", $value);
    }
}