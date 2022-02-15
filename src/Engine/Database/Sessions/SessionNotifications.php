<?php


namespace Engine\Database\Sessions;


use Engine\Database\Session;
use php\time\Time;

class SessionNotifications
{
    /**
     * @var Session
     */
    private $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return int[]
     */
    public function get(): array {
        return $this->session->getVar("notifications");
    }

    /**
     * @param int[] $value
     */
    public function set(array $value){
        $this->session->setVar("notifications", $value);
    }

    public function add(int $value){
        $l = $this->get();
        $l[] = $value;
        $this->set($l);
    }

    public function remove(int $value){
        $l = $this->get();
        $n = [];
        foreach ($l as $i)
            if($i != $value)
                $n[] = $i;
        $this->set($n);
    }
}