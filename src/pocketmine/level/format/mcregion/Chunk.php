<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

namespace pocketmine\level\format\mcregion;

use pocketmine\level\format\generic\BaseFullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryStream;

class Chunk extends BaseFullChunk{

	/** @var CompoundTag */
	protected $nbt;

public function __construct($level, CompoundTag $nbt = null){
		if($nbt === null){
			$this->provider = $level;
			$this->nbt = new CompoundTag("Level", []);
			return;
		}

		$this->nbt = $nbt;

		$this->initializeTag($this->nbt, "Entities");
		$this->initializeTag($this->nbt, "TileEntities");
		$this->initializeTag($this->nbt, "TileTicks");
		$this->initializeTag($this->nbt, "BiomeColors", NBT::TAG_IntArray, array_fill(0, 256, 0));
		$this->initializeTag($this->nbt, "HeightMap", NBT::TAG_IntArray, array_fill(0, 256, 0));
		$this->initializeTag($this->nbt, "Blocks", NBT::TAG_ByteArray, str_repeat("\x00", 32768));
		$this->initializeTag($this->nbt, "Data", NBT::TAG_ByteArray, str_repeat("\x00", 16384));
		$this->initializeTag($this->nbt, "SkyLight", NBT::TAG_ByteArray, str_repeat("\x00", 16384));
		$this->initializeTag($this->nbt, "BlockLight", NBT::TAG_ByteArray, str_repeat("\x00", 16384));
		$this->initializeTag($this->nbt, "ExtraData", NBT::TAG_ByteArray, Binary::writeInt(0));

		$extraData = [];
		if(isset($this->nbt->ExtraData)){
			$stream = new BinaryStream($this->nbt->ExtraData->getValue());
			$count = $stream->getInt();
			for($i = 0; $i < $count; ++$i){
				$key = $stream->getInt();
				$extraData[$key] = $stream->getShort();
			}
		}

		parent::__construct($level, $this->nbt["xPos"], $this->nbt["zPos"], $this->nbt->Blocks->getValue(), $this->nbt->Data->getValue(), $this->nbt->SkyLight->getValue(), $this->nbt->BlockLight->getValue(), $this->nbt->BiomeColors->getValue(), $this->nbt->HeightMap->getValue(), $this->nbt->Entities->getValue(), $this->nbt->TileEntities->getValue(), $extraData);

		unset($this->nbt->Blocks, $this->nbt->Data, $this->nbt->SkyLight, $this->nbt->BlockLight, $this->nbt->BiomeColors, $this->nbt->HeightMap, $this->nbt->Biomes);
	}

	private function initializeTag(CompoundTag $nbt, $tagName, $tagType = NBT::TAG_Compound, $defaultValue = null){
		if(!isset($nbt->{$tagName}) || !($nbt->{$tagName} instanceof $tagType)){
			$nbt->{$tagName} = new $tagType($tagName, $defaultValue);
		}
	}

	public function getBlockId($x, $y, $z){
		if(isset($this->blocks{($x << 11) | ($z << 7) | $y})) return ord($this->blocks{($x << 11) | ($z << 7) | $y});
		else return 0;
	}

	public function setBlockId($x, $y, $z, $id){
		$this->blocks{($x << 11) | ($z << 7) | $y} = chr($id);
		$this->hasChanged = true;
	}

	public function getBlockData($x, $y, $z){
    $index = ($x << 10) | ($z << 6) | ($y >> 1);
    $m = ord($this->data[$index]);

    if(($y & 1) === 0){
        return $m & 0x0F;
    }else{
        return $m >> 4;
    }
}
	public function setBlockData($x, $y, $z, $data){
    $i = ($x << 10) | ($z << 6) | ($y >> 1);
    $old_m = ord($this->data{$i});
    $new_m = 0;
    if(($y & 1) === 0){
        $new_m = ($old_m & 0xf0) | ($data & 0x0f);
    }else{
        $new_m = (($data & 0x0f) << 4) | ($old_m & 0x0f);
    }
    $this->data{$i} = chr($new_m);
    $this->hasChanged = true;
}
	public function getFullBlock($x, $y, $z){
    $i = ($x << 11) | ($z << 7) | $y;
    $block = ord($this->blocks[$i]);
    $data = ord($this->data[$i >> 1]);

    if($y % 2 === 0){
        return ($block << 4) | ($data & 0x0F);
    }else{
        return ($block << 4) | ($data >> 4);
    }
}

