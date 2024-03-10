<?php

namespace pocketmine\entity\ai;

use pocketmine\entity\ai\AIHolder;
use pocketmine\Player;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\entity\Entity;
use pocketmine\entity\Chicken;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\event\entity\EntityDamageEvent;

class ChickenAI
{

    private $AIHolder;

    public $width = 0.6;
    private $dif = 0;

    /**
     * 固定AITICK
     * @param \pocketmine\entity\ai\AIHolder $AIHolder
     */
    public function __construct(AIHolder $AIHolder)
    {
        $this->AIHolder = $AIHolder;
        if ($this->AIHolder->getServer()->aiConfig["chicken"]) {
            $this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
                $this,
                "ChickenRandomWalkCalc"
            ]), 4);

            $this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
                $this,
                "ChickenRandomWalk"
            ]), 2);
            $this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
                $this,
                "array_clear"
            ]), 20 * 5);
        }
    }

    public function ChickenRandomWalkCalc()
    {
            //遍历存档
        foreach ($this->AIHolder->getServer()->getLevels() as $level) {

            //获得难度
            $this->dif = $this->AIHolder->getServer()->getDifficulty();

            foreach ($level->getEntities() as $zo) {
                //判断存档里面有没有鸡

                switch ($zo instanceof Chicken) {
                    case 0:
                        $this->AIHolder->Chicken[$zo->getId()] = array(
                            'ID' => $zo->getId(),
                            'IsChasing' => false,
                            'motionx' => 0,
                            'motiony' => 0,
                            'motionz' => 0,
                            'hurt' => 10,
                            'time' => 10,
                            'x' => 0,
                            'y' => 0,
                            'z' => 0,
                            'oldv3' => $zo->getLocation(),
                            'yup' => 20,
                            'up' => 0,
                            'yaw' => $zo->yaw,
                            'pitch' => 0,
                            'level' => $zo->getLevel()->getName(),
                            'xxx' => 0,
                            'zzz' => 0,
                            'gotimer' => 10,
                            'swim' => 0,
                            'jump' => 0.01,
                            'canjump' => true,
                            'drop' => false,
                            'canAttack' => 0,
                            'knockBack' => false,
                        );

                }
                //判断鸡周围的32格玩家
                switch ($this->AIHolder->willMove($zo)) {

                    case 0:
                        $zom = &$this->AIHolder->Chicken[$zo->getId()];
                        $zom['x'] = $zo->getX();
                        $zom['y'] = $zo->getY();
                        $zom['z'] = $zo->getZ();

                    switch (!isset($this->AIHolder->Chicken[$zo->getId()])) {
                        case 0:
                            $zom = &$this->AIHolder->Chicken[$zo->getId()];

                        switch ($zom['IsChasing'] === false) {
                            case 0:
                            if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
                            //Calucator Move——计算移动
                            for (
                                $i = abs(mt_rand(-20, 20) / 10 -
                                $zom['motionx']);
                                $i >= mt_rand(-5, 5) / 10;
                                ) { 
                                $zom['motionx'] = $i;
                            }

                            for (
                                $z = abs(mt_rand(-20, 20) / 10 -
                                $zom['motionz']);
                                $z >= mt_rand(-5, 5) / 10;
                                ) {
                                $zom['motionz'] = $z;
                            }

                            } elseif (
                                $zom['gotimer'] >= 20 and
                                $zom['gotimer'] <= 24
                                ) {
                                    $zom['motionx'] = mt_rand(-2, 2) / 10;
                                    $zom['motionz'] = mt_rand(-2, 2) / 10;
                                  }
                                    $this->CantMove($zom, $zo);
                                    $zom['gotimer'] += 0.2;
                                    if ($zom['gotimer'] >= 22)
                                        $zom['gotimer'] = 0.2;
                                        $zom['yup'] = 0;
                                        $zom['up'] = 0;

                                }

                        }
                }

            }
        }
        
    }
    /**
     * 判断能不能移动
     */
    public function CantMove($zom,$zo)
    {
        $pos = new Vector3($zom['x'] + $zom['motionx'], 
                           floor($zo->getY()) + 1, $zom['z'] + 
                           $zom['motionz']);  
        
        //如果要跳
        $zy = $this->AIHolder->ifjump($zo->getLevel(), $pos);

        //如果不跳
        switch ($zy === false) {  
            case 0:
            //计算pos2
            $pos2 = new Vector3($zom['x'], $zom['y'], $zom['z']);
            if ($this->AIHolder->ifjump($zo->getLevel(), $pos2) === false) { 
                $pos2 = new Vector3($zom['y'] - 1); 
                $zom['up'] = 1;
                $zom['yup'] = 0;
            } else {
                $zom['motionx'] = -$zom['motionx'];
                $zom['motionz'] = -$zom['motionz'];
                $zom['up'] = 0;
            }
            case 1:
            $pos2 = new Vector3($zom['x'] + $zom['motionx'], $zy - 1, $zom['z'] + $zom['motionz']);  //目标坐标
            if ($pos2->y - $zom['y'] < 0) {
                $zom['up'] = 1;
            } else {
                $zom['up'] = 0;
            }
        }

        /**
         * 判断motionx motionz
         */
        switch ($zom['motionx'] == 0 and $zom['motionz'] == 0) {
            case 0:
            return 0;
            case 1:
            $yaw = $this->AIHolder->getyaw($zom['motionx'], $zom['motionz']);
            $zom['yaw'] = $yaw;
            $zom['pitch'] = 0;
        }

        if (!$zom['knockBack']) {
            $zom['x'] = $pos2->getX();
            $zom['z'] = $pos2->getZ();
            $zom['y'] = $pos2->getY();
            $zom['motiony'] = $pos2->getY() - $zo->getY();
        }
        $zo->setPosition($pos2);
       
    }

    public function ChickenRandomWalk()
    {
        foreach ($this->AIHolder->getServer()->getLevels() as $level) {
            switch ($zo instanceof Chicken) {
                case 0:
                foreach ($level->getEntities() as $zo) {
                    switch (isset($this->AIHolder->Chicken[$zo->getId()])) {
                        case 0:
                        $zom = &$this->AIHolder->Chicken[$zo->getId()];

                        if ($zom['canAttack'] != 0) {
                            $zom['canAttack'] -= 1;
                        }

                        case 1:
                        //降落计算
                        if ($zom['drop'] !== false) {
                            $olddrop = $zom['drop'];
                            $drop = $zom['drop'];
                            $zom['drop'] += 0.5;
                            $dropy = $zo->getY() - ($olddrop * 0.05 + 0.0125);
                            //阶梯计算
                            $posd0 = new Vector3(floor($zo->getX()), floor($dropy), 
                                                 floor($zo->getZ()));
                            $posd = new Vector3($zo->getX(), $dropy, $zo->getZ());
                      
                            switch($this->AIHolder->whatBlock($zo->getLevel(), $posd0)
                                   == "air") {
                                case 0:
                                $zo->setPosition($posd);  
                                case 1:
                            //计算i值        
                            for ($i = 1; $i <= $drop; $i += 1) {
                                $posd0->y += 1;
                                switch ($this->AIHolder->whatBlock($zo->getLevel(), 
                                        $posd0) != "block") {
                                    case 0:
                                    $posd->y = $posd0->y;
                                        
                                    $h = $zom['drop'] * $zom['drop'] / 20;
                                    $damage = $h - 3;
                                        
                                    if ($damage > 0) {
                                        $zo->attack($damage, 
                                        EntityDamageEvent::CAUSE_FALL);
                                    }
                                    $zom['drop'] = false;
                                    break;
                                    }
                                }
                            }
                        } else {
                            $drop = 0;
                        }
                       
                        $pk3 = new SetEntityMotionPacket;
                        $pk3->entities = [
                            [$zo->getID(), $zom['motionx'] / 10, 0, 
                            $zom['motionz'] / 10]
                        ];
                        foreach ($zo->getViewers() as $pl) {
                            $pl->dataPacket($pk3);
                        }

                    }
                }
            }
        }
    }
    
    /**
     * 数组清除
     * 用于清除生物
     */
    public function array_clear()
    {
        if (count($this->AIHolder->Chicken) != 0) {
            foreach ($this->AIHolder->Chicken as $eid => $info) {
                if (!($level->getEntity($eid) instanceof Entity)) {
                foreach ($this->AIHolder->getServer()->getLevels() as $level) {
                    
                        unset($this->AIHolder->Chicken[$eid]);
                        
                    }
                }
            }
        }
    }


}
