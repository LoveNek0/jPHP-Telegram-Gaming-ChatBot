<?php


namespace Engine\Database;


use Engine\Database\Sessions\SessionBets;
use Engine\Database\Sessions\SessionCreateTime;
use Engine\Database\Sessions\SessionData;
use Engine\Database\Sessions\SessionGame;
use Engine\Database\Sessions\SessionLifetime;
use Engine\Database\Sessions\SessionNotifications;
use php\io\File;
use php\io\IOException;
use php\lib\fs;
use php\time\Time;

class Session
{
    /**
     * @return int[]
     */
    public static function getChatList(): array {
        if(!is_dir("./database/"))
            mkdir("./database");
        if(!is_file("./database/sessions"))
            mkdir("./database/sessions");
        try {
            $list = (new File("./database/sessions"))->findFiles();
            $chats = [];
            foreach ($list as $item)
                if ($item->isDirectory())
                    $chats[] = $item->getName();
            return $chats;
        }
        catch (IOException $exception){
            return [];
        }
    }

    /**
     * @param int $chat_id
     * @return int[]
     */
    public static function getSessionList(int $chat_id): array {
        if(!is_dir("./database/sessions/{$chat_id}"))
            return [];
        try {
            $list = (new File("./database/sessions/{$chat_id}"))->findFiles();
            $sessions = [];
            foreach ($list as $item)
                if($item->isFile())
                    if(fs::ext($item->getPath()) == "json")
                        $sessions[] = fs::nameNoExt($item->getPath());
            return $sessions;
        }
        catch (IOException $exception){
            return [];
        }
    }

    /**
     * @param int $chat_id
     * @param int $session_id
     * @param bool $asArray
     * @return Session|null|array
     */
    public static function getSession(int $chat_id, int $session_id, bool $asArray = false){
        if(!is_file("./database/sessions/{$chat_id}/{$session_id}.json"))
            return null;
        $arr = json_decode(file_get_contents("./database/sessions/{$chat_id}/{$session_id}.json"), true);
        if($asArray)
            return $arr;
        $session = new Session();
        $session->chat_id = $chat_id;
        $session->session_id = $session_id;
        return $session;
    }

    /**
     * @param int $chat_id
     * @param string $game
     * @param int $lifetime
     * @return Session
     */
    public static function createSession(int $chat_id, string $game, int $lifetime): Session{
        if(!is_dir("./database/"))
            mkdir("./database");
        if(!is_dir("./database/sessions"))
            mkdir("./database/sessions");
        if(!is_dir("./database/sessions/{$chat_id}"))
            mkdir("./database/sessions/{$chat_id}");
        if(!is_file("./database/session.json"))
            file_put_contents("./database/session.json", json_encode([], JSON_PRETTY_PRINT));

        $sessionList = json_decode(file_get_contents("./database/session.json"), true);

        if(!isset($sessionList["chat_" . $chat_id]))
            $sessionList["chat_" . $chat_id] = 0;

        $session = new Session();
        $session->chat_id = $chat_id;
        $session->session_id = $sessionList["chat_" . $chat_id] + 1;

        $sessionList["chat_" . $chat_id] = $session->session_id;

        file_put_contents("./database/session.json", json_encode($sessionList, JSON_PRETTY_PRINT));

        if(!is_file("./database/sessions/{$session->chatID()}/{$session->sessionID()}.json"))
            file_put_contents("./database/sessions/{$session->chatID()}/{$session->sessionID()}.json", json_encode([], JSON_PRETTY_PRINT));

        $session->createTime()->set(Time::millis());
        $session->bets()->set([]);
        $session->lifetime()->set($lifetime);
        $session->game()->set($game);
        $session->data()->set("");
        $session->notifications()->set([]);

        return $session;
    }

    public static function killSession(Session $session){
        unlink("./database/sessions/{$session->chat_id}/{$session->session_id}.json");
        $f = new File("./database/sessions/{$session->chat_id}/");
        $c = $f->findFiles();
        $co = 0;
        foreach ($c as $fi)
            if($fi->isFile())
                $co++;
            else
                $fi->delete();
        if($co == 0)
            $f->delete();
    }

    private $chat_id;
    private $session_id;

    private function __construct()
    {
    }

    public function chatID(): int{
        return $this->chat_id;
    }

    public function sessionID(): int{
        return $this->session_id;
    }

    public function createTime(): SessionCreateTime{
        return new SessionCreateTime($this);
    }

    public function game(): SessionGame {
        return new SessionGame($this);
    }

    public function bets(): SessionBets {
        return new SessionBets($this);
    }

    public function lifetime(): SessionLifetime{
        return new SessionLifetime($this);
    }

    public function notifications(): SessionNotifications{
        return new SessionNotifications($this);
    }

    public function data(): SessionData{
        return new SessionData($this);
    }

    public function setVar(string $key, $value){
        $session = json_decode(file_get_contents("./database/sessions/{$this->chat_id}/{$this->session_id}.json"), true);
        $session[$key] = $value;
        file_put_contents("./database/sessions/{$this->chat_id}/{$this->session_id}.json", json_encode($session, JSON_PRETTY_PRINT));
    }

    public function getVar(string $key){
        $session = self::getSession($this->chat_id, $this->session_id, true);
        return $session[$key];
    }

    public function destroy(){
        Session::killSession($this);
    }
}