	public function getBlock($x, $y, $z, &$blockId, &$meta = null){
		$full = $this->getFullBlock($x, $y, $z);
		$blockId = $full >> 4;
		$meta = $full & 0x0f;
	}

public function setBlock($x, $y, $z, $blockId = null, $meta = null){
    $i = ($x << 11) | ($z << 7) | $y;

    $changed = false;

    if($blockId !== null){
        $blockId = chr($blockId);
        if($this->blocks[$i] !== $blockId){
            $this->blocks[$i] = $blockId;
            $changed = true;
        }
    }

    if($meta !== null){
        $i >>= 1;
        $old_m = ord($this->data[$i]);
        if(($y & 1) === 0){
            $this->data[$i] = chr(($old_m & 0xf0) | ($meta & 0x0f));
            if(($old_m & 0x0f) !== $meta){
                $changed = true;
            }
        }else{
            $this->data[$i] = chr((($meta & 0x0f) << 4) | ($old_m & 0x0f));
            if((($old_m & 0xf0) >> 4) !== $meta){
                $changed = true;
            }
        }
    }

    if($changed){
        $this->hasChanged = true;
    }

    return $changed;
}
public function getBlockSkyLight($x, $y, $z){
		$index = ($x << 10) | ($z << 6) | ($y >> 1);
		$sl = ord($this->skyLight{$index});
		return ($y & 1) === 0 ? ($sl & 0x0F) : ($sl >> 4);
	}
public function setBlockSkyLight($x, $y, $z, $level){
    $i = ($x << 10) | ($z << 6) | ($y >> 1);
    $old_sl = ord($this->skyLight{$i});
    $new_sl = ($y & 1) === 0 ? (($old_sl & 0xf0) | ($level & 0x0f)) : ((($level & 0x0f) << 4) | ($old_sl & 0x0f));
    $this->skyLight{$i} = chr($new_sl);
    $this->hasChanged = true;
}
	public function getBlockLight($x, $y, $z){
    $blockLight = ord($this->blockLight{($x << 10) | ($z << 6) | ($y >> 1)});
    $mask = ($y & 1) === 0 ? 0x0F : 0xF0;
    return $blockLight & $mask;
}
	public function setBlockLight($x, $y, $z, $level){
    $i = ($x << 10) | ($z << 6) | ($y >> 1);
    $old_l = ord($this->blockLight{$i});
    $new_l = 0;
    if(($y & 1) === 0){
        $new_l = ($old_l & 0xf0) | ($level & 0x0f);
    }else{
        $new_l = (($level & 0x0f) << 4) | ($old_l & 0x0f);
    }
    $this->blockLight{$i} = chr($new_l);
    $this->hasChanged = true;
}

	public function getBlockIdColumn($x, $z){
		return substr($this->blocks, ($x << 11) + ($z << 7), 128);
	}

	public function getBlockDataColumn($x, $z){
		return substr($this->data, ($x << 10) + ($z << 6), 64);
	}

	public function getBlockSkyLightColumn($x, $z){
		return substr($this->skyLight, ($x << 10) + ($z << 6), 64);
	}

	public function getBlockLightColumn($x, $z){
		return substr($this->blockLight, ($x << 10) + ($z << 6), 64);
	}

	public function isLightPopulated(){
		return $this->nbt["LightPopulated"] > 0;
	}

