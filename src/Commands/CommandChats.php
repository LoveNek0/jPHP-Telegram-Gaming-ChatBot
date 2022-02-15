<?php


namespace Commands;




use Engine\Database\Chat;
use Engine\Database\User;
use Engine\Database\User\Permission;
use Engine\GamingBot;
use Engine\System\RegexPatterns;
use Engine\Templates\AbstractCommand;

class CommandChats extends AbstractCommand
{
    public $command = "chats";
    public $aliases = [
        "чаты",
        "чатлист"
    ];
    public $permission = [
        Permission::ALL
    ];
    public $descriptions = [
        "Список чатов с ботом",
        "Список чатов на странице" => ["[страница]"]
    ];

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $message = "💬 <b>Список чатов</b>\n";
        $incorrect = "    ❌ Команда вызвана некорректно!\n";
        $incorrect .= "    💁‍♀️ Для информации введи <code>.help chats</code>";

        $onlyPublicChats = true;
        $page = 1;
        $linksPerPage = 5;
        $chatList = Chat::getChatList();

        if($onlyPublicChats){
            $tmp = [];
            foreach ($chatList as $chat)
                if(Chat::getChat($chat)->login()->get() != "")
                    $tmp[] = $chat;
            $chatList = $tmp;
        }

        $pages = (int)(count($chatList) / $linksPerPage);
        if(((count($chatList) / $linksPerPage) - $pages) != 0.0)
            $pages++;

        if(count($args) > 1)
            $page = -1;

        if(count($args) == 1)
            if(RegexPatterns::isInt($args[0]))
                $page = $args[0];
            else
                $page = -1;

        if($page < 0)
            $message .= $incorrect;
        else
            if($page > $pages) {
                $message .= "    ❌ Номер страницы превышает их количество!\n";
                $message .= "    💁‍♀️ Введи номер от 1 до {$pages}";
            }
            else {
                $message .= "    📄 Страница: <code>{$page}/{$pages}</code>\n";
                $message .= "    📖 Список чатов\n";
                $c = 1;
                foreach ($chatList as $chat_id){
                    $chat = Chat::getChat($chat_id);
                    if($chat->type()->get() == Chat::SUPERGROUP && $chat->login()->get() != "") {
                        if ($c > ($page - 1) * $linksPerPage && $c <= ($page - 1) * $linksPerPage + $linksPerPage)
                            $message .= "      🔹 <a href=\"https://t.me/{$chat->login()->get()}\">{$chat->title()->get()}</a>\n";
                        $c++;
                    }
                }
            }

        $bot->getAPI()->sendMessage()->parse_mode("HTML")->disable_web_page_preview(true)->chat_id($update->message->chat->id)->reply_to_message_id($update->message->message_id)->text($message)->query();
    }
}