<?php


namespace Commands;


use Engine\Database\Chat;
use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\Templates\AbstractCommand;

class CommandCall extends AbstractCommand
{
    public $command = "call";
    public $aliases = [
        "призыв",
        "позвать"
    ];
    public $permission = [
        Permission::ALL
    ];
    public $descriptions = [
        "Позвать всех игроков",
        "Включить режим игнора" => ["игнор|ignore"],
        "Включить режим игнора для игрока" => ["игнор|ignore", "[сообщение]"]
    ];
    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/call_ignore.json"))
            file_put_contents("./database/call_ignore.json", json_encode([], JSON_PRETTY_PRINT));
        $ignore = json_decode(file_get_contents("./database/call_ignore.json"), true);
        $message = "🤙 Призыв\n";

        $da = false;
        if(count($args) == 0 || count($args) > 1)
            $da = true;
        else
            if($args[0] != "ignore" && $args[0] != "игнор")
                $da = true;
            else {
                if ($user->permission()->get() == Permission::MODERATOR || $user->permission()->get() == Permission::OWNER)
                    if (!isset($update->message->reply_to_message))
                        $u = $user->getID();
                    elseif ($update->message->reply_to_message->from->is_bot)
                        $u = $user->getID();
                    else
                        $u = $update->message->reply_to_message->from->id;
                else
                    $u = $update->message->reply_to_message->from->id;

                if (in_array($u, $ignore)) {
                    $n = [];
                    foreach ($ignore as $d)
                        if ($d != $u)
                            $n[] = $d;
                    $ignore = $n;
                    $message .= "    🟥 <a href=\"tg://user?id={$u}\">Пользователь</a> удален из игнор-листа\n";
                } else {
                    $ignore[] = $u;
                    $message .= "    🟥 <a href=\"tg://user?id={$u}\">Пользователь</a> добавлен в игнор-лист\n";
                }
                file_put_contents("./database/call_ignore.json", json_encode($ignore, JSON_PRETTY_PRINT));
            }

        if($da && $user->permission()->get()) {
            $calls = Chat::getChat($update->message->chat->id)->users()->get();
            if ($args > 0) {
                $m = substr($update->message->text, strlen($cmd) + 2);
                $message .= "    💬 Сообщение: <code>{$m}</code>\n";
            }
            $message .= "    💁‍♀️ Игроки";

            $list = [
                "🧚‍♀️", "🧜🏻", "👼🏿", "🧖🏼‍♂️", "🧖‍♀️",
                "🧖🏻", "👯", "👯‍♀️", "💃🏼", "🦊",
                "🐰", "🐹", "🐭", "🐱", "🐶",
                "🐻", "🐼", "🐨", "🐯", "🦁",
                "🐮", "🙉", "🙈", "🐵", "🐸",
                "🐷", "🙊", "🐒", "🐔", "🐧",
                "🐦", "🐤", "🦇", "🦉", "🦅",
                "🦆", "🐥", "🐣", "🐺", "🐗",
                "🐴", "🐝", "🦄", "🐛", "🦗",
                "🦟", "🐜", "🐞", "🐌", "🦋",
                "🕷", "🕸", "🦂", "🐢", "🐍",
                "🦎", "🦞", "🦐", "🐙", "🦑",
                "🦕", "🦖", "🦀", "🐡", "🐠",
                "🐟", "🐬", "🐳", "🦓", "🐆",
                "🐅", "🐊", "🦈", "🐋", "🦛",
                "🦏", "🐪", "🐄", "🐂", "🐃",
                "🦘", "🦒", "🐫", "🐓", "🦃",
                "🦜", "🦢", "🦚", "🦩", "🦨",
                "🦥", "🐁", "🐇", "🐀", "🦔",
                "🐾", "🐲", "🌚", "🌜", "🌸",
                "🌾", "💐", "🌷", "🌹", "🌺",
                "🌼", "🌻", "🌞", "🌝", "🌛",
                "🌘", "🌗", "🌖", "🌕", "🌑",
                "🌒", "🌓", "🌙", "🌔", "🌎",
                "🌍", "🌏", "🪐", "☄️", "🌈",
                "☃️", "⛄️", "☂️", "☔️", "🌊"
            ];

            $i = 0;
            foreach ($calls as $id) {
                if (!in_array($id, $ignore)) {
                    if ($i % 6 == 0)
                        $message .= "\n        ";
                    $message .= "<a href=\"tg://user?id={$id}\">{$list[rand(0, count($list))]}</a>";
                    $i++;
                }
            }
        }

        $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->parse_mode("HTML")->reply_to_message_id($update->message->message_id)->text($message)->query();
    }
}