	public function setLightPopulated($value = 1){
    if ($value) {
        $this->nbt->LightPopulated = new ByteTag("LightPopulated", 1);
    } else {
        $this->nbt->LightPopulated = new ByteTag("LightPopulated", 0);
    }
    $this->hasChanged = true;
}

	/**
	 * @return bool
	 */
	public function isPopulated(){
		return isset($this->nbt->TerrainPopulated) and $this->nbt->TerrainPopulated->getValue() > 0;
	}

	/**
	 * @param int $value
	 */
	public function setPopulated($value = 1){
		$this->nbt->TerrainPopulated = new ByteTag("TerrainPopulated", $value ? 1 : 0);
		$this->hasChanged = true;
	}

	/**
 * @return bool
 */
public function isGenerated(){
    if(isset($this->nbt->TerrainGenerated) && $this->nbt->TerrainGenerated->getValue() > 0){
        return true;
    } elseif(isset($this->nbt->TerrainPopulated) && $this->nbt->TerrainPopulated->getValue() > 0){
        return true;
    }
    return false;
}
/**
	 * @param int $value
	 */
	public function setGenerated($value = 1){
		if (!isset($this->nbt->TerrainGenerated)) {
			$this->nbt->TerrainGenerated = new ByteTag("TerrainGenerated", 0);
		}
		$this->nbt->TerrainGenerated->setValue((int) $value);
		$this->hasChanged = true;
	}

