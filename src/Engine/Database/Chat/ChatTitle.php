<?php


namespace Engine\Database\Chat;


use Engine\Database\Chat;

class ChatTitle
{
    private $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function get(): string {
        return $this->chat->getVar("title");
    }

    public function set(string $title){
        $this->chat->setVar("title", $title);
    }
}