<?php


namespace Engine\Database\Chat;


use Engine\Database\Chat;

class ChatUsers
{
    private $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * @return int[]
     */
    public function get(): array {
        return $this->chat->getVar("users");
    }

    public function set(array $users){
        $this->chat->setVar("users", $users);
    }

    public function add(int $user_id){
        $list = $this->get();
        if(!in_array($user_id, $list))
            $list[] = $user_id;
        $this->set($list);
    }
}