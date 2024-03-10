<?php

namespace pocketmine\utils;

use pocketmine\level\format\Chunk;
use pocketmine\Player;

class ReleaseMemory extends Player
{
    public function ReleaseMemory(){
        $currentMemory = memory_get_usage();
        $memoryLimit = $this->getServer()->ReleaseMemLimit;
        if($currentMemory == $memoryLimit){
            resetMemory();
        }
        function resetMemory(){
            clearCache();
        }
        function clearCache(){
            $cacheData = new \stdClass();
        }
    }
}