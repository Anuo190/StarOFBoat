<?php

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\particle\CriticalParticle;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Random;
use pocketmine\utils\TextFormat;

class Critical
{
    public function onHurt(EntityDamageEvent $event)
    {
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->getDamager() instanceof Player) {
                $pl = $event->getDamager();
                $air = $pl->getLevel()->getBlock(new Vector3($pl->x, $pl->y - 0.75, $pl->z))->getId();
                $air2 = $pl->getLevel()->getBlock(new Vector3($pl->x, $pl->y, $pl->z))->getId();
                if ($air === 0 and $air2 === 0 and !$pl->hasEffect(Effect::BLINDNESS)) {
                    $et = $event->getEntity();
                    $ev = new CriticalEvent($pl, $et);
                    /** @var Player $player */
                    $player->getServer()->getPluginManager()->callEvent($ev);
                    if (!$ev->isCancelled() and $player->getServer()->critical === True) {
                        $event->setDamage($event->getDamage(EntityDamageByEntityEvent::MODIFIER_BASE) * 1.5);
                        $particle = new CriticalParticle(new Vector3($et->x, $et->y + 1, $et->z));
                        $random = new Random((int)(microtime(true) * 1000) + mt_rand());
                        for ($i = 0; $i < 60; ++$i) {
                            $particle->setComponents(
                                $et->x + $random->nextSignedFloat() * $et->x,
                                $et->y + 1.5 + $random->nextSignedFloat() * $et->y + 1.5,
                                $et->z + $random->nextSignedFloat() * $et->z
                            );
                            $pl->getLevel()->addParticle($particle);
                        }
                    }
                }
            }
        }
    }
}