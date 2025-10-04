<?php

namespace pup\magicwands\entities;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Snowball;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\Particle;

class SpellProjectile extends Snowball
{

    protected int $fireTicks;
    private string $spellId;
    private float $spellDamage;
    private array $effects;
    private ?Particle $trailParticle;
    private int $tickCounter = 0;

    public function __construct(
        Location  $location,
        ?Entity   $owner,
        string    $spellId,
        float     $damage,
        int       $fireTicks = 0,
        array     $effects = [],
        ?Particle $trailParticle = null
    )
    {
        parent::__construct($location, $owner);

        $this->spellId = $spellId;
        $this->spellDamage = $damage;
        $this->fireTicks = $fireTicks;
        $this->effects = $effects;
        $this->trailParticle = $trailParticle ?? new FlameParticle();
    }

    public static function getNetworkTypeId()
    : string
    {
        return EntityIds::SNOWBALL;
    }

    public function entityBaseTick(int $tickDiff = 1)
    : bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        if ($this->trailParticle !== null && !$this->isFlaggedForDespawn()) {
            for ($i = 0;
                 $i < 3;
                 $i++)
            {
                $this->getWorld()->addParticle($this->getPosition(), $this->trailParticle);
            }
        }

        $this->tickCounter++;

        return $hasUpdate;
    }

    public function getSpellId()
    : string
    {
        return $this->spellId;
    }

    public function getSpellDamage()
    : float
    {
        return $this->spellDamage;
    }

    public function getFireTicks()
    : int
    {
        return $this->fireTicks;
    }

    public function getEffects()
    : array
    {
        return $this->effects;
    }

    protected function getInitialSizeInfo()
    : EntitySizeInfo
    {
        return new EntitySizeInfo(0.25, 0.25);
    }

    protected function onHit(ProjectileHitEvent $event)
    : void
    {
        if ($this->trailParticle !== null) {
            for ($i = 0;
                 $i < 10;
                 $i++)
            {
                $this->getWorld()->addParticle($this->getPosition(), $this->trailParticle);
            }
        }
    }
}