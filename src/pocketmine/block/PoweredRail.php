<?php

/*
 * PocketMine-iTX Genisys
 * @author PocketMine-iTX Team & iTX Technologies LLC.
 * @link http://itxtech.org 
 *       http://mcpe.asia 
 *       http://pl.zxda.net
*/

namespace pocketmine\block;

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;

class PoweredRail extends Rail{


    const SIDE_NORTH_WEST = 6;
    const SIDE_NORTH_EAST = 7;
    const SIDE_SOUTH_EAST = 8;
    const SIDE_SOUTH_WEST = 9;

    protected $id = self::POWERED_RAIL;
	/** @var Vector3 [] */
	protected $connected = [];

	public function __construct($meta = 0){
		$this->meta = $meta;//0,1,2,3,4,5
	}

	public function getName() : string{
		return "PoweredRail";
	}

	protected function update(){

		return true;
	}


	/**
	 * @param Rail $block
	 * @return bool
	 */
	public function canConnect(Rail $block){
		if($this->distanceSquared($block) > 2){
			return false;
		}
		/** @var Vector3 [] $blocks */
		if(count($blocks = self::check($this)) == 2){
			return false;
		}
		if(isset($blocks[0])){
			$v3 = $blocks[0]->subtract($this);
			$v33 = $block->subtract($this);
			if(abs($v3->x) == abs($v33->z) and abs($v3->z) == abs($v33->x)){
				return false;
			}
		}
		return $blocks;
	}

	public function isBlock(Block $block){
		if($block instanceof AIR){
			return false;
		}
		return $block;
	}

