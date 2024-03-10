<?php



namespace pocketmine\block;

use pocketmine\item\Tool;
use pocketmine\level\Level;

class LitRedStoneLamp extends Solid{

    protected $id = self::LIT_REDSTONE_LAMP;

    public function __construct($meta = 0){
        $this->meta = $meta;
    }

    public function getName(): string
    {
        return "Lit Redstone Lamp";
    }

    public function getLightLevel(){
        return 15;
    }

    public function isLightSource(){
        return true;
    }

    public function getToolType(){
        return Tool::TYPE_PICKAXE;
    }

    public function onUpdate($type){
        if(!$this->isPowered()){
            $this->id=123;
            $this->getLevel()->setBlock($this, $this, true, false);
            $this->BroadcastRedstoneUpdate(Level::REDSTONE_UPDATE_BLOCK, null);
        }
    }

    public function onRedstoneUpdate($type,$power){
        if(!$this->isPowered()){
            $this->BroadcastRedstoneUpdate(Level::REDSTONE_UPDATE_BLOCK, $power);
            $this->id=123;
            $this->getLevel()->setBlock($this, $this, true, false);
            return;
        }
        return;
    }

}
