<?php

namespace pocketmine\entity\Anticheat;

use pocketmine\entity\Anticheat\Utils\Utils;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\TextContainer;
use pocketmine\item\Food;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\StarOFBoatAPI\SimpleClass\simple_getping_setping;


class AntiCheat extends Server
{

    /** @var array */
    protected $lastInteract = [];


    public function onInteract(PlayerInteractEvent $event)
    {
        $player = $event->getPlayer();
        if ($event->getItem() instanceof Food) {
            if ($player->getServer()->antiautogap == true)
                $this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()] = microtime(true);
            if (
                $event->getItem() instanceof Food and
                isset($this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()]) and
                (microtime(true) - ($this->lastInteract[strtolower($player->getName())][$event->getItem()->getId()])) <= $player->getServer()->antiautogaptime
            ) {
                $event->setCancelled(true);
            }
        }
    }

    public function onEntityDamage(EntityDamageEvent $event)
    {
        if (($entity = $event->getEntity()) instanceof Player and $event instanceof EntityDamageByEntityEvent and ($damager = $event->getDamager())) {
            /** @var Player $player */
            if ($player->getServer()->antireach === true) {
                if ($damager->distance($entity) > $player->getServer()->antireachdistance == 4.5) {
                    $event->setCancelled(true);
                    if ($damager->distance($entity) < $player->getServer()->antireachdistance == 4.5) {
                        $event->setCancelled(false);
                    }
                    if ($player->getServer()->antiautoclicker === true) {
                        if (isset($this->lastHit[strtolower($damager->getName())])) {
                            if ((microtime(true) - $this->lastHit[strtolower($damager->getName())]) < $player->getServer()->antiautoclickercountdown) {
                                $event->setCancelled(true);
                            } else {
                                $this->lastHit[strtolower($damager->getName())] = microtime(true);
                            }
                        } else {
                            $this->lastHit[strtolower($damager->getName())] = microtime(true);
                        }
                    }

                }

            }
        }
    }

    public function onTeleport(EntityTeleportEvent $event)
    {
        /** @var Player $player */
        if ($player->getServer()->antifly2 === true) {
            if (($entity = $event->getEntity()) instanceof Player) {
                $this->lastMovement[strtolower($entity->getName())] = [$event->getTo(), 0];
            }
        }
    }

    /** 这个移动监测也许会导致回弹 */
    public function onMove(PlayerMoveEvent $event)
    {
        /** @var Player $player */
        if ($player->getServer()->antifly2 === True) {
            /** @var simple_getping_setping $ping */
            if ($player->getGamemode() == Player::CREATIVE or $player->getGamemode() == Player::SPECTATOR or $player->getAllowFlight() or $player->getPlayer() and $ping->getping() <= 100)
                return false;
        }
        if (isset($this->lastMovement[strtolower($player->getName())])) {
            $data = $this->lastMovement[strtolower($player->getName())];
            /** @var Position $position */
            $position = $data[0];
            if ($data[1] >= ($player->getServer()->antiflyprecision < 15 ? 15 : $player->getServer()->antiflyprecision)) {
                $player->setAllowFlight(false);
                $pk = new \pocketmine\network\protocol\SetPlayerGameTypePacket();
                $pk->gamemode = Player::SURVIVAL & 0x01;
                $player->dataPacket($pk);
                $player->sendSettings();

                $player->teleport($position);
                $this->lastMovement[strtolower($player->getName())] = [$position, 0];
            } elseif ((($player->getY() - $position->getY()) < 1.5 or
                    $position->distance($player) < 6) and
                Utils::checkAround($player)) {
                $this->lastMovement[strtolower($player->getName())] = [$player->getPosition(), 0];

            } else {
                if (($player->getY() - $position->getY()) > 1.5) {
                    $this->lastMovement[strtolower($player->getName())][1]++;
                } else {
                    $this->lastMovement[strtolower($player->getName())] = [$player->getPosition(), 0];
                }
            }
        }
        return true;
    }
}