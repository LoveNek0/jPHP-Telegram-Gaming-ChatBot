<?php


namespace Commands;




use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\Templates\AbstractCommand;

class CommandInfo extends AbstractCommand
{
    public $command = "info";
    public $aliases = [
        "инфа",
        "инфо",
        "вероятность"
    ];
    public $permission = [
        Permission::ALL
    ];
    public $descriptions = [
        "Узнать вероятность чего-то",
        "Вероятность события" => ["[событие]"]
    ];
    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $f1 = rand(0, 1000000000);
        $f2 = rand(0, 1000000000);
        $f3 = ($f1 < $f2)?$f1/$f2:$f2/$f1;
        $f4 = round($f3 * 100, 2);

        $message = "🖖 <b>Вероятность события</b>\n";
        if(count($args) == 0)
            $message .= "    💁‍♀️ Вероятность составляет: <code>{$f4}</code>";
        else {
            $what = substr($update->message->text, strlen($cmd) + 2);
            $message .= "    💁‍♀️ Вероятность того, что <i>\"{$what}\"</i> составляет: <code>{$f4}%</code>";
        }
        $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->reply_to_message_id($update->message->message_id)->parse_mode("HTML")->text($message)->query();
    }
}