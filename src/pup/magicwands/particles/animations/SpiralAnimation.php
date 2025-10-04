<?php

namespace pup\magicwands\particles\animations;

use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\world\particle\Particle;
use pocketmine\world\World;
use pup\magicwands\particles\ParticleAnimation;

class SpiralAnimation extends ParticleAnimation
{

    private float $height;
    private int $spiralTurns;

    public function __construct(Plugin $plugin, World $world, Vector3 $center, float $radius, float $height, int $spiralTurns, int $duration, int $particleCount, Particle $particle, int $tickInterval = 2)
    {
        parent::__construct($plugin, $world, $center, $radius, $duration, $particleCount, $particle, $tickInterval);
        $this->height = $height;
        $this->spiralTurns = $spiralTurns;
    }

    protected function spreadParticles(float $progress)
    : void
    {
        $currentRadius = $this->radius * $progress;
        $currentHeight = $this->height * $progress;

        for ($i = 0;
             $i < $this->particleCount;
             $i++)
        {
            $angle = 2 * M_PI * $this->spiralTurns * $progress + 2 * M_PI * $i / $this->particleCount;
            $x = $this->center->x + $currentRadius * cos($angle);
            $z = $this->center->z + $currentRadius * sin($angle);
            $y = $this->center->y + $currentHeight;

            $pos = new Vector3($x, $y, $z);
            $this->world->addParticle($pos, $this->particle);
        }
    }
}