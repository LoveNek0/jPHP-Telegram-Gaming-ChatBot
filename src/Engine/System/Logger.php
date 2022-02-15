<?php


namespace Engine\System;


use php\time\Time;

class Logger
{
    public static function put(string $message){
        if(!is_dir("./logs"))
            mkdir("./logs");
        $message .= "\n";
        $file = "./logs/" . Time::now()->toString("dd-MM-yyyy") . ".log";
        file_put_contents($file, $message, FILE_APPEND);
        echo $message;
    }

    public static function log(string $message){
        self::put("[" . Time::now()->toString("HH:mm:ss") . "]" . $message);
    }

    public static function info(string $message){
        self::log("[INFO] " . $message);
    }
    public static function warning(string $message){
        self::log("[WARNING] " . $message);
    }
    public static function error(string $message){
        self::log("[ERROR] " . $message);
    }
    public static function debug(string $message){
        self::log("[DEBUG] " . $message);
    }
}