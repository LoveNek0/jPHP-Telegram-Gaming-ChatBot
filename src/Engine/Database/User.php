<?php


namespace Engine\Database;


use Engine\Database\User\Bank;
use Engine\Database\User\Level;
use Engine\Database\User\Login;
use Engine\Database\User\Name;
use Engine\Database\User\Permission;
use Engine\Database\User\Spouse;

class User
{
    /**
     * @param bool $asArray
     * @return int[]|User[]
     */
    public static function getUsers(bool $asArray = false): array{
        if(!is_dir("./database"))
            mkdir("./database");
        if(!is_file("./database/users.json"))
            file_put_contents("./database/users.json", json_encode([], JSON_PRETTY_PRINT));

        $list = json_decode(file_get_contents("./database/users.json"), true);
        if($asArray)
            return $list;

        $l = [];
        foreach ($list as $id => $data)
            $l[] = self::getUser(substr($id, 5));
        return $l;
    }

    /**
     * @param int $id
     * @return bool
     */
    public static function isExist(int $id): bool{
        $list = self::getUsers(true);
        foreach ($list as $u_id => $data)
            if($u_id == "user_" . $id)
                return true;
        return false;
    }

    /**
     * @param int $user_id
     * @param bool $asArray
     * @return User|array|null
     */
    public static function getUser(int $user_id, bool $asArray = false){
        if(!self::isExist($user_id))
            return null;

        $list = self::getUsers(true);
        $user_data = false;
        foreach ($list as $id => $data)
            if($id == "user_" . $user_id){
                $user_data = $data;
                break;
            }
        if($user_data == false)
            return null;

        if($asArray)
            return $user_data;

        $user = new User();
        $user->id = $user_id;
        return $user;
    }

    /**
     * @param int $id
     * @return User
     */
    public static function createUser(int $id): User{
        if(self::isExist($id))
            return self::getUser($id);
        $user = new User();
        $user->id = $id;
        $user->reset();
        return $user;
    }

    private $id;

    private function __construct()
    {

    }

    public function getID(): int{
        return $this->id;
    }

    public function reset(){
        $this->permission()->set(Permission::USER);
        $this->level()->set(0.0);
        $this->bank()->virtual()->set(2500.0);
        $this->spouse()->set(0);
        if($this->name()->get() == null)
            $this->name()->set("");
        if($this->login()->get() == null)
            $this->login()->set("");
    }

    public function name(): Name{
        return new Name($this);
    }

    public function login(): Login{
        return new Login($this);
    }

    public function permission(): Permission{
        return new Permission($this);
    }

    public function level(): Level{
        return new Level($this);
    }

    public function bank(): Bank{
        return new Bank($this);
    }

    public function spouse(): Spouse{
        return new Spouse($this);
    }

    public function setVar(string $key, $value){
        $list = json_decode(file_get_contents("./database/users.json"), true);
        $list["user_" . $this->id][$key] = $value;
        file_put_contents("./database/users.json", json_encode($list, JSON_PRETTY_PRINT));
    }

    public function getVar(string $key){
        if(!isset(self::getUser($this->id, true)[$key]))
            return null;
        return self::getUser($this->id, true)[$key];
    }
}