<?php


namespace Engine;


use Engine\Database\Chat;
use Engine\Database\Session;
use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\System\Logger;
use Engine\Templates\AbstractCommand;
use Engine\Templates\AbstractGame;
use Exception;
use php\lang\Thread;
use php\time\Time;
use telegram\object\TUpdate;
use telegram\TelegramBotApi;
use telegram\tools\TUpdateListener;

class GamingBot
{
    /**
     * @var TelegramBotApi
     */
    private $api;
    /**
     * @var bool
     */
    private $enabled;
    /**
     * @var AbstractCommand[]
     */
    private $commands = [];

    /**
     * @var AbstractGame[]
     */
    private $games = [];

    /**
     * @var TUpdate[]
     */
    private $updateQueue = [];

    /**
     * @var int
     */
    private $lastUpdateID = 0;

    /**
     * @var callable
     */
    private $onJoinChat = null;
    /**
     * @var callable
     */
    private $onNewPlayer = null;

    public function __construct(string $token)
    {
        $this->api = new TelegramBotApi($token);
    }

    public function start(){
        Logger::info("Running the bot...");
        $this->enabled = true;
        Logger::info("Enabling listener...");
        $this->listener();
        Logger::info("Enabling handlers...");
        (new Thread(function () {
            while($this->enabled) {
                $this->handler();
                $this->sessions();
            }
        }))->start();
        Logger::info("The bot has been successfully launched");
    }
    public function stop(){
        $this->enabled = false;
    }

    public function getAPI(): TelegramBotApi{
        return $this->api;
    }

    /**
     * @param callable $callback
     */
    public function onJoinChat(callable $callback){
        $this->onJoinChat = $callback;
        Logger::info("Registering a onJoin event");
    }
    /**
     * @param callable $callback
     */
    public function onNewPlayer(callable $callback){
        $this->onNewPlayer = $callback;
        Logger::info("Registering a onNewPlayer event");
    }

    /**
     * @param AbstractCommand $command
     */
    public function registerCommand(AbstractCommand $command){
        if(!in_array($command, $this->commands)) {
            $this->commands[] = $command;
            Logger::info("Registration of the command \"{$command->command}\"");
        }
    }
    /**
     * @return AbstractCommand[]
     */
    public function getCommandList(): array {
        return $this->commands;
    }

    /**
     * @param AbstractGame $game
     */
    public function registerGame(AbstractGame $game){
        if(!in_array($game, $this->games)) {
            $this->games[] = $game;
            Logger::info("Registration of the game \"{$game->tag}\"");
        }
    }
    /**
     * @return AbstractGame[]
     */
    public function getGameList(): array {
        return $this->games;
    }
    /**
     * @param string $tag
     * @return AbstractGame|null
     */
    public function getGame(string $tag){
        $list = $this->getGameList();
        foreach ($list as $game)
            if($game->tag == $tag)
                return $game;
        return null;
    }

    private function listener(){
        $listener = new TUpdateListener($this->api);
        $listener->setAsync(true);
        $listener->setThreadsCount(5);
        $listener->addListener(function ($update){
            $this->onUpdate($update);
        });
        try {
            $listener->start();
        }catch (Exception $exception){
            Logger::error($exception->getMessage());
            $this->listener();
        }
    }

    /**
     * @param TUpdate $update
     */
    private function onUpdate($update){
        if(!in_array($update, $this->updateQueue))
            $this->updateQueue[] = $update;
        $not_end = true;
        while($not_end){
            $not_end = false;
            for($i = 0; $i < count($this->updateQueue) - 1; $i++)
                if($this->updateQueue[$i]->update_id > $this->updateQueue[$i+1]->update_id){
                    $tmp = $this->updateQueue[$i];
                    $this->updateQueue[$i] = $this->updateQueue[$i+1];
                    $this->updateQueue[$i+1] = $tmp;
                    $not_end = true;
                }
        }
    }

    private function handler(){
        if(count($this->updateQueue) == 0)
            return;

        $current = $this->updateQueue[0];
        $newArray = [];
        foreach ($this->updateQueue as $id => $item)
            if($id != 0 && $item->update_id > $this->lastUpdateID)
                $newArray[] = $item;
        $this->updateQueue = $newArray;
        if($this->lastUpdateID < $current->update_id) {
            $this->lastUpdateID = $current->update_id;
            $this->onHandle($current);
        }
    }

