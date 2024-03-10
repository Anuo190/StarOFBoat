<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\event\Cancellable;
use pocketmine\plugin\PluginBase;

class simple_cancel_event implements Cancellable
{
    private $isCancelled = false;

    public function simpleCancelEve(PluginBase $pluginBase, $yourEvent)
    {
        if ($pluginBase->{$yourEvent}) {
            $this->setCancelled(true);
        }
        return $this;
    }

    public function setCancelled($value = true)
    {
        if(!($this instanceof Cancellable)){
            throw new \BadMethodCallException("Event is not Cancellable");
        }

        $this->isCancelled = (bool) $value;
    }

    public function isCancelled(): bool
    {
        if(!($this instanceof Cancellable)){
            throw new \BadMethodCallException("Event is not Cancellable");
        }

        return $this->isCancelled;
    }
}