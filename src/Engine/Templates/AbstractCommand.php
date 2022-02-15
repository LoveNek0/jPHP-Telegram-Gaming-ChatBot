<?php


namespace Engine\Templates;


use Engine\Database\User;
use Engine\GamingBot;
use telegram\object\TUpdate;

abstract class AbstractCommand
{
    /**
     * @var string
     * Command name
     */
    public $command = "";

    /**
     * @var string[]
     * Command aliases
     */
    public $aliases = [];

    /**
     * @var int[]
     * Command permissions
     */
    public $permission = [];

    /**
     * @var string[]|array
     * Command descriptions
     */
    public $descriptions = [];

    /**
     * @param GamingBot $bot
     * @param User $user
     * @param TUpdate $update
     * @param string $cmd
     * @param string[] $args
     * Execute when command has been called
     */
    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = []){

    }
}