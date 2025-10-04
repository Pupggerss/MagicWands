<?php

namespace pup\magicwands\utils;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\BlockForceFieldParticle;
use pocketmine\world\particle\CriticalParticle;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\particle\EnchantParticle;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\EntityFlameParticle;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\HappyVillagerParticle;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use pocketmine\world\particle\InkParticle;
use pocketmine\world\particle\LavaDripParticle;
use pocketmine\world\particle\LavaParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\particle\PotionSplashParticle;
use pocketmine\world\particle\RainSplashParticle;
use pocketmine\world\particle\RedstoneParticle;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\SnowballPoofParticle;
use pocketmine\world\particle\SporeParticle;
use pocketmine\world\particle\WaterDripParticle;
use pocketmine\world\particle\WaterParticle;
use pocketmine\world\World;

class ParticleSoundMapper
{

    private static array $particleMap = [
        'flame'             => FlameParticle::class,
        'heart'             => HeartParticle::class,
        'lava'              => LavaParticle::class,
        'smoke'             => SmokeParticle::class,
        'critical'          => CriticalParticle::class,
        'enchant'           => EnchantParticle::class,
        'enderman_teleport' => EndermanTeleportParticle::class,
        'water'             => WaterParticle::class,
        'water_drip'        => WaterDripParticle::class,
        'lava_drip'         => LavaDripParticle::class,
        'rain_splash'       => RainSplashParticle::class,
        'snowball_poof'     => SnowballPoofParticle::class,
        'explode'           => ExplodeParticle::class,
        'huge_explode'      => HugeExplodeParticle::class,
        'entity_flame'      => EntityFlameParticle::class,
        'happy_villager'    => HappyVillagerParticle::class,
        'ink'               => InkParticle::class,
        'redstone'          => RedstoneParticle::class,
        'spore'             => SporeParticle::class,
        'block_force_field' => BlockForceFieldParticle::class,
        'dust'              => 'dust',
        'potion'            => 'potion',
        'block'             => 'block',
    ];

    private static array $soundShortcuts = [
        'blaze_shoot'       => 'mob.blaze.shoot',
        'chest_open'        => 'random.chestopen',
        'chest_close'       => 'random.chestclosed',
        'enderman_teleport' => 'mob.endermen.portal',
        'teleport'          => 'mob.endermen.portal',
        'ghast_shoot'       => 'mob.ghast.fireball',
        'potion_splash'     => 'random.glass',
        'explode'           => 'random.explode',
        'bow_shoot'         => 'random.bow',
        'thunder'           => 'ambient.weather.thunder',
        'lightning'         => 'ambient.weather.lightning.impact',
        'anvil_land'        => 'random.anvil_land',
        'click'             => 'random.click',
        'door_open'         => 'random.door_open',
        'door_close'        => 'random.door_close',
        'fizz'              => 'random.fizz',
        'fuse'              => 'random.fuse',
        'levelup'           => 'random.levelup',
        'orb'               => 'random.orb',
        'pop'               => 'random.pop',
        'portal'            => 'portal.portal',
        'eat'               => 'random.eat',
        'drink'             => 'random.drink',
        'burp'              => 'random.burp',
    ];

    /**
     * @param array|string $config Either a string name or array with type and parameters
     * @return Particle|null
     */
    public static function getParticle(array|string $config)
    : ?Particle
    {
        if (is_string($config)) {
            $lowerName = strtolower($config);

            if (!isset(self::$particleMap[$lowerName])) {
                return null;
            }

            $mapped = self::$particleMap[$lowerName];

            if (class_exists($mapped) && is_subclass_of($mapped, Particle::class)) {
                return new $mapped();
            }

            return match ($mapped) {
                'dust' => new DustParticle(new Color(255, 0, 0)),
                'potion' => new PotionSplashParticle(new Color(255, 0, 255)),
                'block' => new BlockBreakParticle(VanillaBlocks::STONE()),
                default => null,
            };
        }

        if (is_array($config)) {
            $type = strtolower($config['type'] ?? '');

            switch ($type) {
                case 'dust':
                    $r = $config['r'] ?? $config['red'] ?? 255;
                    $g = $config['g'] ?? $config['green'] ?? 0;
                    $b = $config['b'] ?? $config['blue'] ?? 0;

                    if (isset($config['color']) && is_array($config['color'])) {
                        [$r,
                            $g,
                            $b] = array_values($config['color']);
                    }

                    return new DustParticle(new Color((int)$r, (int)$g, (int)$b));

                case 'potion':
                    $r = $config['r'] ?? $config['red'] ?? 255;
                    $g = $config['g'] ?? $config['green'] ?? 0;
                    $b = $config['b'] ?? $config['blue'] ?? 255;

                    if (isset($config['color']) && is_array($config['color'])) {
                        [$r,
                            $g,
                            $b] = array_values($config['color']);
                    }

                    return new PotionSplashParticle(new Color((int)$r, (int)$g, (int)$b));

                case 'block':
                    $blockName = $config['block'] ?? 'stone';
                    $block = self::getBlockByName($blockName);

                    return $block !== null ? new BlockBreakParticle($block) : null;

                default:
                    return self::getParticle($type);
            }
        }

        return null;
    }

