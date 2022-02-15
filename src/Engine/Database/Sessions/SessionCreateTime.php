<?php


namespace Engine\Database\Sessions;


use Engine\Database\Session;

class SessionCreateTime
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
        return $this->session->getVar("create_time");
    }

    public function set(int $value){
        $this->session->setVar("create_time", $value);
    }
}