<?php

namespace raklib\protocol;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;


class trafficThreshold extends Packet
{
    public static $ID = 0x87;


    public function checkTrafficThreshold(PlayerJoinEvent $event)
    {
        /** @var Player $pl */
        $trafficThreashold = $pl->getServer()->trafficThreshold;
        $currentTraffic = $_SERVER['CONTENT_LENGTH'] ?? 0; // 获取当前请求的流量大小
        if ($currentTraffic == $trafficThreashold and $event->getPlayer()) {
            $pl->kick("流量已达到阈值!");
            }
        }

}