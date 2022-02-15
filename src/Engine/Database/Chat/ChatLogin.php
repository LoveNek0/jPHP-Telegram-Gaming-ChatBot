<?php


namespace Engine\Database\Chat;


use Engine\Database\Chat;

class ChatLogin
{
    private $chat;

    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    public function get(): string {
        return $this->chat->getVar("login");
    }

    public function set(string $login){
        $this->chat->setVar("login", $login);
    }
}