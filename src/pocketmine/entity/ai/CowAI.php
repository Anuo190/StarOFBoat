<?php

namespace pocketmine\entity\ai;

use pocketmine\entity\Ocelot;
use pocketmine\entity\Pig;
use pocketmine\entity\Sheep;
use pocketmine\entity\Wolf;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;
use pocketmine\entity\Cow;
use pocketmine\entity\Mooshroom;
use pocketmine\scheduler\CallbackTask;
use pocketmine\network\protocol\SetEntityMotionPacket;

class CowAI
{

	private $AIHolder;

	public $width = 0.3;
	private $dif = 0;

	/**
	 * 
	 * 固定AITICK
	 * @param \pocketmine\entity\ai\AIHolder $AIHolder
	 * 
	 */
	public function __construct(AIHolder $AIHolder)
	{
		$this->AIHolder = $AIHolder;
		if ($this->AIHolder->getServer()->aiConfig["cow"]) {
			$this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
				$this,
				"CowRandomWalkCalc"
			]), 2);

			$this->AIHolder->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([
				$this,
				"CowRandomWalk"
			]), 4);


		}
	}

	/**
	 * 计算牛的孙吉走路
	 */
	public function CowRandomWalkCalc()
	{
		//遍历存档
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			//获得存档难度
			$this->dif = $this->AIHolder->getServer()->getDifficulty();
			//遍历世界生物类型
			foreach ($level->getEntities() as $zo) {
				$allowedEntities = [/*牛*/Cow::NETWORK_ID, 
									/*蘑菇牛*/Mooshroom::NETWORK_ID, 
									/*猪*/Pig::NETWORK_ID, 
									/*羊*/Sheep::NETWORK_ID, 
									/*流浪者*/Ocelot::NETWORK_ID, 
									/*狼*/Wolf::NETWORK_ID];
				
				//遍历数组里是否有allowed entity里面的实体
				switch (in_array($zo::NETWORK_ID, $allowedEntities)) {
					case 0:
						$zom = &$this->AIHolder->Cow[$zo->getId()];
					switch($this->AIHolder->willMove($zo)) {
						case 0:
						if (!isset($this->AIHolder->Cow[$zo->getId()])) {
							$this->initializeCowData($zo);
							$this->updateCowMovement($zom, $zo);
						}

						
					}
				}
			}
		}
	}

	private function initializeCowData($zo)
	{
		//牛的基本信息
		$this->AIHolder->Cow[$zo->getId()] = [
			'ID' => $zo->getId(),
			'IsChasing' => false,
			'motionx' => 0,
			'motiony' => 0,
			'motionz' => 0,
			'hurt' => 10,
			'time' => 10,
			'x' => $zo->getX(),
			'y' => $zo->getY(),
			'z' => $zo->getZ(),
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
		];
	}

	//更新牛顿运动
	private function updateCowMovement(&$zom, $zo)
	{
		$zom = &$this->AIHolder->Cow[$zo->getId()];
		if ($zom['gotimer'] == 0 or $zom['gotimer'] == 10) {
			//计算运动
			for ($i = abs(mt_rand(-20, 20) / 10 - $zom['motionx']); 
				 $i >= mt_rand(-6, 6) / 10;) {
				 $zom['motionx'] = $i;
			}
			
			for ($z = abs(mt_rand(-20, 20) / 10 - $zom['motionz']);
				 $z >= mt_rand(-6, 6) / 10;) {
				 $zom['motionz'] = $z;
			}
			
		} elseif ($zom['gotimer'] >= 20 and $zom['gotimer'] <= 24) {
			$zom['motionx'] = mt_rand(-2, 2) / 10;
			$zom['motionz'] = mt_rand(-2, 2) / 10;

		}
		$zom['gotimer'] += 0.2;
		if ($zom['gotimer'] >= 22)
			$zom['gotimer'] = 0.2;

		$pos = new Vector3($zom['x'] + $zom['motionx'], floor($zo->getY()) + 1, $zom['z'] + $zom['motionz']);  //目标坐标
		$zy = $this->AIHolder->ifjump($zo->getLevel(), $pos);
		$pos2 = new Vector3($zom['x'], $zom['y'], $zom['z']);

		switch ($zy === false) {
			case 0:
			if ($this->AIHolder->ifjump($zo->getLevel(), $pos2) === false) {
				$zom['yup'] = 0;
			} else {

				$zom['motionx'] = -$zom['motionx'];
				$zom['motionz'] = -$zom['motionz'];
				$zom['up'] = 0;

			}
			case 1:
			$pos2 = new Vector3($zom['x'] + $zom['motionx'], $zy - 1, $zom['z'] + $zom['motionz']);
			if ($pos2->y - $zom['y'] < 0) {
				$zom['up'] = 1;
			} else {
				$zom['up'] = 0;
			}
		}

		switch ($zom['motionx'] == 0 and $zom['motionz'] == 0) {
			case 0:
				return;
			case 1:
			$yaw = $this->AIHolder->getyaw($zom['motionx'], $zom['motionz']);
			$zom['yaw'] = $yaw;
			$zom['pitch'] = 0;
		}


		if (!$zom['knockBack']) {
			$zom['x'] = $pos2->getX();
			$zom['z'] = $pos2->getZ();
			$zom['y'] = $pos2->getY();
		}

		$zom['motiony'] = $pos2->getY() - $zo->getY();
		$zo->setPosition($pos2);

	}

	public function CowRandomWalk()
	{
		foreach ($this->AIHolder->getServer()->getLevels() as $level) {
			foreach ($level->getEntities() as $zo) {
				$allowedEntities = [Cow::NETWORK_ID, 
									Mooshroom::NETWORK_ID, 
									Pig::NETWORK_ID, 
									Sheep::NETWORK_ID, 
									Ocelot::NETWORK_ID, 
									Wolf::NETWORK_ID];

				switch(in_array($zo::NETWORK_ID, $allowedEntities)) {
					case 0:
					$zom = &$this->AIHolder->Cow[$zo->getId()];
					switch (isset($this->AIHolder->Cow[$zo->getId()])) {
						case 0:
						if ($zom['canAttack'] != 0) {
							$zom['canAttack'] -= 1;
						}

						$downly = $zo->onGround;

						if (abs($zo->getY() - $zom['oldv3']->y) == 1 && 
							$zom['canjump'] === true) 
						{
							$zom['canjump'] = true;
							$zom['jump'] = 0.1;
						}
						else {
							if ($zom['jump'] > 0.01) {
								$zom['jump'] -= 0.2;
							} else {
								$zom['jump'] = 0;
							}
						}

						$pk3 = new SetEntityMotionPacket;
						$pk3->entities = [
							[$zo->getID(), 
							$zom['xxx'], 
							$zom['jump'] - ($downly ? 0.04 : 0), 
							$zom['zzz']]
						];
						foreach ($zo->getViewers() as $pl) {
							$pl->dataPacket($pk3);
						}
					}
				}
			}
		}
	}
	public function array_clear()
	{
			foreach ($this->AIHolder->Cow as $eid => $info) {
			switch (count($this->AIHolder->Cow) != 0) {
				case 0:
				foreach ($this->AIHolder->getServer()->getLevels() as $level) {
					if (!($level->getEntity($eid) instanceof Entity)) {
						unset($this->AIHolder->Cow[$eid]);
						
					}
				}
			}
		}
	}


}
