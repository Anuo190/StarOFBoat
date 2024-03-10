<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\Player;

class simple_entitydamage_by_entity_event
{
    public function simple_Entity_Damage_By_Entity_Event(EntityDamageByEntityEvent $event){
        $damager = $event->getDamager();
        $getdamager = $event->getEntity();
        if ($damager instanceof Player){
            return $damager;
        } elseif ($getdamager instanceof Player){
            return $getdamager;
        }
        return null;
    }
}