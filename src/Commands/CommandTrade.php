<?php


namespace Commands;




use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\System\Logger;
use Engine\System\RegexPatterns;
use Engine\Templates\AbstractCommand;
use php\time\Time;

class CommandTrade extends AbstractCommand
{
    public $command = "trade";
    public $aliases = [
        "трейд"
    ];
    public $descriptions = [
        "Перевести пользователю средства",
        "Перевести средства через ID" => ["[сумма]", "[id]"],
        "Перевести средства через сообщение" => ["[сумма]", "[пересланное сообщение]"],
    ];
    public $permission = [
        Permission::ALL
    ];

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $message = "💸 <b>Транзакция</b>\n";
        $howToUse = "    ℹ️ Для чего нужно?\n";
        $howToUse .= "      Команда нужна для перевода средств между пользователями\n\n";
        $howToUse .= "    💁‍♀️ Как использовать?\n";
        $howToUse .= "      🔹 Ввести команду\n";
        $howToUse .= "        <code>/trade [сумма] [ID]</code>\n";
        $howToUse .= "      где\n";
        $howToUse .= "        <i>сумма</i> - Сумма для перевода\n";
        $howToUse .= "        <i>ID</i> - ID пользователя\n\n";
        $howToUse .= "      🔹 Ввести команду, ответив на сообщение другого пользователя\n";
        $howToUse .= "        <code>/trade [сумма]</code>\n";
        $howToUse .= "      где\n";
        $howToUse .= "        <i>сумма</i> - Сумма для перевода\n";
        if(count($args) > 0)
            if(count($args) == 1){
                $sum = $args[0];
                if(RegexPatterns::isFloat($sum) && isset($update->message->reply_to_message))
                    if(User::isExist($update->message->reply_to_message->from->id))
                        $message .= $this->trade($update->message->chat->id, User::getUser($user->getID()), User::getUser($update->message->reply_to_message->from->id), $sum);
                    else
                        $message .= "    ❌ Пользователь не найден\n";
                else
                    $message .= $howToUse;
            }
            elseif(count($args) == 2){
                $sum = $args[0];
                $id = $args[1];
                if(RegexPatterns::isFloat($sum) && RegexPatterns::isInt($id))
                    if(User::isExist($id))
                        $message .= $this->trade($update->message->chat->id, User::getUser($user->getID()), User::getUser($id), $sum);
                    else
                        $message .= "    ❌ Пользователь не найден\n";
                else
                    $message .= $howToUse;
            }
            else
                $message .= $howToUse;
        else
            $message .= $howToUse;
        $bot->getAPI()->sendMessage()->parse_mode("HTML")->reply_to_message_id($update->message->message_id)->text($message)->chat_id($update->message->chat->id)->query();
    }

    private function trade(int $chat_id, User $from, User $to, float $sum): string{
        if (!is_dir("./database/"))
            mkdir("./database/");
        if (!is_file("./database/transactions.json"))
            file_put_contents("./database/transactions.json", json_encode([], JSON_PRETTY_PRINT));
        $list = json_decode(file_get_contents("./database/transactions.json"), true);
        if(!isset($list["chat_" . $chat_id]))
            $list["chat_" . $chat_id] = 0;
        $id = $list["chat_" . $chat_id] + 1;
        $time = Time::now();
        $message = "";
        if($from->getID() == $to->getID()){
            $message .= "    🆔 TransID: <code>{$id}</code>\n";
            $message .= "    💁‍♀️ Статус: <code>Неудачно</code>\n";
            $message .= "    ⏳ Время: <code>" . $time->toString('dd-MM-yyyy HH:mm:ss') . "</code>\n";
            $message .= "    ℹ️ Причина: <code>Невозможно произвести перевод на свой баланс</code>";
            Logger::info("Transaction with id {$id} in chat {$chat_id} on sum {$sum} from user {$from->getID()} to user {$to->getID()} was completed with status FAILED and message \"Unable to transfer to your balance\"");
        }
        elseif($from->bank()->virtual()->get() < $sum){
            $message .= "    🆔 TransID: <code>{$id}</code>\n";
            $message .= "    💁‍♀️ Статус: <code>Неудачно</code>\n";
            $message .= "    ⏳ Время: <code>" . $time->toString('dd-MM-yyyy HH:mm:ss') . "</code>\n";
            $message .= "    ℹ️ Причина: <code>Сумма транзакции превышает баланс</code>";
            Logger::info("Transaction with id {$id} in chat {$chat_id} on sum {$sum} from user {$from->getID()} to user {$to->getID()} was completed with status FAILED and message \"Transaction amount exceeds balance\"");
        }
        elseif($sum <= 0){
            $message .= "    🆔 TransID: <code>{$id}</code>\n";
            $message .= "    💁‍♀️ Статус: <code>Неудачно</code>\n";
            $message .= "    ⏳ Время: <code>" . $time->toString('dd-MM-yyyy HH:mm:ss') . "</code>\n";
            $message .= "    ℹ️ Причина: <code>Сумма транзакции должна быть больше нуля</code>";
            Logger::info("Transaction with id {$id} in chat {$chat_id} on sum {$sum} from user {$from->getID()} to user {$to->getID()} was completed with status FAILED and message \"The transaction amount must be greater than zero\"");
        }
        else {
            $list["chat_" . $chat_id] = $id;
            $from->bank()->virtual()->sub($sum);
            $to->bank()->virtual()->add($sum);

            file_put_contents("./database/transactions.json", json_encode($list, JSON_PRETTY_PRINT));

            $message .= "    🆔 TransID: <code>{$id}</code>\n";
            $message .= "    💁‍♀️ Статус: <code>Успешно</code>\n";
            $message .= "    ⏳ Время: <code>" . $time->toString('dd-MM-yyyy HH:mm:ss') . "</code>\n";
            $message .= "    💰 Баланс игроков:\n";
            $message .= "    💸 <a href=\"tg://user?id={$from->getID()}\">" . (($from->login()->get() == "")?$from->name()->get():$from->login()->get()) . "</a> - <code>" . round($from->bank()->virtual()->get(), 2) . "💲</code>\n";
            $message .= "    💸 <a href=\"tg://user?id={$to->getID()}\">" . (($to->login()->get() == "")?$to->name()->get():$to->login()->get()) . "</a> - <code>" . round($to->bank()->virtual()->get(), 2) . "💲</code>\n";

            Logger::info("Transaction with id {$id} in chat {$chat_id} on sum {$sum} from user {$from->getID()} to user {$to->getID()} was completed with status SUCCESS");
        }
        return $message;
    }
}