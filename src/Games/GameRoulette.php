<?php


namespace Games;



use Engine\Database\Session;
use Engine\Database\Sessions\SessionBets\Bet;
use Engine\Database\User;
use Engine\GamingBot;
use Engine\System\RegexPatterns;
use Engine\Templates\AbstractGame;
use Exception;
use php\time\Time;

class GameRoulette extends AbstractGame
{
    public $tag = "roulette";
    public $notifications = [30000, 60000, 90000];
    public $timeout = 120000;

    public function onCall(GamingBot $bot, User $user, $update, string $cmd, $args = [])
    {
        $chat_id = $update->message->chat->id;
        $session = null;

        $sessions = $this->getSessions($chat_id);
        if(count($sessions) > 0)
            if(count($sessions) == 1)
                $session = $sessions[0];
            else
                throw new Exception("Game \"Roulette\" can`t have more then one session in chat");

        $how_to_play = "🟣 <b>Рулетка</b>\n";
        $how_to_play .= "  🗒 Правила\n";
        $how_to_play .= "    🔹 <code>1. Игра завершается успешно, если хотя бы 2 игрока поставили на разные цвета</code>\n";
        $how_to_play .= "    🔹 <code>2. В случае, когда правило 1 не выполняется, все деньги возвращаются игрокам</code>\n";
        $how_to_play .= "    🔹 <code>3. В случае выполнения правила 1, банк игры делится между победителями следующим образом:</code>\n";
        $how_to_play .= "        <code>(все_ставки)*(ставка/сумма_ставок_победителей)</code>\n";
        $how_to_play .= "      <code>Что делает распределение средств максимально справедливым.</code>\n\n";
        $how_to_play .= "  💁‍♀️ Как играть?\n";
        $how_to_play .= "    🔸 Ввести команду:\n";
        $how_to_play .= "      <code>/roulette [r|g|b] [ставка]</code>\n\n";
        $how_to_play .= "    где:\n\n";
        $how_to_play .= "      🔹 <i>первый параметр</i> - цвет для ставки:\n";
        $how_to_play .= "        🔴 <i>r (к)</i> - красный цвет\n";
        $how_to_play .= "        🟢 <i>g (з)</i> - зеленый цвет\n";
        $how_to_play .= "        ⚫️ <i>b (ч)</i> - черный цвет\n\n";
        $how_to_play .= "      🔹 <i>второй параметр</i> - ставка";

        if(count($args) == 2){
            $color = strtolower($args[0]);
            $bet = $args[1];

            if(!in_array($color, ["r", "g", "b", "к", "з", "ч"]) || !RegexPatterns::isFloat($bet))
                $message = $how_to_play;
            else {
                if ($bet > $user->bank()->virtual()->get())
                    $message = "🟣 <b>Рулетка</b>\n    ❌ Ставка перевышает ваш баланс!";
                elseif ($bet <= 0)
                    $message = "🟣 <b>Рулетка</b>\n    ❌ Ставка должна быть больше нуля!";
                else {
                    if ($session == null)
                        $session = $this->createSession($chat_id);

                    $no = false;
                    foreach ($session->bets()->get() as $be)
                        if ($be->user_id == $user->getID()) {
                            $no = true;
                            break;
                        }

                    if(!$no) {
                        if ($color == "к")
                            $color = "r";
                        if ($color == "з")
                            $color = "g";
                        if ($color == "ч")
                            $color = "b";

                        $session->bets()->add(new Bet($user->getID(), (float)$bet, $color));
                        $user->bank()->virtual()->sub((float)$bet);
                    }
                    $message = $this->getSessionInfo($session);
                }
            }
        }
        else
            if($session != null)
                $message = $this->getSessionInfo($session);
            else
                $message = $how_to_play;

        $bot->getAPI()->sendMessage()->text($message)->chat_id($update->message->chat->id)->reply_to_message_id($update->message->message_id)->parse_mode("HTML")->query();
    }

