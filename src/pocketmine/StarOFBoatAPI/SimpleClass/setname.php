<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\Player;
use pocketmine\tile\Nameable;

class Setname extends Player
{
    private $name;

    /**
     * @param string $name
     * @return string $name
     */
    public function setName($name): string
    {
        return $name;
    }
}