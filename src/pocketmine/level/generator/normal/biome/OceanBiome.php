<?php

namespace pocketmine\level\generator\normal\biome;

use pocketmine\block\Block;
use pocketmine\block\BlockIds;
use pocketmine\block\Gold;
use pocketmine\block\Stone;
use pocketmine\level\generator\object\OreType;
use pocketmine\level\generator\populator\Sugarcane;
use pocketmine\level\generator\populator\TallGrass;
use pocketmine\level\generator\populator\Tree;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;


class OceanBiome extends WateryBiome
{

    public function __construct()
    {
        parent::__construct();

        $sugarcane = new Sugarcane();
        $sugarcane->setBaseAmount(6);
        $tallGrass = new TallGrass();
        $tallGrass->setBaseAmount(5);

        $this->addPopulator($sugarcane);
        $this->addPopulator($tallGrass);

        $this->setElevation(46, 68);

        $this->temperature = 0.5;
        $this->rainfall = 0.5;
        /** @var Player $pl */
        if ($this->temperature == 0.25 && $this->rainfall == 0.4 && $pl->getServer()->specialgenerator === True) {
            for ($x = 0; $x < 16; ++$x) {
                for ($z = 0; $z < 16; ++$z) {
                    $islandHeight = 127;
                    $islandRadius = 1000;
                    $distanceToCenter = sqrt(($x - 8) ** 2 + ($z - 8) ** 2);
                    $height = $islandHeight - ($distanceToCenter / $islandRadius) * $islandHeight;
                    for ($y = 0; $y < 128; ++$y) {
                        $tree = new Tree();
                        $tree->setBaseAmount(3);

                        if ($y < $height) {
                            $this->setGroundCover([
                                Block::get(BlockIds::SAND)
                            ]);
                        } elseif ($y < $height + 3) {
                            $this->setGroundCover([
                                Block::get(BlockIds::GRASS),
                                Block::get(BlockIds::STONE),
                                Block::get(BlockIds::STONE),
                                Block::get(BlockIds::STONE),
                                Block::get(BlockIds::STONE),
                                Block::get(BlockIds::STONE),
                                Block::get(BlockIds::STONE),
                                Block::get(BlockIds::COAL_ORE)
                            ]);
                                $this->addPopulator($tree);
                            }
                        }
                    }
                }
            }}

    public function getName(): string
    {
        return "Ocean";
    }}