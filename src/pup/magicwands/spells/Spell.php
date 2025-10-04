<?php

namespace pup\magicwands\spells;

use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\Particle;

abstract class Spell
{

    protected string $id;
    protected string $name;
    protected array $config;

    public function __construct(string $id, string $name, array $config = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->config = $config;
    }

    /**
     * Execute the spell logic
     * @return bool Success
     */
    abstract public function cast(Player $caster, Vector3 $target, float $range)
    : bool;

    /**
     * Can this spell be used in the air (without targeting a block)?
     */
    public function canUseInAir()
    : bool
    {
        return false;
    }

    /**
     * Get particle for animation
     */
    abstract public function getParticle()
    : Particle;

    /**
     * Get animation type (circle, spiral, pulse, projectile)
     */
    abstract public function getAnimationType()
    : string;

    /**
     * Get animation parameters
     */
    public function getAnimationConfig()
    : array
    {
        return [
            'radius'         => $this->config['radius'] ?? 2.0,
            'duration'       => $this->config['animation_duration'] ?? 20,
            'particle_count' => $this->config['particle_count'] ?? 20,
            'tick_interval'  => $this->config['tick_interval'] ?? 2
        ];
    }

    public function getId()
    : string
    {
        return $this->id;
    }

    public function getName()
    : string
    {
        return $this->name;
    }

    public function getConfig()
    : array
    {
        return $this->config;
    }

    /**
     * Get particle configuration (can be string or array)
     */
    protected function getParticleConfig()
    {
        return $this->config['particle'] ?? 'flame';
    }

    protected function getConfigValue(string $key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}