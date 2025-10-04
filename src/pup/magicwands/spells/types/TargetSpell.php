<?php

namespace pup\magicwands\spells\types;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\HeartParticle;
use pocketmine\world\particle\Particle;
use pup\magicwands\spells\Spell;
use pup\magicwands\utils\ParticleSoundMapper;
use pup\placeholderapi\PlaceholderApi;

class TargetSpell extends Spell
{

    public function canUseInAir()
    : bool
    {
        return true;
    }

    public function cast(Player $caster, Vector3 $target, float $range)
    : bool
    {
        $targetEntity = $this->getTargetEntity($caster, $range);

        if ($targetEntity === null && $this->getConfigValue('can_self_cast', true)) {
            $targetEntity = $caster;
        }

        if ($targetEntity === null) {
            $caster->sendMessage(PlaceholderApi::parse(
                $this->getConfigValue('no_target_message', '{red}No valid target found!')
            ));
            return false;
        }

        if (!$targetEntity instanceof Living) {
            $caster->sendMessage(PlaceholderApi::parse(
                '{red}Target must be a living entity!'
            ));
            return false;
        }

        $healAmount = $this->getConfigValue('heal_amount', 0.0);
        $actualHeal = 0.0;

        if ($healAmount > 0) {
            if ($targetEntity->getHealth() >= $targetEntity->getMaxHealth()) {
                $actualHeal = 0.0;
            } else {
                $oldHealth = $targetEntity->getHealth();
                $newHealth = min($targetEntity->getHealth() + $healAmount, $targetEntity->getMaxHealth());
                $targetEntity->setHealth($newHealth);
                $actualHeal = $newHealth - $oldHealth;
            }
        }

        $effects = $this->getConfigValue('effects', []);
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

                $targetEntity->getEffects()->add(
                    new EffectInstance($effect, $duration, $amplifier, true)
                );
            }
        }

        $soundConfig = $this->getConfigValue('sound');
        if ($soundConfig !== null) {
            ParticleSoundMapper::playSound($soundConfig, $caster->getWorld(), $caster->getLocation());
        }

        if ($targetEntity === $caster) {
            $selfMsg = $this->getConfigValue('self_cast_message', '{green}Spell cast on yourself!');
            $selfMsg = str_replace('{heal_amount}', (string)$actualHeal, $selfMsg);
            $caster->sendMessage(PlaceholderApi::parse($selfMsg));
        } else {
            $casterMsg = $this->getConfigValue('caster_message', '{green}Spell cast on {target}!');
            $casterMsg = str_replace('{target}', $targetEntity->getName(), $casterMsg);
            $casterMsg = str_replace('{heal_amount}', (string)$actualHeal, $casterMsg);
            $caster->sendMessage(PlaceholderApi::parse($casterMsg));

            if ($targetEntity instanceof Player) {
                $targetMsg = $this->getConfigValue('target_message', '{green}You were affected by {caster}!');
                $targetMsg = str_replace('{caster}', $caster->getName(), $targetMsg);
                $targetEntity->sendMessage(PlaceholderApi::parse($targetMsg));
            }
        }

        return true;
    }

    private function getTargetEntity(Player $player, float $range)
    : ?Entity
    {
        $start = $player->getEyePos();
        $direction = $player->getDirectionVector();
        $world = $player->getWorld();

        $bb = $player->getBoundingBox()->expandedCopy($range, $range, $range);

        $closestEntity = null;
        $closestDistance = PHP_FLOAT_MAX;

        foreach ($world->getNearbyEntities($bb)
                 as
                 $entity)
        {
            if ($entity === $player) continue;
            if (!$entity instanceof Living) continue;

            $entityPos = $entity->getPosition()->add(0, $entity->getEyeHeight() / 2, 0);
            $distance = $start->distance($entityPos);

            if ($distance > $range) continue;
            if ($distance >= $closestDistance) continue;

            if ($this->isLookingAt($start, $direction, $entityPos, 15.0)) {
                $closestEntity = $entity;
                $closestDistance = $distance;
            }
        }

        return $closestEntity;
    }

    private function isLookingAt(Vector3 $start, Vector3 $direction, Vector3 $target, float $threshold = 15.0)
    : bool
    {
        $toTarget = $target->subtractVector($start)->normalize();
        $dot = $direction->dot($toTarget);

        $angleThreshold = cos(deg2rad($threshold));

        return $dot >= $angleThreshold;
    }

    public function getParticle()
    : Particle
    {
        $particleConfig = $this->getParticleConfig();
        $particle = ParticleSoundMapper::getParticle($particleConfig);

        return $particle ?? new HeartParticle();
    }

    public function getAnimationType()
    : string
    {
        return $this->getConfigValue('animation_type', 'circle');
    }
}