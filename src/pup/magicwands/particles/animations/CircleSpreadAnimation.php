<?php

namespace pup\magicwands\particles\animations;

use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\world\particle\Particle;
use pocketmine\world\World;
use pup\magicwands\particles\ParticleAnimation;

class CircleSpreadAnimation extends ParticleAnimation
{

    private float $startRadius;
    private float $targetRadius;

    public function __construct(Plugin $plugin, World $world, Vector3 $center, float $startRadius, float $targetRadius, int $duration, int $particleCount, Particle $particle, int $tickInterval = 2)
    {
        parent::__construct($plugin, $world, $center, $targetRadius, $duration, $particleCount, $particle, $tickInterval);
        $this->startRadius = $startRadius;
        $this->targetRadius = $targetRadius;
    }

    protected function spreadParticles(float $progress)
    : void
    {
        $currentRadius = $this->startRadius + ($this->targetRadius - $this->startRadius) * $progress;

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