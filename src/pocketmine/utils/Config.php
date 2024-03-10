<?php

namespace pocketmine\utils;

use pocketmine\scheduler\FileWriteTask;
use pocketmine\Server;

class Config
{
    const DETECT = -1;
    const PROPERTIES = 0;
    const CNF = self::PROPERTIES;
    const JSON = 1;
    const YAML = 2;
    const SERIALIZED = 4;
    const ENUM = 5;
    const ENUMERATION = self::ENUM;

    private $config = [];
    private $nestedCache = [];
    private $file;
    private $correct = false;
    private $type = self::DETECT;

    public static $formats = [
        "properties" => self::PROPERTIES,
        "cnf" => self::CNF,
        "conf" => self::CNF,
        "config" => self::CNF,
        "json" => self::JSON,
        "js" => self::JSON,
        "yml" => self::YAML,
        "yaml" => self::YAML,
        "sl" => self::SERIALIZED,
        "serialize" => self::SERIALIZED,
        "txt" => self::ENUM,
        "list" => self::ENUM,
        "enum" => self::ENUM,
    ];

    public function __construct($file, $type = self::DETECT, $default = [], &$correct = null)
    {
        $this->load($file, $type, $default);
        $correct = $this->correct;
    }

    public function reload()
    {
        $this->config = [];
        $this->nestedCache = [];
        $this->correct = false;
        $this->load($this->file);
    }

    public function load($file, $type = self::DETECT, $default = [])
    {
        $this->correct = true;
        $this->type = (int)$type;
        $this->file = $file;
        if (!is_array($default)) {
            $default = [];
        }
        if (!file_exists($file)) {
            $this->config = $default;
            $this->save();
        } else {
            if ($this->type === self::DETECT) {
                $extension = explode(".", basename($this->file));
                $extension = strtolower(trim(array_pop($extension)));
                if (isset(self::$formats[$extension])) {
                    $this->type = self::$formats[$extension];
                } else {
                    $this->correct = false;
                }
            }
            if ($this->correct === true) {
                $content = file_get_contents($this->file);
                switch ($this->type) {
                    case self::PROPERTIES:
                    case self::CNF:
                        $this->parseProperties($content);
                        break;
                    case self::JSON:
                        $this->config = json_decode($content, true);
                        break;
                    case self::YAML:
                        $content = self::fixYAMLIndexes($content);
                        $this->config = yaml_parse($content);
                        break;
                    case self::SERIALIZED:
                        $this->config = unserialize($content);
                        break;
                    case self::ENUM:
                        $this->parseList($content);
                        break;
                    default:
                        $this->correct = false;
                        return false;
                }
                if (!is_array($this->config)) {
                    $this->config = $default;
                }
                if ($this->fillDefaults($default, $this->config) > 0) {
                    $this->save();
                }
            } else {
                return false;
            }
        }
        return true;
    }

    public function check()
    {
        return $this->correct === true;
    }

