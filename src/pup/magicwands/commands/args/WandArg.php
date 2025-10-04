<?php

namespace pup\magicwands\command\args;

use CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;
use pup\magicwands\Main;

final class WandArg extends StringEnumArgument
{
    public function getTypeName()
    : string
    {
        return "wand";
    }

    public function canParse(string $testString, CommandSender $sender)
    : bool
    {
        return $this->getValue($testString) !== null;
    }

    public function getValue(string $string)
    : ?string
    {
        $wandName = strtolower($string);
        $wandManager = Main::getInstance()->getWandManager();
        $ids = $wandManager->getWandIds();
        $ids[] = "all";

        foreach ($ids
                 as
                 $registeredName)
        {
            $registeredLower = strtolower($registeredName);
            if ($registeredLower === $wandName || str_contains($registeredLower, $wandName)) {
                return $registeredName;
            }
        }

        return null;
    }

    public function parse(string $argument, CommandSender $sender)
    : ?string
    {
        return $this->getValue($argument);
    }

    public function getEnumValues()
    : array
    {
        $wandManager = Main::getInstance()->getWandManager();
        $ids = $wandManager->getWandIds();
        $ids[] = "all";
        return $ids;
    }

    public function getEnumName()
    : string
    {
        return "wand";
    }
}