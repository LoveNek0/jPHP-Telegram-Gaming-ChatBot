<?php


namespace Commands;




use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\Templates\AbstractCommand;

class CommandHelp extends AbstractCommand
{
    public $command = "help";
    public $aliases = [
        "помощь",
        "хелп"
    ];
    public $descriptions = [
        "Список команд",
        "Информация о команде" => ["[команда]"]
    ];
    public $permission = [
        Permission::ALL
    ];

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $message = @"❓ <b>Помощь</b>\n";
        if(count($args) > 0){
            $e = true;
            foreach ($bot->getCommandList() as $command)
                if(in_array($args[0], $command->aliases) || $args[0] == $command->command) {
                    $e = false;
                    if(!in_array($user->permission()->get(), $command->permission) && !in_array(Permission::ALL, $command->permission)) {
                        $message .= "    ⚠️ Команда \"/{$command->command}\" недоступна!";
                        break;
                    }
                    foreach ($command->descriptions as $key => $value){
                        $message .= "    🔹/".$command->command;
                        if(is_array($value))
                            foreach ($value as $item)
                                $message .= " <i>{$item}</i>";
                        else
                            $key = $value;
                        $message .= " - " . $key . "\n";
                    }
                    if(count($command->aliases) > 0) {
                        $message .= "\n    🔻 Так же команду можно вызывать как: \n";
                        foreach ($command->aliases as $alias)
                            $message .= "        🔸 <code>." . $alias . "</code>\n";
                    }
                    break;
                }
            if($e)
                $message .= "    ❌ Команда \"{$args[0]}\" не найдена!";
        }
        else{
            $message .= "    💁‍♀️ Что бы посмотреть информацию о команде, введите\n";
            $message .= "        <code>/help [команда]</code>\n\n";
            $message .= "    📓 Список доступных команд:\n";
            foreach ($bot->getCommandList() as $command){
                if(!in_array($user->permission()->get(), $command->permission) && !in_array(Permission::ALL, $command->permission))
                    continue;
                $message .= "      🔹 /" . $command->command;
                if(isset($command->descriptions[0]))
                    $message .= " - " . $command->descriptions[0];
                else {
                    foreach ($command->descriptions as $description => $params) {
                        if (is_array($params))
                            foreach ($params as $item)
                                $message .= " {$item}";
                        $message .= " - " . $description;
                        break;
                    }
                }
                $message .= "\n";
            }
        }
        $bot->getAPI()->sendMessage()->text($message)->chat_id($update->message->chat->id)->reply_to_message_id($update->message->message_id)->parse_mode("HTML")->query();
    }

}