    public function onEnd(GamingBot $bot, Session $session)
    {
        $r = [];
        $g = [];
        $b = [];
        foreach ($session->bets()->get() as $bet)
            switch ($bet->data){
                case "r":
                    $r[$bet->user_id] = $bet->bet;
                    break;
                case "g":
                    $g[$bet->user_id] = $bet->bet;
                    break;
                case "b":
                    $b[$bet->user_id] = $bet->bet;
                    break;
            }
        $i = 0;
        if(count($r) > 0)
            $i++;
        if(count($g) > 0)
            $i++;
        if(count($b) > 0)
            $i++;

        $message = "🟣 <b>Рулетка</b>\n";
        if($i < 2)
            $message .= "    ❌ Сессия <code>{$session->sessionID()}</code> была завершена\n    💁‍♀️ Причина: <code>недостаточно игроков</code>";
        else {
            $dots = [
                "💚", // g
                "🖤", // b
                "❤️", // r
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️",
                "🖤",
                "❤️"
            ];
            $win = rand(0, count($dots) - 1);
            $winners = [];
            if($dots[$win] == $dots[0])
                $winners = $g;
            if($dots[$win] == $dots[1])
                $winners = $b;
            if($dots[$win] == $dots[2])
                $winners = $r;
            $line = $dots[$win];
            $count = 4;
            for ($i = $win + 1; $i <= $win + $count; $i++) {
                $now = $i;
                if ($now > count($dots) - 1)
                    $now -= (count($dots) - 1);
                $line .= $dots[$now];
            }
            for ($i = $win - 1; $i >= $win - $count; $i--) {
                $now = $i;
                if ($now < 0)
                    $now += count($dots) - 1;
                $line = $dots[$now] . $line;
            }
            for($i = 0; $i < $count*2+1; $i++)
                if($i == (int)($count))
                    $message .= "🔻";
                else
                    $message .= "➖";
            $message .= "\n" . $line . "\n";
            for($i = 0; $i < $count*2+1; $i++)
                if($i == (int)($count))
                    $message .= "🔺";
                else
                    $message .= "➖";
            $message .= "\n\n";
            $message .= "  💁‍♀️ Результаты игры\n";
            $message .= "    🆔 Сессия: <code>{$session->sessionID()}</code>\n";
            $bank = 0;
            foreach ($session->bets()->get() as $bet)
                $bank += $bet->bet;
            $message .= "    🏦 Банк игры: <code>{$bank}</code>\n";
            $message .= "    🏆 Победители:\n";
            $gameBank = 0;
            $winBank = 0;
            foreach ($r as $i => $v)
                $gameBank += $v;
            foreach ($g as $i => $v)
                $gameBank += $v;
            foreach ($b as $i => $v)
                $gameBank += $v;
            foreach ($winners as $i => $v)
                $winBank += $v;

            foreach ($winners as $winner_id => $winner_bet) {
                $user = User::getUser($winner_id);
                $cash = $winner_bet + ($gameBank - $winBank) * ($winner_bet / $winBank);
                $user->bank()->virtual()->add($cash);
                $user->level()->add(($cash / $gameBank)/100);
                $message .= "      🔹 <a href=\"tg://user?id={$user->getID()}\">" . (($user->login()->get() == "")?$user->name()->get():$user->login()->get()) . "</a> - <code>" . round($cash, 2) . "💲</code>\n";
            }
        }
        $bot->getAPI()->sendMessage()->chat_id($session->chatID())->text($message)->parse_mode("HTML")->query();
    }

    public function onNotify(GamingBot $bot, Session $session)
    {
        $bot->getAPI()->sendMessage()->chat_id($session->chatID())->text($this->getSessionInfo($session))->parse_mode("HTML")->query();
    }

    private function getSessionInfo(Session $session): string{
        $message = "🟣 <b>Рулетка</b>\n";
        $message .= "    🆔 Сессия: <code>{$session->sessionID()}</code>\n";
        $bank = 0;
        foreach ($session->bets()->get() as $bet)
            $bank += $bet->bet;
        $message .= "    🏦 Банк игры: <code>{$bank}</code>\n";

        $time = ($session->createTime()->get() + $session->lifetime()->get()) - Time::millis();
        $h = (int)($time/(60*60*1000));
        $m = (int)(($time - $h * (60*60*1000))/(60*1000));
        $s = (int)(($time - $h*60*60*1000 - $m*60*1000) / 1000);
        $ms = (int)($time - $h*60*60*1000 - $m*60*1000 - $s*1000);

        $message .= "    ⏳ Результаты через: <code>" . ($h>0?$h."ч ":"") . ($m>0?$m."мин ":"") . ($s>0?$s."сек ":"") . (($h+$m+$s == 0)?$ms."мс ":"") . "</code>\n";
        $message .= "    💰 Ставки: <code>" . (count($session->bets()->get())>0?"\n":"нет") . "</code>";
        if(count($session->bets()->get()) > 0){
            $r = [];
            $g = [];
            $b = [];
            foreach ($session->bets()->get() as $bet)
                switch ($bet->data){
                    case "r":
                        $r[$bet->user_id] = $bet->bet;
                        break;
                    case "g":
                        $g[$bet->user_id] = $bet->bet;
                        break;
                    case "b":
                        $b[$bet->user_id] = $bet->bet;
                        break;
                }

            if(count($r) > 0) {
                $message .= "      ❤️ Красный:\n";
                foreach ($r as $id => $bet) {
                    $u = User::getUser($id);
                    $message .= "        🔹 <a href=\"tg://user?id={$u->getID()}\">" . (($u->login()->get() == "") ? $u->name()->get() : $u->login()->get()) . "</a> - <code>{$bet}💲</code>\n";
                }
            }

            if(count($g) > 0) {
                $message .= "      💚 Зеленый:\n";
                foreach ($g as $id => $bet) {
                    $u = User::getUser($id);
                    $message .= "        🔹 <a href=\"tg://user?id={$u->getID()}\">" . (($u->login()->get() == "") ? $u->name()->get() : $u->login()->get()) . "</a> - <code>{$bet}💲</code>\n";
                }
            }

            if(count($b) > 0) {
                $message .= "      🖤 Черный:\n";
                foreach ($b as $id => $bet) {
                    $u = User::getUser($id);
                    $message .= "        🔹 <a href=\"tg://user?id={$u->getID()}\">" . (($u->login()->get() == "") ? $u->name()->get() : $u->login()->get()) . "</a> - <code>{$bet}💲</code>\n";
                }
            }
        }
        return $message;
    }
}