    private static function getBlockByName(string $name)
    : ?Block
    {
        $name = strtolower(str_replace([' ',
            '-'], '_', $name));
        $method = str_replace('_', '', ucwords($name, '_'));

        if (method_exists(VanillaBlocks::class, $method)) {
            return VanillaBlocks::$method();
        }

        $blockMap = [
            'grass'             => VanillaBlocks::GRASS(),
            'dirt'              => VanillaBlocks::DIRT(),
            'stone'             => VanillaBlocks::STONE(),
            'cobblestone'       => VanillaBlocks::COBBLESTONE(),
            'wood'              => VanillaBlocks::OAK_PLANKS(),
            'planks'            => VanillaBlocks::OAK_PLANKS(),
            'oak_planks'        => VanillaBlocks::OAK_PLANKS(),
            'glass'             => VanillaBlocks::GLASS(),
            'sand'              => VanillaBlocks::SAND(),
            'gravel'            => VanillaBlocks::GRAVEL(),
            'gold_ore'          => VanillaBlocks::GOLD_ORE(),
            'iron_ore'          => VanillaBlocks::IRON_ORE(),
            'coal_ore'          => VanillaBlocks::COAL_ORE(),
            'diamond_ore'       => VanillaBlocks::DIAMOND_ORE(),
            'emerald_ore'       => VanillaBlocks::EMERALD_ORE(),
            'log'               => VanillaBlocks::OAK_LOG(),
            'oak_log'           => VanillaBlocks::OAK_LOG(),
            'leaves'            => VanillaBlocks::OAK_LEAVES(),
            'oak_leaves'        => VanillaBlocks::OAK_LEAVES(),
            'sponge'            => VanillaBlocks::SPONGE(),
            'wool'              => VanillaBlocks::WOOL(),
            'gold_block'        => VanillaBlocks::GOLD(),
            'iron_block'        => VanillaBlocks::IRON(),
            'diamond_block'     => VanillaBlocks::DIAMOND(),
            'emerald_block'     => VanillaBlocks::EMERALD(),
            'tnt'               => VanillaBlocks::TNT(),
            'bookshelf'         => VanillaBlocks::BOOKSHELF(),
            'obsidian'          => VanillaBlocks::OBSIDIAN(),
            'ice'               => VanillaBlocks::ICE(),
            'snow'              => VanillaBlocks::SNOW(),
            'clay'              => VanillaBlocks::CLAY(),
            'netherrack'        => VanillaBlocks::NETHERRACK(),
            'soul_sand'         => VanillaBlocks::SOUL_SAND(),
            'glowstone'         => VanillaBlocks::GLOWSTONE(),
            'bedrock'           => VanillaBlocks::BEDROCK(),
            'brick'             => VanillaBlocks::BRICKS(),
            'bricks'            => VanillaBlocks::BRICKS(),
            'end_stone'         => VanillaBlocks::END_STONE(),
            'purpur'            => VanillaBlocks::PURPUR(),
            'prismarine'        => VanillaBlocks::PRISMARINE(),
            'sea_lantern'       => VanillaBlocks::SEA_LANTERN(),
            'magma'             => VanillaBlocks::MAGMA(),
            'nether_wart_block' => VanillaBlocks::NETHER_WART_BLOCK(),
            'bone_block'        => VanillaBlocks::BONE_BLOCK(),
            'concrete'          => VanillaBlocks::CONCRETE(),
            'glazed_terracotta' => VanillaBlocks::GLAZED_TERRACOTTA(),
        ]; //Correct way?

        return $blockMap[$name] ?? VanillaBlocks::STONE();
    }

    /**
     * @param string|array $config Sound config
     * @param World $world World to play sound in
     * @param Vector3 $pos Position to play sound at
     */
    public static function playSound(string|array $config, World $world, Vector3 $pos)
    : void
    {
        $soundName = '';
        $volume = 1.0;
        $pitch = 1.0;

        if (is_string($config)) {
            $soundName = $config;
        } elseif (is_array($config)) {
            $soundName = $config['name'] ?? $config['sound'] ?? '';
            $volume = (float)($config['volume'] ?? 1.0);
            $pitch = (float)($config['pitch'] ?? 1.0);
        }

        if (empty($soundName)) {
            return;
        }

        $lowerName = strtolower($soundName);
        $mcSoundString = self::$soundShortcuts[$lowerName] ?? $soundName;

        $pk = PlaySoundPacket::create(
            $mcSoundString,
            $pos->getX(),
            $pos->getY(),
            $pos->getZ(),
            $volume,
            $pitch
        );

        NetworkBroadcastUtils::broadcastPackets($world->getPlayers(), [$pk]);
    }


    public static function getAvailableParticles()
    : array
    {
        return array_keys(self::$particleMap);
    }

    public static function getAvailableSoundShortcuts()
    : array
    {
        return array_keys(self::$soundShortcuts);
    }
}