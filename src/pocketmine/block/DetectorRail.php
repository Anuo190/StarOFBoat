<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\entity\Minecart;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\level\Level;

class DetectorRail extends PoweredRail{

    protected $id = self::DETECTOR_RAIL;

    const SIDE_NORTH_WEST = 6;
    const SIDE_NORTH_EAST = 7;
    const SIDE_SOUTH_EAST = 8;
    const SIDE_SOUTH_WEST = 9;
    public function __construct($meta = 0){
        $this->meta = $meta;
    }

    public function getName() : string {
        return "Detector Rail";
    }

    public function getHardness(){
        return 0.1;
    }

    public function getToolType(){
        return Tool::TYPE_PICKAXE;
    }
    public function onUpdate($type){
        if($type === Level::BLOCK_UPDATE_SCHEDULED){
            if($this->meta === 1 && !$this->isEntityCollided()){
                $this->meta = 0;
                $this->getLevel()->setBlock($this, Block::get($this->getId(), $this->meta), false, true, true);
                return Level::BLOCK_UPDATE_WEAK;
            }
        }
        if($type === Level::BLOCK_UPDATE_NORMAL){
            $this->getLevel()->scheduleUpdate($this, 50);
        }
        return false;
    }
    public function onEntityCollide(Entity $entity){
        if(!$this->isPowered()){
            $this->togglePowered();
        }
    }
    public function getDrops(Item $item): array{
        return [[Item::DETECTOR_RAIL,0,1]];
    }

    public function isPowered(){
        return (($this->meta & 0x01) === 0x01);
    }

    public function isEntityCollided(){
        foreach($this->getLevel()->getEntities() as $entity){
            if($entity instanceof Minecart && $this->getLevel()->getBlock($entity->getPosition()) === $this) return true;
        }
        return false;
    }
    /**
     * Toggles the current state of this plate
     */
    public function togglePowered(){
        $this->meta ^= 0x08;
        $this->isPowered()?$this->power = 15:$this->power = 0;
        $this->getLevel()->setBlock($this, $this, true, true);
    }
}
