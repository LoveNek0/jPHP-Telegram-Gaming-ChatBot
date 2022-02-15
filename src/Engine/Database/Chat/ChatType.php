<?php


namespace Engine\Database\Chat;


use Engine\Database\Chat;

class ChatType
{
    private $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function get(): string {
        return $this->chat->getVar("type");
    }

    public function set(string $type){
        $this->chat->setVar("type", $type);
    }
}