	/**
	 * @param string        $data
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function fromBinary($data, LevelProvider $provider = null){
		$nbt = new NBT(NBT::BIG_ENDIAN);

		try{
			$nbt->readCompressed($data, ZLIB_ENCODING_DEFLATE);
			$chunk = $nbt->getData();

			if(!isset($chunk->Level) or !($chunk->Level instanceof CompoundTag)){
				return null;
			}

			return new Chunk($provider instanceof LevelProvider ? $provider : McRegion::class, $chunk->Level);
		}catch(\Throwable $e){
			return null;
		}
	}

	public static function fromFastBinary($data, LevelProvider $provider = null){
    try{
        $offset = 0;

        $chunk = new Chunk($provider instanceof LevelProvider ? $provider : McRegion::class, null);
        $chunk->provider = $provider;
        $chunk->x = Binary::readInt($data, $offset);
        $offset += 4;
        $chunk->z = Binary::readInt($data, $offset);
        $offset += 4;

        $chunk->blocks = substr($data, $offset, 32768);
        $offset += 32768;
        $chunk->data = substr($data, $offset, 16384);
        $offset += 16384;
        $chunk->skyLight = substr($data, $offset, 16384);
        $offset += 16384;
        $chunk->blockLight = substr($data, $offset, 16384);
        $offset += 16384;

        $chunk->heightMap = array_values(unpack("C*", substr($data, $offset, 256)));
        $offset += 256;
        $chunk->biomeColors = array_values(unpack("N*", substr($data, $offset, 1024)));
        $offset += 1024;

        $flags = ord($data{$offset++});

        $chunk->nbt->TerrainGenerated = new ByteTag("TerrainGenerated", $flags & 0b1);
        $chunk->nbt->TerrainPopulated = new ByteTag("TerrainPopulated", ($flags >> 1) & 0b1);
        $chunk->nbt->LightPopulated = new ByteTag("LightPopulated", ($flags >> 2) & 0b1);

        return $chunk;
    }catch(\Throwable $e){
        return null;
    }
}

    public function toFastBinary(){
        $binary = Binary::writeInt($this->x) .
            Binary::writeInt($this->z) .
            $this->getBlockIdArray() .
            $this->getBlockDataArray() .
            $this->getBlockSkyLightArray() .
            $this->getBlockLightArray();

        $heightMapPacked = pack("C*", ...$this->getHeightMapArray());
        $biomeColorPacked = pack("N*", ...$this->getBiomeColorArray());
        $flags = ($this->isLightPopulated() ? 1 << 2 : 0) + ($this->isPopulated() ? 1 << 1 : 0) + ($this->isGenerated() ? 1 : 0);

        return $binary . $heightMapPacked . $biomeColorPacked . chr($flags);
    }

	public function toBinary(){
		$nbt = clone $this->getNBT();

		$nbt->xPos = new IntTag("xPos", $this->x);
		$nbt->zPos = new IntTag("zPos", $this->z);

		if($this->isGenerated()){
			$nbt->Blocks = new ByteArrayTag("Blocks", $this->getBlockIdArray());
			$nbt->Data = new ByteArrayTag("Data", $this->getBlockDataArray());
			$nbt->SkyLight = new ByteArrayTag("SkyLight", $this->getBlockSkyLightArray());
			$nbt->BlockLight = new ByteArrayTag("BlockLight", $this->getBlockLightArray());

			$nbt->BiomeColors = new IntArrayTag("BiomeColors", $this->getBiomeColorArray());

			$nbt->HeightMap = new IntArrayTag("HeightMap", $this->getHeightMapArray());
		}

		$entities = [];

		foreach($this->getEntities() as $entity){
			if(!($entity instanceof Player) and !$entity->closed){
				$entity->saveNBT();
				$entities[] = $entity->namedtag;
			}
		}

		$nbt->Entities = new ListTag("Entities", $entities);
		$nbt->Entities->setTagType(NBT::TAG_Compound);


		$tiles = [];
		foreach($this->getTiles() as $tile){
			$tile->saveNBT();
			$tiles[] = $tile->namedtag;
		}

		$nbt->TileEntities = new ListTag("TileEntities", $tiles);
		$nbt->TileEntities->setTagType(NBT::TAG_Compound);

		$extraData = new BinaryStream();
		$extraData->putInt(count($this->getBlockExtraDataArray()));
		foreach($this->getBlockExtraDataArray() as $key => $value){
			$extraData->putInt($key);
			$extraData->putShort($value);
		}

		$nbt->ExtraData = new ByteArrayTag("ExtraData", $extraData->getBuffer());

		$writer = new NBT(NBT::BIG_ENDIAN);
		$nbt->setName("Level");
		$writer->setData(new CompoundTag("", ["Level" => $nbt]));

		return $writer->writeCompressed(ZLIB_ENCODING_DEFLATE, RegionLoader::$COMPRESSION_LEVEL);
	}

	/**
	 * @return CompoundTag
	 */
	public function getNBT(){
		return $this->nbt;
	}

	/**
	 * @param int           $chunkX
	 * @param int           $chunkZ
	 * @param LevelProvider $provider
	 *
	 * @return Chunk
	 */
	public static function getEmptyChunk($chunkX, $chunkZ, LevelProvider $provider = null){
		try{
			$chunk = new Chunk($provider instanceof LevelProvider ? $provider : McRegion::class, null);
			$chunk->x = $chunkX;
			$chunk->z = $chunkZ;

			$chunk->data = str_repeat("\x00", 16384);
			$chunk->blocks = $chunk->data . $chunk->data;
			$chunk->skyLight = str_repeat("\xff", 16384);
			$chunk->blockLight = $chunk->data;

			$chunk->heightMap = array_fill(0, 256, 0);
			$chunk->biomeColors = array_fill(0, 256, 0);

			$chunk->nbt->V = new ByteTag("V", 1);
			$chunk->nbt->InhabitedTime = new LongTag("InhabitedTime", 0);
			$chunk->nbt->TerrainGenerated = new ByteTag("TerrainGenerated", 0);
			$chunk->nbt->TerrainPopulated = new ByteTag("TerrainPopulated", 0);
			$chunk->nbt->LightPopulated = new ByteTag("LightPopulated", 0);

			return $chunk;
		}catch(\Throwable $e){
			return null;
		}
	}
}