<?php

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\level\generator\normal\biome\OceanBiome;
use pocketmine\utils\Random;

class RandCage extends Populator
{
    public $baseAmount;
    //TODO!!
    public function generateIronCage(ChunkManager $level, int $chunkX, int $chunkZ)
    {
        $cageCenterX = ($chunkX * 16) + 8;
        $cageCenterZ = ($chunkZ * 16) + 8;
        $cageY = 1;

        $level->setBlockIdAt($cageCenterX, $cageY, $cageCenterZ, Block::IRON_BLOCK);

        $cageHeight = 3; // 
        for ($y = $cageY + 1; $y <= $cageY + $cageHeight; $y++) {
            $level->setBlockIdAt($cageCenterX, $y, $cageCenterZ, Block::IRON_BARS);
        }
    }

    public function setBaseAmount($amount){
        $this->baseAmount = $amount;
    }

    /**
     * @param ChunkManager $level
     * @param $chunkX
     * @param $chunkZ
     * @param Random $random
     * @return mixed
     */
    public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random)
    {
        return $this;

    }
}