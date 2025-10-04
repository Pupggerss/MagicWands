<?php

namespace pup\magicwands\spells\types;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\Particle;
use pup\magicwands\entities\SpellProjectile;
use pup\magicwands\spells\Spell;
use pup\magicwands\utils\ParticleSoundMapper;
use pup\placeholderapi\PlaceholderApi;

class ProjectileSpell extends Spell
{

    public static function handleProjectileHit(ProjectileHitEvent $event)
    : void
    {
        $projectile = $event->getEntity();

        if (!$projectile instanceof SpellProjectile) {
            return;
        }

        if ($event instanceof ProjectileHitEntityEvent) {
            $hitEntity = $event->getEntityHit();

            if ($hitEntity instanceof Living) {
                $damage = $projectile->getSpellDamage();
                $fireTicks = $projectile->getFireTicks();
                $effects = $projectile->getEffects();

                $owner = $projectile->getOwningEntity();

                if ($damage > 0 && $owner instanceof Player) {
                    $hitEntity->attack(
                        new EntityDamageByEntityEvent(
                            $owner,
                            $hitEntity,
                            EntityDamageEvent::CAUSE_PROJECTILE,
                            $damage
                        )
                    );
                }

                if ($fireTicks > 0) {
                    $hitEntity->setOnFire($fireTicks / 20);
                }

                if (!empty($effects)) {
                    foreach ($effects
                             as
                             $effectData)
                    {
                        $effectName = $effectData['name'] ?? null;
                        if ($effectName === null) continue;

                        $effect = StringToEffectParser::getInstance()->parse($effectName);
                        if ($effect !== null) {
                            $duration = ($effectData['duration'] ?? 5) * 20;
                            $amplifier = $effectData['amplifier'] ?? 0;

                            $hitEntity->getEffects()->add(
                                new EffectInstance($effect, $duration, $amplifier, true)
                            );
                        }
                    }
                }

                if ($hitEntity instanceof Player && $owner instanceof Player) {
                    $hitMsg = "{red}You were hit by {yellow}" . $owner->getName() . "{red}'s spell!";
                    $hitEntity->sendMessage(PlaceholderApi::parse($hitMsg, $hitEntity));
                }
            }
        }
    }

    public function canUseInAir()
    : bool
    {
        return true;
    }

    public function cast(Player $caster, Vector3 $target, float $range)
    : bool
    {
        $world = $caster->getWorld();

        $damage = $this->getConfigValue('damage', 5.0);
        $fireTicks = $this->getConfigValue('fire_ticks', 0);
        $effects = $this->getConfigValue('effects', []);
        $speed = $this->getConfigValue('speed', 1.5);

        $trailParticle = $this->getParticle();

        $projectile = new SpellProjectile(
            $caster->getLocation(),
            $caster,
            $this->id,
            $damage,
            $fireTicks,
            $effects,
            $trailParticle
        );

        $direction = $caster->getDirectionVector();
        $projectile->setMotion($direction->multiply($speed));

        $projectile->spawnToAll();

        $soundConfig = $this->getConfigValue('sound');
        if ($soundConfig !== null) {
            ParticleSoundMapper::playSound($soundConfig, $world, $caster->getLocation());
        }

        $caster->sendMessage(PlaceholderApi::parse(
            $this->getConfigValue('success_message', '{green}Projectile launched!')
        ));

        return true;
    }

    public function getParticle()
    : Particle
    {
        $particleConfig = $this->getParticleConfig();
        $particle = ParticleSoundMapper::getParticle($particleConfig);

        return $particle ?? new FlameParticle();
    }

    public function getAnimationType()
    : string
    {
        return 'none';
    }
}