	public function connect(Rail $rail, $force = false){

		if(!$force){
			$connected = $this->canConnect($rail);
			if(!is_array($connected)){
				return false;
			}
			/** @var Vector3 [] $connected */
			$connected[] = $rail;
			switch(count($connected)){
				case  1:
					$v3 = $connected[0]->subtract($this);
					$this->meta = (($v3->y != 1) ? ($v3->x == 0 ? 0 : 1) : ($v3->z == 0 ? ($v3->x / -2) + 2.5 : ($v3->z / 2) + 4.5));
					break;
				case 2:
					$subtract = [];
					foreach($connected as $key => $value){
						$subtract[$key] = $value->subtract($this);
					}
					if(abs($subtract[0]->x) == abs($subtract[1]->z) and abs($subtract[1]->x) == abs($subtract[0]->z)){
						$v3 = $connected[0]->subtract($this)->add($connected[1]->subtract($this));
						$this->meta = $v3->x == 1 ? ($v3->z == 1 ? 6 : 9) : ($v3->z == 1 ? 7 : 8);
					}elseif($subtract[0]->y == 1 or $subtract[1]->y == 1){
						$v3 = $subtract[0]->y == 1 ? $subtract[0] : $subtract[1];
						$this->meta = $v3->x == 0 ? ($v3->x == -1 ? 4 : 5) : ($v3->x == 1 ? 2 : 3);
					}else{
						$this->meta = $subtract[0]->x == 0 ? 0 : 1;
					}
					break;
				default:
					break;
			}
		}
		$this->level->setBlock($this, Block::get($this->id, $this->meta), true, true);
		return true;
	}

    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null){
        $down = $block->getSide(Vector3::SIDE_DOWN);
        if($down->isTransparent() === false || ($down instanceof Slab && ($down->meta & 0x08) === 0x08) || ($down instanceof WoodSlab && ($down->meta & 0x08) === 0x08) || ($down instanceof Stair && ($down->meta & 0x04) === 0x04)){
            $this->getLevel()->setBlock($this, Block::get($this->id, 0));
            $up = $block->getSide(Vector3::SIDE_UP);
            if($block->getSide(Vector3::SIDE_EAST) && $block->getSide(Vector3::SIDE_WEST)){
                if($up->getSide(Vector3::SIDE_EAST)){
                    $this->setDirection(Vector3::SIDE_EAST, true);
                }
                elseif($up->getSide(Vector3::SIDE_WEST)){
                    $this->setDirection(Vector3::SIDE_WEST, true);
                }
                else{
                    $this->setDirection(Vector3::SIDE_EAST);
                }
            }
            elseif($block->getSide(Vector3::SIDE_SOUTH) && $block->getSide(Vector3::SIDE_NORTH)){
                if($up->getSide(Vector3::SIDE_SOUTH)){
                    $this->setDirection(Vector3::SIDE_SOUTH, true);
                }
                elseif($up->getSide(Vector3::SIDE_NORTH)){
                    $this->setDirection(Vector3::SIDE_NORTH, true);
                }
                else{
                    $this->setDirection(Vector3::SIDE_SOUTH);
                }
            }
            else{
                $this->setDirection(Vector3::SIDE_NORTH);
            }
            return true;
        }
        return false;
    }

	public function getHardness() {
		return 0.7;
	}

    public function isPowered(){
        return (($this->meta & 0x08) === 0x08);
    }

    /**
     * Toggles the current state of this plate
     */
    public function togglePowered(){
        $this->meta ^= 0x08;
        $this->isPowered()?$this->power = 15:$this->power = 0;
        $this->getLevel()->setBlock($this, $this, true, true);
    }

    public function setDirection($face, $isOnSlope = false){
        $extrabitset = (($this->meta & 0x08) === 0x08);
        if($face !== Vector3::SIDE_WEST && $face !== Vector3::SIDE_EAST && $face !== Vector3::SIDE_NORTH && $face !== Vector3::SIDE_SOUTH){
            throw new \Exception("This rail variant can't be on a curve!");
        }
        $this->meta = ($extrabitset?($this->meta | 0x08):($this->meta & ~0x08));
        $this->getLevel()->setBlock($this, Block::get($this->id, $this->meta));
    }

    public function isCurve(){
        return false;
    }

    public function getDrops(Item $item): array
    {
        return [[Item::POWERED_RAIL,0,1]];
    }

    public function onRedstoneUpdate($type, $power){
        if(!$this->isPowered()){
            $this->togglePowered();
        }
    }

    public function onUpdate($type){
        if($type === Level::BLOCK_UPDATE_NORMAL){
            if(($down = $this->getSide(0)) instanceof Transparent && !($down instanceof Slab && ($down->meta & 0x08) === 0x08) || ($down instanceof WoodSlab && ($down->meta & 0x08) === 0x08) || ($down instanceof Stair && ($down->meta & 0x04) === 0x04)){
                $this->getLevel()->useBreakOn($this);
                return Level::BLOCK_UPDATE_NORMAL;
            }
        }
        return false;
    }

    public function getDirection(){
        switch($this->meta){
            case 0:
            {
                return Vector3::SIDE_SOUTH;
            }
            case 1:
            {
                return Vector3::SIDE_EAST;
            }
            case 2:
            {
                return Vector3::SIDE_EAST;
            }
            case 3:
            {
                return Vector3::SIDE_WEST;
            }
            case 4:
            {
                return Vector3::SIDE_NORTH;
            }
            case 5:
            {
                return Vector3::SIDE_SOUTH;
            }
            case 6:
            {
                return self::SIDE_NORTH_WEST;
            }
            case 7:
            {
                return self::SIDE_NORTH_EAST;
            }
            case 8:
            {
                return self::SIDE_SOUTH_EAST;
            }
            case 9:
            {
                return self::SIDE_SOUTH_WEST;
            }
            default:
            {
                return Vector3::SIDE_SOUTH;
            }
        }
    }

    public function __toString(): string{
        return $this->getName() . " facing " . $this->getDirection() . ($this->isCurve()?" on a curve ":($this->isOnSlope()?" on a slope":""));
    }

    public function isOnSlope(){
        $d = $this->meta;
        return ($d == 0x02 || $d == 0x03 || $d == 0x04 || $d == 0x05);
    }

    public function canPassThrough(){
		return true;
	}
}