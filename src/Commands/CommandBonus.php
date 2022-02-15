<?php


namespace Commands;



use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\System\RegexPatterns;
use Engine\Templates\AbstractCommand;
use php\time\Time;

class CommandBonus extends AbstractCommand
{
    public $command = "bonus";
    public $aliases = [
        "бонус"
    ];
    public $descriptions = [
        "Бонусные вирты",
        "Выдать бонусные вирты" => ["[ID|переслать сообщение]"]
    ];
    public $permission = [
        Permission::ALL
    ];

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/bonus.json"))
            file_put_contents("./database/bonus.json", json_encode([], JSON_PRETTY_PRINT));
        $list = json_decode(file_get_contents("./database/bonus.json"), true);
        $time = Time::millis();
        $id = -1;
        $message = "💰 <b>Бонус</b>\n";
        $suck = false;
        if($user->permission()->get() == Permission::MODERATOR || $user->permission()->get() == Permission::OWNER)
            if(count($args) == 1) {
                if (RegexPatterns::isInt($args[0])) {
                    $id = $args[0];
                    $suck = true;
                }
                else
                    $message .= "    ❌ ID должен быть целым числом";
            }
            elseif(count($args) == 0) {
                $suck = true;
                if(isset($update->message->reply_to_message->from))
                    $id = $update->message->reply_to_message->from->id;
                else
                    $id = $user->getID();
            }
            else
                $message .= "    ❌ Команда поддерживает только один параметр";
        else
            if(count($args) == 0) {
                if(isset($update->message->reply_to_message))
                    $message .= "    ❌ Выдавать бонусы другим пользователям могут только модераторы";
                else {
                    $id = $user->getID();
                    $suck = true;
                }
            }
            else
                $message .= "    ❌ Выдавать бонусы другим пользователям могут только модераторы";

        if($suck){
            if(!User::isExist($id))
                $message .= "    ❌ Пользователь не найден";
            else{
                $to = User::getUser($id);
                if(!isset($list["user_" . $to->getID()]))
                    $list["user_" . $to->getID()] = 0;
                $last = $list["user_" . $to->getID()];

                $coolDown = 24*60*60*1000;

                if($to->permission()->get() == Permission::VIP)
                    $coolDown /= 2;
                if($to->permission()->get() == Permission::MODERATOR)
                    $coolDown /= 4;
                if($to->permission()->get() == Permission::OWNER)
                    $coolDown = 0;

                if($time < $last + $coolDown)
                    $t = ($last + $coolDown) - $time;
                else
                    $t = $coolDown;

                $h = (int)($t/(60*60*1000));
                $m = (int)(($t - $h * (60*60*1000))/(60*1000));
                $s = (int)(($t - $h*60*60*1000 - $m*60*1000) / 1000);
                $ms = (int)($t - $h*60*60*1000 - $m*60*1000 - $s*1000);

                if($time < $last + $coolDown)
                    $message .= "    ⏳ Бонус будет доступен через <code>" . ($h>0?$h."ч ":"") . ($m>0?$m."мин ":"") . ($s>0?$s."сек ":"") . (($h+$m+$s == 0)?$ms."мс ":"") . "</code>";
                else {
                    if ($to->permission()->get() == Permission::VIP)
                        $rand = (float)(rand(10000, 50000) . "." . rand(0, 99));
                    else
                        $rand = (float)(rand(1000, 10000) . "." . rand(0, 99));
                    $message .= "    🧐 Получатель: <a href=\"tg://user?id={$to->getID()}\">" . (($to->login()->get() == "") ? $to->name()->get() : "@" . $to->login()->get()) . "</a>\n";
                    $message .= "    💵 Сумма: <code>{$rand}</code>💲\n";
                    $message .= "    ⏳ Следующий бонус будет доступен через: <code>" . ($h>0?$h."ч ":"") . ($m>0?$m."мин ":"") . ($s>0?$s."сек ":"") . (($h+$m+$s == 0)?$ms."мс ":"") . "</code>";
                    $to->bank()->virtual()->add($rand);
                    $list["user_" . $to->getID()] = $time;
                }
            }
        }
        file_put_contents("./database/bonus.json", json_encode($list, JSON_PRETTY_PRINT));
        $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->reply_to_message_id($update->message->message_id)->text($message)->parse_mode("HTML")->query();
    }
}