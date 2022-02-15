<?php


namespace Commands;



use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\Templates\AbstractCommand;

class CommandRoulette extends AbstractCommand
{
    public $command = "roulette";
    public $aliases = [
        "рул",
        "рулетка",
        "ргб",
        "rgb"
    ];
    public $permission = [
        Permission::ALL
    ];
    public $descriptions = [
        "Игра в рулетку",
        "Сделать ставку в рулетке" => ["[r|g|b]", "[сумма]"]
    ];
    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $game = $bot->getGame("roulette");
        if($game != null)
            $game->onCall($bot, $user, $update, $cmd, $args);
    }
}