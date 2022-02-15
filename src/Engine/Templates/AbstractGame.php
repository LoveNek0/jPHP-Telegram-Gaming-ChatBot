<?php


namespace Engine\Templates;


use Engine\Database\Session;
use Engine\Database\User;
use Engine\GamingBot;
use telegram\object\TUpdate;

abstract class AbstractGame
{
    /**
     * @var string
     */
    public $tag = "";
    public $timeout = 0;
    public $notifications = [];

    public function __construct()
    {
    }

    /**
     * @param int $chat_id
     * @return Session[]
     */
    public function getSessions(int $chat_id): array {
        $list = Session::getSessionList($chat_id);
        $res = [];
        foreach ($list as $item) {
            $session = Session::getSession($chat_id, $item);
            if ($session->game()->get() == $this->tag)
                $res[] = $session;
        }
        return $res;
    }

    public function createSession(int $chat_id): Session{
        $session = Session::createSession($chat_id, $this->tag, $this->timeout);
        foreach ($this->notifications as $notification)
            $session->notifications()->add($notification);
        return $session;
    }

    public function onCreate(GamingBot $bot, User $user, string $cmd, array $args = []){

    }

    /**
     * @param GamingBot $bot
     * @param User $user
     * @param TUpdate $update
     * @param string $cmd
     * @param string[] $args
     */
    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = []){

    }

    public function onNotify(GamingBot $bot, Session $session){

    }

    public function onEnd(GamingBot $bot, Session $session){

    }
}