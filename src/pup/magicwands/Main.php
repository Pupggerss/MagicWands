<?php

namespace pup\magicwands;

use CortexPE\Commando\exception\HookAlreadyRegistered;
use CortexPE\Commando\PacketHooker;
use Exception;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\World;
use pup\magicwands\commands\WandCommand;
use pup\magicwands\entities\SpellProjectile;
use pup\magicwands\spells\SpellManager;
use pup\magicwands\wands\MagicWand;
use pup\magicwands\wands\WandListener;
use pup\magicwands\wands\WandManager;
use pup\placeholderapi\PlaceholderApi;

final class Main extends PluginBase
{
    use SingletonTrait;

    private WandManager $wandManager;
    private SpellManager $spellManager;
    private Config $config;

    public function onLoad()
    : void
    {
        self::setInstance($this);
    }

    /**
     * @throws HookAlreadyRegistered
     */
    public function onEnable()
    : void
    {
        $this->saveDefaultConfig();
        $this->config = $this->getConfig();

        $this->spellManager = new SpellManager();
        $this->wandManager = new WandManager();

        $this->registerWandsFromConfig();

        if (!PacketHooker::isRegistered()) {
            PacketHooker::register($this);
        }

        Server::getInstance()->getPluginManager()->registerEvents(new WandListener($this), $this);
        Server::getInstance()->getCommandMap()->register("magicwands", new WandCommand($this));

        $this->registerPlaceholders();
        $this->registerEntities();

        $this->getLogger()->info("MagicWands plugin enabled!");
        $this->getLogger()->info("Registered " . count($this->wandManager->getAllWands()) . " wands with " . count($this->spellManager->getAllSpells()) . " spells");
    }

    private function registerWandsFromConfig()
    : void
    {
        $wandsConfig = $this->config->get("wands", []);

        foreach ($wandsConfig
                 as
                 $wandId
        =>
                 $wandData)
        {
            try {
                $wandName = PlaceholderApi::parseColors($wandData['name'] ?? "Unknown Wand");
                $wandDesc = PlaceholderApi::parseColors($wandData['description'] ?? "A magical wand");
                $cooldown = ($wandData['cooldown'] ?? 3) * 20;
                $range = (float)($wandData['range'] ?? 20.0);

                $spellData = $wandData['spell'] ?? [];
                $spellType = $spellData['type'] ?? null;

                if ($spellType === null) {
                    $this->getLogger()->warning("Wand '{$wandId}' has no spell type defined, skipping...");
                    continue;
                }

                if (!$this->spellManager->hasSpellType($spellType)) {
                    $this->getLogger()->warning("Unknown spell type '{$spellType}' for wand '{$wandId}', skipping...");
                    continue;
                }

                $spell = $this->spellManager->createSpell(
                    id: $wandId . "_spell",
                    name: $wandName . " Spell",
                    type: $spellType,
                    config: $this->parseSpellConfig($spellData)
                );

                if ($spell === null) {
                    $this->getLogger()->error("Failed to create spell for wand '{$wandId}'");
                    continue;
                }

                $wand = new MagicWand(
                    id: $wandId,
                    name: $wandName,
                    description: $wandDesc,
                    cooldown: $cooldown,
                    range: $range,
                    spell: $spell
                );

                $this->wandManager->registerWand($wand);

                $this->getLogger()->debug("Registered wand: {$wandId} with spell type: {$spellType}");

            } catch (Exception $e) {
                $this->getLogger()->error("Error registering wand '{$wandId}': " . $e->getMessage());
            }
        }
    }

    private function parseSpellConfig(array $spellData)
    : array
    {
        $config = [];

        foreach ($spellData
                 as
                 $key
        =>
                 $value)
        {
            if ($key === 'type') continue;

            if (is_string($value) && str_contains($value, '{')) {
                $config[$key] = PlaceholderApi::parseColors($value);
            } else {
                $config[$key] = $value;
            }
        }

        return $config;
    }

    private function registerPlaceholders()
    : void
    {
        PlaceholderApi::registerPlaceholder('total_wands', function () {
            return (string)count($this->wandManager->getAllWands());
        });

        PlaceholderApi::registerPlaceholder('wand_types', function () {
            return implode(", ", $this->wandManager->getWandIds());
        });

        PlaceholderApi::registerPlaceholder('total_spells', function () {
            return (string)count($this->spellManager->getAllSpells());
        });

        PlaceholderApi::registerPlaceholder('spell_types', function () {
            return implode(", ", $this->spellManager->getSpellTypes());
        });
    }

    private function registerEntities()
    : void
    {
        EntityFactory::getInstance()->register(
            SpellProjectile::class,
            function (World $world, CompoundTag $nbt)
            : SpellProjectile {
                return new SpellProjectile(
                    EntityDataHelper::parseLocation($nbt, $world),
                    null,
                    "",
                    0.0,
                    0,
                    [],
                    null
                );
            },
            ['SpellProjectile',
                'magic_projectile']
        );
    }

    public function getWandManager()
    : WandManager
    {
        return $this->wandManager;
    }

    public function getSpellManager()
    : SpellManager
    {
        return $this->spellManager;
    }

    public function onDisable()
    : void
    {
        $this->getLogger()->info("MagicWands plugin disabled!");
    }
}