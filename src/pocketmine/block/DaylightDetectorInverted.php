<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\Player;

class DaylightDetectorInverted extends DaylightDetector{
	protected $id = self::DAYLIGHT_SENSOR_INVERTED;

    public function getName(): string
    {
        return "Inverted Daylight Detector";
    }

    public function __construct($meta = 0){
        $this->meta = $meta;
    }

    public function onActivate(Item $item, Player $player = null){
        $this->id = self::DAYLIGHT_DETECTOR;
        $this->getLevel()->setBlock($this, $this, true, false);
        $this->BroadcastRedstoneUpdate(Level::REDSTONE_UPDATE_PLACE, $this->getPower());
        return true;
    }
}
