<?php


namespace Commands;


use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\System\RegexPatterns;
use Engine\Templates\AbstractCommand;
use php\time\Time;

class CommandMarry extends AbstractCommand
{
    public $command = "marry";
    public $aliases = [
        "свадьба",
        "жениться",
        "брак"
    ];
    public $permission = [
        Permission::ALL
    ];
    public $descriptions = [
        "Создать брак с кем-то",
        "Сделать или принять предложение" => ["[переслать сообщение]"],
        "Отклонить свое предложение" => ["cancel|отмена"],
        "Отклонить чье-то предложение" => ["cancel|отмена", "[переслать сообщение]"],
        "Разорвать брак" => ["break|разорвать"],
        "Список предложений" => ["list|список"]
    ];

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/marry_invites.json"))
            file_put_contents("./database/marry_invites.json", json_encode([], JSON_PRETTY_PRINT));
        $list = json_decode(file_get_contents("./database/marry_invites.json"), true);

        $message = "💍 Брак\n";

        $e = false;
        if(isset($update->message->reply_to_message))
            if($update->message->reply_to_message->from->id == $user->getID()) {
                $message .= "    ❌ Нельзя использовать команду на себя";
                $e = true;
            }

        if(!$e)
            if(count($args) == 0){
                if(!isset($update->message->reply_to_message))
                    $message .= "    📔 Команда позволяет заключать и разрывать браки\n    💁‍♀️ Введи <code>.help marry</code> для справки";
                else
                    if($update->message->reply_to_message->from->is_bot)
                        $message .= "    ❌ Нельзя выйти за бота!";
                    else{
                        $partner = $update->message->reply_to_message->from->id;
                        if(!User::isExist($partner))
                            $message .= "    ❌ Пользователь не найден среди игроков!";
                        else{
                            $partner = User::getUser($partner);
                            if($user->spouse()->get() != 0) {
                                $t = User::getUser($user->spouse()->get());
                                $message .= "    ❌ Ты уже в браке с <a href=\"tg://user?id={$t->getID()}\">{$t->name()->get()}</a>";
                            }
                            elseif($partner->spouse()->get() != 0){
                                $t = User::getUser($partner->spouse()->get());
                                $message .= "    ❌ <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a> уже в браке с <a href=\"tg://user?id={$t->getID()}\">{$t->name()->get()}</a>";
                            }
                            else{
                                $t = null;
                                foreach ($list as $u_id => $data){
                                    if($u_id == "user_" . $user->getID()){
                                        $t = User::getUser($data["id"]);
                                        break;
                                    }
                                }
                                if($t != null){
                                    $message .= "    💁‍♀️ Ты уже сделал предложение <a href=\"tg://user?id={$t->getID()}\">{$t->name()->get()}</a>";
                                }
                                else
                                    if(isset($list["user_" . $partner->getID()]))
                                        if($list["user_" . $partner->getID()]["id"] == $user->getID()){
                                            $user->spouse()->set($partner->getID());
                                            $partner->spouse()->set($user->getID());
                                            $tt = [];
                                            foreach ($list as $a => $b)
                                                if($a != "user_" . $user->getID() && $a != "user_" . $partner->getID())
                                                    $tt[$a] = $b;
                                            $list = $tt;
                                            file_put_contents("./database/marry_invites.json", json_encode($list, JSON_PRETTY_PRINT));
                                            $message .= "    🥰 <a href=\"tg://user?id={$user->getID()}\">{$user->name()->get()}</a> заключил брак с <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a>";
                                        }
                                        else{
                                            $list["user_" . $user->getID()]["id"] = $partner->getID();
                                            $list["user_" . $user->getID()]["time"] = Time::millis();
                                            file_put_contents("./database/marry_invites.json", json_encode($list, JSON_PRETTY_PRINT));
                                            $message .= "    💁‍♀️ Ты сделал предложение <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a>";
                                        }
                                    else{
                                        $list["user_" . $user->getID()]["id"] = $partner->getID();
                                        $list["user_" . $user->getID()]["time"] = Time::millis();
                                        file_put_contents("./database/marry_invites.json", json_encode($list, JSON_PRETTY_PRINT));
                                        $message .= "    💁‍♀️ Ты сделал предложение <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a>";
                                    }
                            }
                        }
                    }
            }
            else if(count($args) == 1){
                if($args[0] == "break" || $args[0] == "разорвать"){
                    if(isset($update->message->reply_to_message))
                        $message .= "    ❌ Команда использована некорректно\n    💁‍♀️ Введи <code>.help marry</code> для справки";
                    else
                        if($user->spouse()->get() == 0)
                            $message .= "    ❌ Ты не в браке";
                        else{
                            $t = User::getUser($user->spouse()->get());
                            $message .= "    😔 <a href=\"tg://user?id={$user->getID()}\">{$user->name()->get()}</a> развелся с <a href=\"tg://user?id={$t->getID()}\">{$t->name()->get()}</a>";
                            $t->spouse()->set(0);
                            $user->spouse()->set(0);
                        }
                }
                elseif($args[0] == "list" || $args[0] == "список"){
                    $message .= "    📝 Список предложений к <a href=\"tg://user?id={$user->getID()}\">{$user->name()->get()}</a>\n";
                    foreach ($list as $u_id => $data)
                        if($data["id"] == $user->getID()){
                            $from = User::getUser(substr($u_id, 5));
                            $message .= "      🔹 <a href=\"tg://user?id={$from->getID()}\">{$from->name()->get()}</a> - {$data['time']}\n";
                        }
                }
                elseif($args[0] == "отмена" || $args[0] == "cancel"){
                    if(!isset($update->message->reply_to_message)){
                        $t = null;
                        foreach ($list as $a => $b)
                            if($a == "user_" . $user->getID()){
                                $t = User::getUser($b["id"]);
                                break;
                            }
                        if($t == null)
                            $message .= "    ❌ Ты никому не делал предложение";
                        else{
                            $tt = [];
                            foreach ($list as $a => $b)
                                if($a != "user_" . $user->getID())
                                    $tt[$a] = $b;
                            $list = $tt;
                            file_put_contents("./database/marry_invites.json", json_encode($list, JSON_PRETTY_PRINT));
                            $message .= "    💁‍♀️ Предложение к <a href=\"tg://user?id={$t->getID()}\">{$t->name()->get()}</a> отменено";
                        }
                    }
                    else{
                        if(!User::isExist($update->message->reply_to_message->from->id))
                            $message .= "    ❌ Пользователь не найден среди игроков!";
                        else{
                            $partner = User::getUser($update->message->reply_to_message->from->id);
                            if(!isset($list["user_" . $partner->getID()]))
                                $message .= "    ❌ Игрок <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a> не делал предложение <a href=\"tg://user?id={$user->getID()}\">{$user->name()->get()}</a>";
                            else
                                if($list["user_" . $partner->getID()]["id"] != $user->getID())
                                    $message .= "    ❌ Игрок <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a> не делал предложение <a href=\"tg://user?id={$user->getID()}\">{$user->name()->get()}</a>";
                                else{
                                    $message .= "    ❌ Игрок <a href=\"tg://user?id={$partner->getID()}\">{$partner->name()->get()}</a> отклонил предложение от <a href=\"tg://user?id={$user->getID()}\">{$user->name()->get()}</a>";
                                    $tt = [];
                                    foreach ($list as $a=>$b)
                                        if($a != "user_" . $partner->getID())
                                            $tt[$a] = $b;
                                    $list = $tt;
                                    file_put_contents("./database/marry_invites.json", json_encode($list, JSON_PRETTY_PRINT));
                                }
                        }
                    }
                }
                else
                    $message .= "    ❌ Команда использована некорректно\n    💁‍♀️ Введи <code>.help marry</code> для справки";
            }
            else
                $message .= "    ❌ Команда использована некорректно\n    💁‍♀️ Введи <code>.help marry</code> для справки";

        $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->parse_mode("HTML")->reply_to_message_id($update->message->message_id)->text($message)->query();
    }
}