    public function save($async = false)
    {
        if ($this->correct === true) {
            try {
                $content = null;
                switch ($this->type) {
                    case self::PROPERTIES:
                    case self::CNF:
                        $content = $this->writeProperties();
                        break;
                    case self::JSON:
                        $content = json_encode($this->config, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING);
                        break;
                    case self::YAML:
                        $content = yaml_emit($this->config, YAML_UTF8_ENCODING);
                        break;
                    case self::SERIALIZED:
                        $content = serialize($this->config);
                        break;
                    case self::ENUM:
                        $content = implode("\r\n", array_keys($this->config));
                        break;
                }

                if ($async) {
                    Server::getInstance()->getScheduler()->scheduleAsyncTask(new FileWriteTask($this->file, $content));
                } else {
                    file_put_contents($this->file, $content);
                }
            } catch (\Throwable $e) {
                $logger = Server::getInstance()->getLogger();
                $logger->critical("Could not save Config " . $this->file . ": " . $e->getMessage());
                if (\pocketmine\DEBUG > 1 && $logger instanceof MainLogger) {
                    $logger->logException($e);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    public function __get($k)
    {
        return $this->get($k);
    }

    public function set($k, $v = true)
    {
        $this->setNested($k, $v);
    }

    public function __isset($k)
    {
        return $this->exists($k);
    }

    public function __unset($k)
    {
        $this->remove($k);
    }

    public function setNested($key, $value)
    {
        $vars = explode(".", $key);
        $base = array_shift($vars);

        if (!isset($this->config[$base])) {
            $this->config[$base] = [];
        }

        $base =& $this->config[$base];

        while (count($vars) > 0) {
            $baseKey = array_shift($vars);
            if (!isset($base[$baseKey])) {
                $base[$baseKey] = [];
            }
            $base =& $base[$baseKey];
        }

        $base = $value;
        $this->nestedCache[$key] = $value;
    }

    public function getNested($key, $default = null)
    {
        if (isset($this->nestedCache[$key])) {
            return $this->nestedCache[$key];
        }

        $vars = explode(".", $key);
        $base = array_shift($vars);
        if (isset($this->config[$base])) {
            $base = $this->config[$base];
        } else {
            return $default;
        }

        while (count($vars) > 0) {
            $baseKey = array_shift($vars);
            if (is_array($base) && isset($base[$baseKey])) {
                $base = $base[$baseKey];
            } else {
                return $default;
            }
        }

        return $this->nestedCache[$key] = $base;
    }

    public function get($k, $default = false)
    {
        return ($this->correct && isset($this->config[$k])) ? $this->config[$k] : $default;
    }

    public function exists($k, $lowercase = false)
    {
        if ($lowercase) {
            $k = strtolower($k);
            $array = array_change_key_case($this->config, CASE_LOWER);
            return isset($array[$k]);
        } else {
            return isset($this->config[$k]);
        }
    }

    public function remove($k)
    {
        unset($this->config[$k]);
    }

    public function getAll($keys = false)
    {
        return ($keys ? array_keys($this->config) : $this->config);
    }

    public function setDefaults(array $defaults)
    {
        $this->fillDefaults($defaults, $this->config);
    }

    private function fillDefaults($default, &$data)
    {
        $changed = 0;
        foreach ($default as $k => $v) {
            if (is_array($v)) {
                if (!isset($data[$k]) || !is_array($data[$k])) {
                    $data[$k] = [];
                }
                $changed += $this->fillDefaults($v, $data[$k]);
            } elseif (!isset($data[$k])) {
                $data[$k] = $v;
                ++$changed;
            }
        }

        return $changed;
    }

    private function parseList($content)
    {
        foreach (explode("\n", trim(str_replace("\r\n", "\n", $content))) as $v) {
            $v = trim($v);
            if ($v == "") {
                continue;
            }
            $this->config[$v] = true;
        }
    }

    private function writeProperties()
    {
        $content = "#Properties Config file\r\n#" . date("D M j H:i:s T Y") . "\r\n";
        foreach ($this->config as $k => $v) {
            if (is_bool($v)) {
                $v = $v ? "on" : "off";
            } elseif (is_array($v)) {
                $v = implode(";", $v);
            }
            $content .= $k . "=" . $v . "\r\n";
        }

        return $content;
    }

    private function parseProperties($content)
    {
        if (preg_match_all('/([a-zA-Z0-9\-_\.]*)=([^\r\n]*)/u', $content, $matches) > 0) {
            foreach ($matches[1] as $i => $k) {
                $v = trim($matches[2][$i]);
                switch (strtolower($v)) {
                    case "on":
                    case "true":
                    case "yes":
                        $v = true;
                        break;
                    case "off":
                    case "false":
                    case "no":
                        $v = false;
                        break;
                }
                if (isset($this->config[$k])) {
                    MainLogger::getLogger()->debug("[Config] Repeated property " . $k . " on file " . $this->file);
                }
                $this->config[$k] = $v;
            }
        }
    }

    public static function fixYAMLIndexes($str)
    {
        return preg_replace("#^([ ]*)([a-zA-Z_]{1}[ ]*)\\:$#m", "$1\"$2\":", $str);
    }
}