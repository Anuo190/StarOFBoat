<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\plugin\PluginBase;

class simple_getping_setping extends PluginBase
{
    private $ping = 0;

    public function setping(int $ping){
        return $this->ping = $ping;
    }
    public function getping(){
        return $this->ping;
    }

}