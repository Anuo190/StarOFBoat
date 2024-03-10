<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use synapse\Synapse;
use synapse\Player;
use raklib\protocol\DataPacket;
use synapse\network\protocol\spp\TransferPacket;

class Simple_synapse_api extends Synapse
{

    public $synapseConfig = [];

    public static $handlerList = null;

    /** @var bool */
    private $firstTime;

    /** @var Synapse */
    private $synapse = null;

    public function __construct(Player $player, bool $firstTime = true)
    {
        $this->player = $player;
        $this->firstTime = $firstTime;
    }

    public function Simple_getsynapse()
    {
        /**
         * 获取Synapse实例
         *
         * @return Synapse
         */
        return $this->synapse;
    }

    public function SynapseEnabled(): bool
    {
        return (bool) $this->synapseConfig["enabled"];
    }

    /**
     * 获取玩家是否第一次登录
     *
     * @return bool
     */
    public function isFirstTime(): bool
    {
        return $this->firstTime;
    }

    /** @var \synapse\Player */
    protected $player;

    public function getPlayer()
    {
        return $this->player;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function sendDataPacket(DataPacket $pk)
    {
        $this->interface->putPacket($pk);
    }

    public function getServerIp(): string
    {
        return $this->serverIp;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function isMainServer(): bool
    {
        return $this->isMainServer;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getHash(): string
    {
        return $this->serverIp . ":" . $this->port;
    }

    public function emergencyShutdown()
    {
    }

    public function setName($name)
    {
    }

    public function process()
    {
    }

    public function close(Player $player, $reason = "unknown reason")
    {
    }

    public function getSynapse()
    {
        return $this->synapse;
    }

    private $client;

    /** @var DataPacket[] */
    public function reconnect()
    {
        $this->client->reconnect();
    }

    public function shutdown()
    {
        $this->client->shutdown();
    }

    /**
     * 注册数据包
     *
     * @param int        $id 0-255
     * @param DataPacket $class
     */
    /** @var DataPacket[] */
    private $packetPool = [];
    public function registerPacket($id, $class)
    {
        $this->packetPool[$id] = new $class;
    }

    public function transfer(string $hash)
    {
        $clients = Synapse::getInstance()->getClientData();
        if (isset($clients[$hash])) {
            foreach ($this->getLevel()->getEntities() as $entity) {
                if (isset($entity->hasSpawned[$this->getLoaderId()])) {
                    $entity->despawnFrom($this);
                }
            }
            $pk = new TransferPacket();
            $pk->uuid = $this->uuid;
            $pk->clientHash = $hash;
            Synapse::getInstance()->sendDataPacket($pk);
        }
    }
}