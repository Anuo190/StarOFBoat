<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class simple_getplayer extends Player
{

    /**
     * 
     * @return \pocketmine\Player
     */
    public function simple_Get_Player(): Player
    {
        $this->getPlayer();
        return $this;
    }

}