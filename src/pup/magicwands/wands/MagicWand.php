<?php

namespace pup\magicwands\wands;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use pup\magicwands\Main;
use pup\magicwands\particles\animations\CircleSpreadAnimation;
use pup\magicwands\particles\animations\PulseAnimation;
use pup\magicwands\particles\animations\SpiralAnimation;
use pup\magicwands\spells\Spell;

class MagicWand
{

    /** @var array<string, array<string, int>> Player cooldowns per wand [playerName][wandId] => timestamp */
    private static array $playerCooldowns = [];
    private string $id;
    private string $name;
    private string $description;
    private int $cooldown;
    private float $range;
    private Item $item;
    private Spell $spell;

    public function __construct(
        string $id,
        string $name,
        string $description,
        int    $cooldown,
        float  $range,
        Spell  $spell
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->cooldown = $cooldown;
        $this->range = $range;
        $this->spell = $spell;
        $this->item = $this->createItem();
    }

    private function createItem()
    : Item
    {
        $item = VanillaItems::STICK();
        $item->setCustomName("§r§e" . $this->name);
        $item->setLore([
            "§r§7" . $this->description,
            "§r§eCooldown: §f" . ($this->cooldown / 20) . "s",
            "§r§dRange: §f" . $this->range . " blocks",
            "§r§bSpell: §f" . $this->spell->getName()
        ]);
        return $item;
    }

    public function getName()
    : string
    {
        return $this->name;
    }

    public function getId()
    : string
    {
        return $this->id;
    }

    public function onUse(Player $player, Vector3 $clickPos)
    : bool
    {
        if ($this->isOnCooldown($player)) {
            $remaining = $this->getRemainingCooldown($player);
            $player->sendTip("§cCooldown: " . round($remaining / 20, 1) . "s");
            return false;
        }

        // Execute the spell
        $success = $this->spell->cast($player, $clickPos, $this->range);

        if ($success) {
            $this->setCooldown($player);
            $this->playAnimation($player->getWorld(), $clickPos);
        }

        return $success;
    }

    private function isOnCooldown(Player $player)
    : bool
    {
        $playerName = $player->getName();
        return isset(self::$playerCooldowns[$playerName][$this->id])
            && self::$playerCooldowns[$playerName][$this->id] > time();
    }

    private function getRemainingCooldown(Player $player)
    : int
    {
        $playerName = $player->getName();
        if (!isset(self::$playerCooldowns[$playerName][$this->id])) {
            return 0;
        }
        return max(0, (self::$playerCooldowns[$playerName][$this->id] - time()) * 20);
    }

    private function setCooldown(Player $player)
    : void
    {
        $playerName = $player->getName();
        if (!isset(self::$playerCooldowns[$playerName])) {
            self::$playerCooldowns[$playerName] = [];
        }
        self::$playerCooldowns[$playerName][$this->id] = time() + ($this->cooldown / 20);
    }

    private function playAnimation(World $world, Vector3 $position)
    : void
    {
        $animType = $this->spell->getAnimationType();

        if ($animType === 'none') {
            return;
        }

        $plugin = Main::getInstance();
        $particle = $this->spell->getParticle();
        $animConfig = $this->spell->getAnimationConfig();

        switch ($animType) {
            case 'circle':
                $animation = new CircleSpreadAnimation(
                    plugin: $plugin,
                    world: $world,
                    center: $position,
                    startRadius: $animConfig['radius'] / 4,
                    targetRadius: $animConfig['radius'],
                    duration: $animConfig['duration'],
                    particleCount: $animConfig['particle_count'],
                    particle: $particle,
                    tickInterval: $animConfig['tick_interval']
                );
                $animation->start();
                break;

            case 'spiral':
                $animation = new SpiralAnimation(
                    plugin: $plugin,
                    world: $world,
                    center: $position,
                    radius: $animConfig['radius'],
                    height: $animConfig['radius'] * 2.5,
                    spiralTurns: 3,
                    duration: $animConfig['duration'],
                    particleCount: $animConfig['particle_count'],
                    particle: $particle,
                    tickInterval: $animConfig['tick_interval']
                );
                $animation->start();
                break;

            case 'pulse':
                $animation = new PulseAnimation(
                    plugin: $plugin,
                    world: $world,
                    center: $position,
                    radius: $animConfig['radius'],
                    pulses: 2,
                    duration: $animConfig['duration'],
                    particleCount: $animConfig['particle_count'],
                    particle: $particle,
                    tickInterval: $animConfig['tick_interval']
                );
                $animation->start();
                break;
        }
    }

    public function canUseInAir()
    : bool
    {
        return $this->spell->canUseInAir();
    }

    public function getItem()
    : Item
    {
        return clone $this->item;
    }

    public function getDescription()
    : string
    {
        return $this->description;
    }

    public function getCooldown()
    : int
    {
        return $this->cooldown;
    }

    public function getRange()
    : float
    {
        return $this->range;
    }

    public function getSpell()
    : Spell
    {
        return $this->spell;
    }
}