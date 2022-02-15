<?php

use Commands\CommandBonus;
use Commands\CommandCall;
use Commands\CommandChats;
use Commands\CommandHelp;
use Commands\CommandInfo;
use Commands\CommandMarry;
use Commands\CommandProfile;
use Commands\CommandRoulette;
use Commands\CommandTrade;
use Engine\Database\User;
use Engine\GamingBot;
use Games\GameRoulette;

$token = "775834874:AAFyLIadcVtXeP_6kaFmgY6KZL0ps1lwK9k";

$bot = new GamingBot($token);
$bot->onJoinChat(function ($update) use ($bot){
    $message = "🖖 <b>Привет, я " . (isset($bot->getAPI()->getMe()->query()->first_name)?$bot->getAPI()->getMe()->query()->first_name." ":"") . (isset($bot->getAPI()->getMe()->query()->last_name)?$bot->getAPI()->getMe()->query()->last_name:"") . "!</b>\n";
    $message .= "    💁‍♀️ <b><i>Для чего я нужнен?</i></b>\n";
    $message .= "      🔹 Я игровой чат-бот.\n";
    $message .= "      🔹 С помощью меня можно развеять скуку в чате и поднять актив!\n";
    $message .= "    💭 <b><i>Как мной пользоваться?</i></b>\n\n";
    $message .= "      🔹 Просто введи первым символом слеш (\"/\") или точку (\".\"), после чего напиши команду.\n";
    $message .= "      🔹 Что бы посмотреть список доступных команд, введи <code>/help</code> или <code>.хелп</code> .\n";
    $message .= "      🔹 Что бы посмотреть информацию о конкретной команде, введи <code>/help [команда]</code>.\n\n";
    $message .= "  🐈 <b><i>Хорошей игры!</i></b> 💞\n";
    $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->text($message)->parse_mode("HTML")->query();
});
$bot->onNewPlayer(function ($update, User $user) use ($bot){
    $message = "🖖 <b>Привет, <a href=\"tg://user?id={$user->getID()}\">" . (($user->login()->get() == "")?$user->name()->get():"@".$user->login()->get()) . "</a>, я " . (isset($bot->getAPI()->getMe()->query()->first_name)?$bot->getAPI()->getMe()->query()->first_name." ":"") . (isset($bot->getAPI()->getMe()->query()->last_name)?$bot->getAPI()->getMe()->query()->last_name:"") . "!</b>\n";
    $message .= "    💁‍♀️ <b><i>Для чего я нужнен?</i></b>\n";
    $message .= "      🔹 Я игровой чат-бот.\n";
    $message .= "      🔹 С помощью меня можно развеять скуку в чате и поднять актив!\n";
    $message .= "    💭 <b><i>Как мной пользоваться?</i></b>\n\n";
    $message .= "      🔹 Просто введи первым символом слеш (\"/\") или точку (\".\"), после чего напиши команду.\n";
    $message .= "      🔹 Что бы посмотреть список доступных команд, введи <code>/help</code> или <code>.хелп</code> .\n";
    $message .= "      🔹 Что бы посмотреть информацию о конкретной команде, введи <code>/help [команда]</code>.\n\n";
    $message .= "  🐈 <b><i>Хорошей игры!</i></b> 💞\n";
    if(isset($update->message->new_chat_member))
        $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->text($message)->parse_mode("HTML")->query();
    else
        $bot->getAPI()->sendMessage()->chat_id($update->message->chat->id)->text($message)->parse_mode("HTML")->reply_to_message_id($update->message->message_id)->query();
});

$bot->registerCommand(new CommandHelp());
$bot->registerCommand(new CommandProfile());
$bot->registerCommand(new CommandRoulette());
$bot->registerCommand(new CommandTrade());
$bot->registerCommand(new CommandBonus());
$bot->registerCommand(new CommandChats());
$bot->registerCommand(new CommandInfo());
$bot->registerCommand(new CommandCall());
$bot->registerCommand(new CommandMarry());

$bot->registerGame(new GameRoulette());

$bot->start();

