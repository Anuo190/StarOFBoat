<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\Player;

class simple_set_player_expierence extends Player
{
    public function setExp(int $exp)
    {
        $this->getPlayer()->setExp($exp);
        return $exp;
    }
}