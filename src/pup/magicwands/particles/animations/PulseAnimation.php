<?php

namespace pup\magicwands\particles\animations;

use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\world\particle\Particle;
use pocketmine\world\World;
use pup\magicwands\particles\ParticleAnimation;

class PulseAnimation extends ParticleAnimation
{

    private float $pulses;

    public function __construct(Plugin $plugin, World $world, Vector3 $center, float $radius, int $pulses, int $duration, int $particleCount, Particle $particle, int $tickInterval = 2)
    {
        parent::__construct($plugin, $world, $center, $radius, $duration, $particleCount, $particle, $tickInterval);
        $this->pulses = $pulses;
    }

    protected function spreadParticles(float $progress)
    : void
    {
        $pulseProgress = fmod($progress * $this->pulses, 1.0);
        $currentRadius = $this->radius * $pulseProgress;

        for ($i = 0;
             $i < $this->particleCount;
             $i++)
        {
            $angle = 2 * M_PI * $i / $this->particleCount;
            $x = $this->center->x + $currentRadius * cos($angle);
            $z = $this->center->z + $currentRadius * sin($angle);

            $pos = new Vector3($x, $this->center->y, $z);
            $this->world->addParticle($pos, $this->particle);
        }
    }
}