    /**
     * @param TUpdate $update
     */
    private function onHandle($update){
        if(isset($update->message)) {
            $message = $update->message;

            // Chat Manager
            $chat = Chat::addChat($message->chat->id);
            $chat->title()->set(
                isset($message->chat->title)?$message->chat->title:(
                    (isset($message->chat->first_name)?$message->chat->first_name:"")
                    .
                    (isset($message->chat->last_name)?$message->chat->last_name:""))
            );
            $chat->type()->set($message->chat->type);
            $chat->login()->set(isset($message->chat->username)?$message->chat->username:"");
            if(!$message->from->is_bot)
                $chat->users()->add($message->from->id);
            if(isset($message->left_chat_member))
                if($message->left_chat_member->id == $this->api->getMe()->query()->id) {
                    $chat->status()->set(false);
                    Logger::info("Bot was kicked from chat {$chat->id()}");
                }
            // End Chat Manager

            // Update user info
            $updateUser = function ($message, $user){
                if (isset($message->from->username))
                    $user->login()->set($message->from->username);
                $user->name()->set(
                    ((isset($message->from->first_name)) ? $message->from->first_name : "") .
                    ((isset($message->from->first_name) && isset($message->from->last_name)) ? " " : "") .
                    ((isset($message->from->last_name)) ? $message->from->last_name : "")
                );
            };

            if(isset($message->new_chat_member)) {
                if ($message->new_chat_member->id == $this->api->getMe()->query()->id) {
                    if ($this->onJoinChat != null)
                        call_user_func($this->onJoinChat, $update);
                    Logger::info("Bot was added to chat {$chat->id()}");
                } else
                    if (!User::isExist($message->new_chat_member->id) && !$message->new_chat_member->is_bot) {
                        $user = User::createUser($message->new_chat_member->id);

                        $updateUser($message, $user);

                        if ($user != null && $this->onNewPlayer != null)
                            call_user_func($this->onNewPlayer, $update, $user);
                    }
            }
            if (isset($message->text)){
                if (!User::isExist($message->from->id) && !$message->from->is_bot) {
                    $user = User::createUser($message->from->id);

                    $updateUser($message, $user);

                    if($user != null)
                        if($this->onNewPlayer != null)
                            call_user_func($this->onNewPlayer, $update, $user);

                    Logger::info("User {$user->getID()} was registered in bot");
                }

                if(strlen($message->text) > 1 && (str_split($message->text)[0] == "." || str_split($message->text)[0] == "/")) {
                    $cmd = "";
                    $dog = "";
                    $prs = [];
                    $i = 0;
                    $is_line = false;
                    $is_cmd = true;
                    $is_dog = false;
                    foreach (str_split(substr($message->text, 1)) as $char){
                        if($is_cmd){
                            if($char == " ")
                                $is_cmd = false;
                            else
                                if(!$is_dog)
                                    if($char == "@")
                                        $is_dog = true;
                                    else
                                        $cmd .= $char;
                                else
                                    $dog .= $char;
                        }
                        else
                            if($is_line)
                                if($char == "\"") {
                                    $is_line = false;
                                    $i++;
                                }
                                else {
                                    if (!isset($prs[$i]))
                                        $prs[$i] = "";
                                    $prs[$i] .= $char;
                                }
                            else
                                if($char == "\"")
                                    $is_line = true;
                                else
                                    if($char == " ")
                                        $i++;
                                    else{
                                        if (!isset($prs[$i]))
                                            $prs[$i] = "";
                                        $prs[$i] .= $char;
                                    }
                    }
                    if($is_dog)
                        if($dog != $this->api->getMe()->query()->username)
                            return;
                    $params = [];
                    foreach ($prs as $pr)
                        if($pr != "" && $pr != null && $pr != " ")
                            $params[] = $pr;
                    foreach ($this->commands as $command) {
                        if ($command->command == strtolower($cmd) || in_array(strtolower($cmd), $command->aliases)) {

                            $user = User::getUser($message->from->id);

                            $updateUser($message, $user);

                            if(in_array($user->permission()->get(), $command->permission) || in_array(Permission::ALL, $command->permission)) {
                                $ps = "";
                                foreach($params as $i => $prs)
                                    $ps .= "\"$prs\"" . (($i<count($params)-1)?", ":"");
                                Logger::info("User {$user->getID()} in chat {$chat->id()} has called the command \"$command->command\" " . ((count($params) == 0)?"without parameters":"with the parameter" . (count($params)==1?"":"s") . " {$ps}"));
                                $command->onCall($this, $user, $update, $cmd, $params);
                            }
                        }
                    }
                }
            }
        }
    }

    private function sessions(){
        $time = Time::millis();
        foreach (Session::getChatList() as $chat_id)
            foreach ($this->games as $game) {
                $sessions = $game->getSessions($chat_id);
                foreach ($sessions as $session)
                    if ($session->createTime()->get() + $session->lifetime()->get() <= $time) {
                        $game->onEnd($this, $session);
                        $session->destroy();
                    } elseif (count($session->notifications()->get()) > 0) {
                        if ($session->createTime()->get() + $session->notifications()->get()[0] <= $time) {
                            $game->onNotify($this, $session);
                            $session->notifications()->remove($session->notifications()->get()[0]);
                        }
                    }
            }
    }
}