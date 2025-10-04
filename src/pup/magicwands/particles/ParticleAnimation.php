<?php

namespace pup\magicwands\particles;

use pocketmine\math\Vector3;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\Task;
use pocketmine\world\particle\Particle;
use pocketmine\world\World;

class ParticleAnimation extends Task
{

    protected World $world;
    protected Vector3 $center;
    protected float $radius;
    protected int $duration;
    protected int $particleCount;
    protected Particle $particle;
    protected Plugin $plugin;

    protected int $currentTick = 0;
    protected bool $isRunning = false;

    protected int $tickInterval;

    public function __construct(Plugin $plugin, World $world, Vector3 $center, float $radius, int $duration, int $particleCount, Particle $particle, int $tickInterval = 2)
    {
        $this->plugin = $plugin;
        $this->world = $world;
        $this->center = $center;
        $this->radius = $radius;
        $this->duration = $duration;
        $this->particleCount = $particleCount;
        $this->particle = $particle;
        $this->tickInterval = $tickInterval;
    }

    public function start()
    : void
    {
        if ($this->isRunning) return;

        $this->isRunning = true;
        $this->currentTick = 0;
        $this->plugin->getScheduler()->scheduleRepeatingTask($this, $this->tickInterval);
    }

    public function onRun()
    : void
    {
        if (!$this->isRunning || $this->currentTick >= $this->duration) {
            $this->stop();
            return;
        }

        $progress = $this->currentTick / $this->duration;
        $this->spreadParticles($progress);

        $this->currentTick++;
    }

    public function stop()
    : void
    {
        $this->isRunning = false;
        $this->getHandler()?->cancel();
    }

    protected function spreadParticles(float $progress)
    : void
    {
    }

    public function isRunning()
    : bool
    {
        return $this->isRunning;
    }
}