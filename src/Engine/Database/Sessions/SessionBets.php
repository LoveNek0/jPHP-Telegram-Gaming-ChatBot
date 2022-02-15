<?php


namespace Engine\Database\Sessions;


use Engine\Database\Session;
use Engine\Database\Sessions\SessionBets\Bet;

class SessionBets
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
     * @return Bet[]
     */
    public function get(): array {
        $arr = $this->session->getVar("bets");
        $res = [];
        foreach ($arr as $user_id => $info)
            $res[] = new Bet($user_id, $info["bet"], $info["data"]);
        return $res;
    }

    /**
     * @param Bet[] $value
     */
    public function set(array $value){
        $arr = [];
        foreach ($value as $bet)
            $arr[$bet->user_id] = [
                "bet" => $bet->bet,
                "data" => $bet->data
        ];
        $this->session->setVar("bets", $arr);
    }

    public function add(Bet $bet){
        $arr = $this->get();
        $arr[] = $bet;
        $this->set($arr);
    }
}