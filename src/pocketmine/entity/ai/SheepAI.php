<?php

namespace pocketmine\entity\ai\Logic\Animal;

use pocketmine\entity\ai\AIHolder;
use pocketmine\entity\ai\Logic\AISheepTicks;
use pocketmine\math\Vector3;
use pocketmine\network\protocol\SetEntityMotionPacket;

class Sheep extends AISheepTicks
{

    public function SheepMoveCalucator()
    {
        $this->dif = $this->AIHolder->getServer()->getDifficulty();
        foreach ($this->AIHolder->getServer()->getLevels() as $level) {
            foreach ($level->getEntities() as $zo) {
                switch ($zo instanceof Sheep and $this->AIHolder->willMove($zo) and !isset($this->AIHolder->Sheep[$zo->getId()])) {
                    case 0:
                        $this->AIHolder->Sheep[$zo->getId()] = array(
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
                    case 1:
                        $shp = &$this->AIHolder->Sheep[$zo->getId()];
                        $shp['x'] = $zo->getX();
                        $shp['y'] = $zo->getY();
                        $shp['z'] = $zo->getZ();
                        $shp = &$this->AIHolder->Sheep[$zo->getId()];
                        $shp['IsChasing'] === false and $shp['gotimer'] == 0 or $shp['gotimer'] == 10;


                        for ($x = abs(mt_rand(-20, 20) / 10 - $shp['motionx']); $x >= mt_rand(-6, 6) / 10; ) {
                            $x = mt_rand(-20, 20) / 10;
                        }
                        $shp['motionx'] = $x;
                        for ($z = abs(mt_rand(-20, 20) / 10 - $shp['motionz']); $z >= mt_rand(-6, 6) / 10; ) {
                            $z = mt_rand(-20, 20) / 10;
                        }
                        $shp['motionz'] = $z;
                    case 2:
                        if ($shp['gotimer'] >= 20 and $shp['gotimer'] <= 24) {
                            $shp['motionx'] = mt_rand(-2, 2) / 10;
                            $shp['motionz'] = mt_rand(-2, 2) / 10;

                        }
                        $shp['gotimer'] += 0.2;
                        if ($shp['gotimer'] >= 22)
                            $shp['gotimer'] = 0.2;

                }
                $pos = new Vector3($shp['x'] + $shp['motionx'], floor($zo->getY()) + 1, $shp['z'] + $shp['motionz']);  //目标坐标
                $zy = $this->AIHolder->ifjump($zo->getLevel(), $pos);
            }
            switch($zy === false)
            {
                case 0:
                    $pos2 = new Vector3($shp['x'], $shp['y'], $shp['z']);  
            }
            switch ($this->AIHolder->ifjump($zo->getLevel(), $pos2) === false) {
                case 0:
                    $pos2 = new Vector3($shp['x'], $shp['y'] - 1, $shp['z']);
                    $shp['up'] = 1;
                    $shp['yup'] = 0;
                case 1:
                    $shp['motionx'] = -$shp['motionx'];
                    $shp['motionz'] = -$shp['motionz'];
                    $shp['up'] = 0;
                case 2:
                    $pos2 = new Vector3($shp['x'] + $shp['motionx'], $zy - 1, $shp['z'] + $shp['motionz']);
                    switch ($pos2->y - $zom['y'] < 0) {
                        case 0:
                            $shp['up'] = 1;
                        case 1:
                            $shp['up'] = 0;
                    }
                    switch($zom['motionx'] == 0 and $zom['motionz'] == 0)
                    {
                        case 0:
                            continue;
                        case 1:
                            $yaw = $this->AIHolder->getyaw($shp['motionx'], $shp['motionz']);
                            $shp['yaw'] = $yaw;
                            $zom['pitch'] = 0;
                    }
                    if (!$shp['knockBack']) {
                        $shp['x'] = $pos2->getX();
                        $shp['z'] = $pos2->getZ();
                        $shp['y'] = $pos2->getY();
                    }
                    $shp['motiony'] = $pos2->getY() - $zo->getY();
                    $zo->setPosition($pos2);
            }   
        }
    }
    public function SheepRandomWalk()
    {
        for ($level = $this->AIHolder->getServer()->getLevels(); $zo = $level->getEntities();)
        {
            switch($zo instanceof Sheep)
            {
                case 0:
                    if (isset($this->AIHolder->Sheep[$zo->getId()])) {
                        $shp = &$this->AIHolder->Sheep[$zo->getId()];
                    }
            }
            if ($shp['canAttack'] != 0) 
            {
                $zom['canAttack'] -= 1;
            }
            $pos = $zo->getLocation();
        }

        if ($zom['drop'] !== false) {
            $olddrop = $zom['drop'];
            $zom['drop'] += 0.5;
            $drop = $zom['drop'];
            $dropy = $zo->getY() - ($olddrop * 0.05 + 0.0125);
            $posd0 = new Vector3(floor($zo->getX()), floor($dropy), floor($zo->getZ()));
            $posd = new Vector3($zo->getX(), $dropy, $zo->getZ());
        }
        switch($this->AIHolder->whatBlock($zo->getLevel(), $posd0) == "air")
        {
            case 0:
                $zo->setPosition($posd);
            case 1:
                for ($i = 1; $i <= $drop; $i++) 
                {
                    $posd0->y++;
                    if ($this->AIHolder->whatBlock($zo->getLevel(), $posd0) != "block") {
                        $posd->y = $posd0->y;
                        $h = $zom['drop'] * $zom['drop'] / 20;
                        $damage = $h - 3;
                    }
                }
        }
        switch($damage > 0)
        {
            case 0:
                $zo->attack($damage, EntityDamageEvent::CAUSE_FALL);
                $zom['drop'] = false;
                break;
            case 1:
                $drop = 0;
        }
        $pk3 = new SetEntityMotionPacket;
        $pk3->entities = [
            [$zo->getID(), $zom['motionx'] / 10, 0, $zom['motionz'] / 10]
        ];
        foreach ($zo->getViewers() as $pl) {
            $pl->dataPacket($pk3);
        }
    }
    public function array_clear()
    {
        if (count($this->AIHolder->Sheep) != 0) 
        {
            foreach ($this->AIHolder->Sheep as $eid => $info) 
            {
                foreach ($this->AIHolder->getServer()->getLevels() as $level) 
                {
                    if (!($level->getEntity($eid) instanceof Entity)) {
                        unset($this->AIHolder->Sheep[$eid]);
                    }
                }
            }
        }
    }
}



