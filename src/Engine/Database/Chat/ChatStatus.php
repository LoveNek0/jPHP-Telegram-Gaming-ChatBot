<?php


namespace Engine\Database\Chat;


use Engine\Database\Chat;

class ChatStatus
{
    private $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function get(): bool {
        return $this->chat->getVar("active");
    }

    public function set(bool $status){
        $this->chat->setVar("active", $status);
    }
}