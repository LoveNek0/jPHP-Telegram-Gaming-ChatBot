<?php


namespace Commands;



use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\Templates\AbstractCommand;

class CommandProfile extends AbstractCommand
{
    public $command = "profile";
    public $aliases = [
        "профиль"
    ];
    public $descriptions = [
        "Просмотреть свой профиль",
        "Профиль игрока по id" => ["[id]"],
        "Профиль игрока по сообщению" => ["[сообщение]"]
    ];
    public $permission = [
        Permission::ALL
    ];

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $profile = null;
        if(isset($update->message->reply_to_message))
            if(User::isExist($update->message->reply_to_message->from->id))
                $profile = User::getUser($update->message->reply_to_message->from->id);
            else
                $profile = null;
        else
            if(count($args) > 0)
                if(User::isExist($args[0]))
                    $profile = User::getUser($args[0]);
                else
                    $profile = null;
            else
                $profile = $user;

        $message = "📋 <b>Профиль</b>";
        if($profile == null)
            $message .= "\n    ❌ Пользователь не найден";
        else {
            $permission = [
                "Игрок",
                "VIP",
                "Модератор",
                "Владелец"
            ];
            $message .= " <a href=\"tg://user?id={$profile->getID()}\">" . (($profile->login()->get() == "")?$profile->name()->get():"@".$profile->login()->get()) . "</a>\n";
            $message .= "    🆔 ID: <code>{$profile->getID()}</code>\n";
            $message .= "    💬 Имя: <code>{$profile->name()->get()}</code>\n";
            $message .= "    💎 Привелегия: <code>{$permission[$profile->permission()->get()]}</code>\n";
            $message .= "    ⭐️ Уровень: <code>" . round($profile->level()->get(), 3) . "🌟</code>\n";
            $message .= "    🏦 Баланс" . "\n";
            $message .= "        💵 Вирты: <code>" . round($profile->bank()->virtual()->get(), 2) . "💲</code>\n";
//            $message .= "        💳 Реалы: {$profile->getBalanceReal()}💲\n";
            $message .= "    💍 Брак: " . ($profile->spouse()->get() == 0?"Свободен":" занят(-а) <a href=\"tg://user?id={$profile->spouse()->get()}\">" . User::getUser($profile->spouse()->get())->name()->get() . "</a>") . "\n";
        }
        $bot->getAPI()->sendMessage()->text($message)->chat_id($update->message->chat->id)->reply_to_message_id($update->message->message_id)->parse_mode("HTML")->query();
    }
}