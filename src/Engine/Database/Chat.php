<?php


namespace Engine\Database;


use Engine\Database\Chat\ChatLogin;
use Engine\Database\Chat\ChatStatus;
use Engine\Database\Chat\ChatTitle;
use Engine\Database\Chat\ChatType;
use Engine\Database\Chat\ChatUsers;
use telegram\object\TChat;

class Chat
{
    public const
                PRIVATE = "private",
                SUPERGROUP = "supergroup";
    /**
     * @return int[]
     */
    public static function getChatList(): array {
        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/chats.json"))
            file_put_contents("./database/chats.json", json_encode([], JSON_PRETTY_PRINT));
        $list = json_decode(file_get_contents("./database/chats.json"), true);
        $arr = [];
        foreach ($list as $id => $value)
            $arr[] = substr($id, 5);
        return $arr;
    }

    /**
     * @param int $chat_id
     * @return bool
     */
    public static function isExist(int $chat_id): bool{
        return self::getChat($chat_id) != null;
    }

    /**
     * @param int $id
     * @return Chat
     */
    public static function addChat(int $id): Chat{
        $chat = self::getChat($id);
        if($chat!=null)
            return $chat;

        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/chats.json"))
            file_put_contents("./database/chats.json", json_encode([], JSON_PRETTY_PRINT));

        $list = json_decode(file_get_contents("./database/chats.json"), true);
        $list["chat_" . $id] = [];
        file_put_contents("./database/chats.json", json_encode($list, JSON_PRETTY_PRINT));

        $chat = self::getChat($id);
        $chat->users()->set([]);
        $chat->title()->set("");
        $chat->login()->set("");
        $chat->type()->set("");
        $chat->status()->set(true);
        return $chat;
    }

    /**
     * @param int $id
     * @return Chat|null
     */
    public static function getChat(int $id){
        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/chats.json"))
            file_put_contents("./database/chats.json", json_encode([], JSON_PRETTY_PRINT));

        $list = json_decode(file_get_contents("./database/chats.json"), true);
        if(!isset($list["chat_" . $id]))
            return null;

        $chat = new Chat();
        $chat->id = $id;
        return $chat;
    }

    private $id;

    public function __construct()
    {
    }

    public function id(): int{
        return $this->id;
    }

    public function users(): ChatUsers{
        return new ChatUsers($this);
    }

    public function title(): ChatTitle{
        return new ChatTitle($this);
    }

    public function login(): ChatLogin{
        return new ChatLogin($this);
    }

    public function type(): ChatType{
        return new ChatType($this);
    }

    public function status(): ChatStatus{
        return new ChatStatus($this);
    }

    public function setVar(string $key, $value){
        $list = json_decode(file_get_contents("./database/chats.json"), true);
        $list["chat_" . $this->id][$key] = $value;
        file_put_contents("./database/chats.json", json_encode($list, JSON_PRETTY_PRINT));
    }

    public function getVar(string $key){
        if(!is_dir("./database/"))
            mkdir("./database/");
        if(!is_file("./database/chats.json"))
            file_put_contents("./database/chats.json", json_encode([], JSON_PRETTY_PRINT));

        $list = json_decode(file_get_contents("./database/chats.json"), true);
        if(!isset($list["chat_" . $this->id]))
            return null;
        return $list["chat_" . $this->id][$key];
    }
}