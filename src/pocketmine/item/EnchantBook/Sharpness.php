<?php

namespace pocketmine\item\EnchantBook;

use pocketmine\item\Item;

class Sharpness extends Item
{


    public function __construct($meta = 1, int $count = 1)
    {
        parent::__construct(self::ENCHANTED_BOOK,$meta,$count,"Sharpness");

        $this->hasEnchantment(9);

    }
}