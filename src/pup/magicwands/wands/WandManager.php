<?php

namespace pup\magicwands\wands;

use pocketmine\item\Item;
use pocketmine\player\Player;

class WandManager
{

    /** @var MagicWand[] Wand ID to wand instance mapping */
    private array $registeredWands = [];

    /** @var array<string, string> Item hash to wand ID mapping */
    private array $itemToWandMap = [];

    public function __construct() { }

    /**
     * Register a wand type
     */
    public function registerWand(MagicWand $wand)
    : void
    {
        $this->registeredWands[$wand->getId()] = $wand;

        $item = $wand->getItem();
        $hash = $this->getItemHash($item);
        $this->itemToWandMap[$hash] = $wand->getId();
    }

    /**
     * Create a unique hash for an item
     */
    private function getItemHash(Item $item)
    : string
    {
        return $item->getName() . ":" . implode("|", $item->getLore());
    }

    /**
     * Get all registered wands
     * @return MagicWand[]
     */
    public function getAllWands()
    : array
    {
        return $this->registeredWands;
    }

    /**
     * Check if an item is a wand
     */
    public function isWand(Item $item)
    : bool
    {
        return isset($this->itemToWandMap[$this->getItemHash($item)]);
    }

    /**
     * Get wand from item
     */
    public function getWandFromItem(Item $item)
    : ?MagicWand
    {
        $hash = $this->getItemHash($item);
        $wandId = $this->itemToWandMap[$hash] ?? null;

        return $wandId !== null ? $this->getWand($wandId) : null;
    }

    /**
     * Get a wand by ID
     * @param string $id Internal wand ID (e.g., "healing", "poison")
     */
    public function getWand(string $id)
    : ?MagicWand
    {
        return $this->registeredWands[$id] ?? null;
    }

    /**
     * Give a wand to a player
     * @param Player $player
     * @param string $wandId Internal wand ID
     * @return bool
     */
    public function giveWand(Player $player, string $wandId = "all")
    : bool
    {
        if ($wandId === "all") {
            foreach ($this->registeredWands
                     as
                     $wand)
            {
                if ($player->getInventory()->canAddItem($wand->getItem())) {
                    $player->getInventory()->addItem($wand->getItem());
                }
            }
            return true;
        } else {
            $wand = $this->getWand($wandId);

            if ($wand === null) {
                return false;
            }

            $inventory = $player->getInventory();
            if ($inventory->canAddItem($wand->getItem())) {
                $inventory->addItem($wand->getItem());
                return true;
            }
        }

        return false;
    }

    /**
     * Get list of wand IDs
     * @return string[]
     */
    public function getWandIds()
    : array
    {
        return array_keys($this->registeredWands);
    }

    /**
     * Get list of wand display names
     * @return string[]
     */
    public function getWandNames()
    : array
    {
        return array_map(fn($wand) => $wand->getName(), $this->registeredWands);
    }

    /**
     * Get wand ID to display name mapping
     * @return array<string, string>
     */
    public function getWandIdToNameMap()
    : array
    {
        return array_map(function ($wand) { return $wand->getName(); }, $this->registeredWands);
    }
}