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
use pocketmine\level\sound\DoorSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\Player;

class IronTrapdoor extends Trapdoor {
	protected $id = self::IRON_TRAPDOOR;

	public function getName() : string{
		return "Iron Trapdoor";
	}

	public function getHardness() {
		return 5;
	}

	public function getResistance(){
		return 25;
	}

    public function canBeActivated(): bool
    {
        return false;
    }
    protected function recalculateBoundingBox(){

        $damage = $this->getDamage();

        $f = 0.1875;

        if(($damage & 0x08) > 0){
            $bb = new AxisAlignedBB(
                $this->x,
                $this->y + 1 - $f,
                $this->z,
                $this->x + 1,
                $this->y + 1,
                $this->z + 1
            );
        }else{
            $bb = new AxisAlignedBB(
                $this->x,
                $this->y,
                $this->z,
                $this->x + 1,
                $this->y + $f,
                $this->z + 1
            );
        }

        if(($damage & 0x04) > 0){
            if(($damage & 0x03) === 0){
                $bb->setBounds(
                    $this->x,
                    $this->y,
                    $this->z + 1 - $f,
                    $this->x + 1,
                    $this->y + 1,
                    $this->z + 1
                );
            }elseif(($damage & 0x03) === 1){
                $bb->setBounds(
                    $this->x,
                    $this->y,
                    $this->z,
                    $this->x + 1,
                    $this->y + 1,
                    $this->z + $f
                );
            }
            if(($damage & 0x03) === 2){
                $bb->setBounds(
                    $this->x + 1 - $f,
                    $this->y,
                    $this->z,
                    $this->x + 1,
                    $this->y + 1,
                    $this->z + 1
                );
            }
            if(($damage & 0x03) === 3){
                $bb->setBounds(
                    $this->x,
                    $this->y,
                    $this->z,
                    $this->x + $f,
                    $this->y + 1,
                    $this->z + 1
                );
            }
        }

        return $bb;
    }
    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
        if(($target->isTransparent() === false or $target->getId() === self::SLAB or $target->getId() === self::PACKED_ICE) and $face !== 0 and $face !== 1){
            $faces = [
                2 => 3,
                3 => 2,
                4 => 1,
                5 => 0,
            ];
            $this->meta = $faces[$face] & 0x03;
            if($fy > 0.5){
                $this->meta |= 0x04;
            }
            $this->getLevel()->setBlock($block, $this, true, true);

            return true;
        }

        return false;
    }
    public function getDrops(Item $item): array
    {
        return [
            [$this->id, 0, 1],
        ];
    }
    public function onRedstoneUpdate($type, $power){
        if(($this->isPowered() and $this->meta < 8) || (!$this->isPowered() and $this->meta > 8)){
            $this->meta ^= 0x08;
            $this->getLevel()->setBlock($this, $this);
            $this->getLevel()->addSound(new DoorSound($this));
        }
    }
}