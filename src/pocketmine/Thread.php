<?php

namespace pocketmine;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\utils\MainLogger;
use pocketmine\event\entity;

abstract class Thread extends \Thread{

    /** @var \ClassLoader */
    protected $classLoader;
    protected $isKilled = false;

    public function getClassLoader(){
        return $this->classLoader;
    }

    public function setClassLoader(\ClassLoader $loader = null){
        if($loader === null){
            $loader = Server::getInstance()->getLoader();
        }
        $this->classLoader = $loader;
    }

    public function registerClassLoader(){
        if(!interface_exists("ClassLoader", false)){
            require(\pocketmine\PATH . "src/spl/ClassLoader.php");
            require(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
            require(\pocketmine\PATH . "src/pocketmine/CompatibleClassLoader.php");
        }
        if($this->classLoader !== null){
            $this->classLoader->register(true);
        }
    }

    public function start(int $options = PTHREADS_INHERIT_ALL){
        ThreadManager::getInstance()->add($this);

        if(!$this->isRunning() and !$this->isJoined() and !$this->isTerminated()){
            if($this->classLoader === null){
                $this->setClassLoader();

            }
            return parent::start($options);
        }

        return false;
    }

    /**
     * Stops the thread using the best way possible. Try to stop it yourself before calling this.
     */
    public function quit(){
        $this->isKilled = true;

        $this->notify();

        if(!$this->isJoined()){
            if(!$this->isTerminated()){
                $this->join();

            }
        }

        ThreadManager::getInstance()->remove($this);
    }

    private $threadName;

    public function getThreadName(){
        if($this->threadName === null){
            $this->threadName = (new \ReflectionClass($this))->getShortName();
        }
        return $this->threadName;
    }

}