<?php


namespace Engine\Database\User;


use Engine\Database\User;

class Permission
{
    public const
                ALL = -1,
                USER = 0,
                VIP = 1,
                MODERATOR = 2,
                OWNER = 3;

    /**
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get(): int {
        return $this->user->getVar("permission");
    }

    public function set(int $permission){
        $this->user->setVar("permission", $permission);
    }
}