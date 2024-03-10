<?php

namespace pocketmine\StarOFBoatAPI\SimpleClass;

use pocketmine\event\TextContainer;
use pocketmine\lang\BaseLang;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class simple_lang_translate extends Player
{
    /** @var BaseLang */
    public $baseLang;

    /**
     * @param PluginBase $pluginBase
     * @param string $playerName
     * @return string|null
     */
    public function simpleLanguageTranslate(TextContainer $c): string
    {
        $player = $this->getPlayer();
        $lang = $player->getServer()->getLanguage();
        $translatedText = $lang->translate($c);
        return $translatedText;
    }
}