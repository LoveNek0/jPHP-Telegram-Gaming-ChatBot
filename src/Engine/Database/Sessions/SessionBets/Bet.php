<?php


namespace Engine\Database\Sessions\SessionBets;


class Bet
{
    /**
     * @var int
     */
    public $user_id;

    /**
     * @var float
     */
    public $bet;

    /**
     * @var mixed
     */
    public $data;

    public function __construct(int $user_id, float $bet, $data)
    {
        $this->user_id = $user_id;
        $this->bet = $bet;
        $this->data = $data;
    }
}