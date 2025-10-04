<?php

namespace pup\magicwands\spells\types;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\Particle;
use pup\magicwands\spells\Spell;
use pup\magicwands\utils\ParticleSoundMapper;
use pup\placeholderapi\PlaceholderApi;

class AOESpell extends Spell
{

    public function cast(Player $caster, Vector3 $target, float $range)
    : bool
    {
        $targetPos = $this->getTargetBlock($caster, $range);

        if ($targetPos === null) {
            $caster->sendMessage(PlaceholderApi::parse(
                $this->getConfigValue('no_target_message', '{red}No valid target location!')
            ));
            return false;
        }

        $world = $caster->getWorld();
        $radius = $this->getConfigValue('radius', 5.0);

        $soundConfig = $this->getConfigValue('sound');
        if ($soundConfig !== null) {
            ParticleSoundMapper::playSound($soundConfig, $world, $targetPos);
        }

        $affectedCount = $this->affectEntities($world, $targetPos, $caster, $radius);

        $successMsg = $this->getConfigValue('success_message', '{green}Spell cast successfully!');
        $successMsg = str_replace('{count}', (string)$affectedCount, $successMsg);
        $caster->sendMessage(PlaceholderApi::parse($successMsg));

        return true;
    }

    private function getTargetBlock(Player $player, float $range)
    : ?Vector3
    {
        $start = $player->getEyePos();
        $direction = $player->getDirectionVector();
        $end = $start->addVector($direction->multiply($range));
        if ($start === $end) {
            $start = $start->addVector($direction->multiply(0.1));
        }

        $world = $player->getWorld();

        foreach (VoxelRayTrace::betweenPoints($start->floor(), $end)
                 as
                 $vector3)
        {
            $block = $world->getBlockAt((int)$vector3->x, (int)$vector3->y, (int)$vector3->z);

            if ($block->isSolid()) {
                return $vector3;
            }
        }

        return null;
    }

    private function affectEntities($world, Vector3 $center, Player $caster, float $radius)
    : int
    {
        $count = 0;
        $damage = $this->getConfigValue('damage', 0.0);
        $effects = $this->getConfigValue('effects', []);
        $affectCaster = $this->getConfigValue('affect_caster', false);

        foreach ($world->getNearbyEntities(
            new AxisAlignedBB(
                $center->x - $radius,
                $center->y - $radius,
                $center->z - $radius,
                $center->x + $radius,
                $center->y + $radius,
                $center->z + $radius
            )
        )
                 as
                 $entity)
        {
            if (!$entity instanceof Living) continue;
            if ($entity === $caster && !$affectCaster) continue;

            if ($damage > 0) {
                $distance = $entity->getPosition()->distance($center);
                $damageMultiplier = 1 - ($distance / $radius);
                $finalDamage = $damage * $damageMultiplier;

                $entity->attack(
                    new EntityDamageByEntityEvent(
                        $caster,
                        $entity,
                        EntityDamageEvent::CAUSE_MAGIC,
                        $finalDamage
                    )
                );
            }
            $spawnLightning = $this->getConfigValue('spawn_lightning', false);
            $lightningCount = $this->getConfigValue('lightning_count', 1);

            if ($spawnLightning) {
                for ($i = 0;
                     $i < $lightningCount;
                     $i++)
                {
                    $light = new AddActorPacket();
                    $light->actorUniqueId = Entity::nextRuntimeId();
                    $light->actorRuntimeId = 1;
                    $light->position = $entity->getPosition()->asVector3();
                    $light->type = "minecraft:lightning_bolt";
                    $light->yaw = $entity->getLocation()->getYaw();
                    $light->syncedProperties = new PropertySyncData([], []);

                    $block = $entity->getWorld()->getBlock($entity->getPosition()->floor()->down());
                    $particle = new BlockBreakParticle($block);

                    $entity->getWorld()->addParticle($entity->getPosition(), $particle,
                        $entity->getWorld()
                            ->getPlayers
                            ());

                    NetworkBroadcastUtils::broadcastPackets($entity->getWorld()->getPlayers(),
                        [$light]);
                }
            }

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

                    $entity->getEffects()->add(
                        new EffectInstance($effect, $duration, $amplifier, true)
                    );
                }
            }

            if ($this->getConfigValue('fire_ticks', 0) > 0) {
                $entity->setOnFire($this->getConfigValue('fire_ticks') / 20);
            }

            if ($entity instanceof Player && $entity !== $caster) {
                $affectedMsg = $this->getConfigValue('affected_message');
                if ($affectedMsg) {
                    $affectedMsg = str_replace('{caster}', $caster->getName(), $affectedMsg);
                    $entity->sendMessage(PlaceholderApi::parse($affectedMsg, $entity));
                }
            }


            $count++;
        }

        return $count;
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
        return $this->getConfigValue('animation_type', 'circle');
    }
}