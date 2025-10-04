<?php

namespace pup\magicwands\spells\types;

use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\player\Player;
use pocketmine\world\particle\EndermanTeleportParticle;
use pocketmine\world\particle\Particle;
use pup\magicwands\spells\Spell;
use pup\magicwands\utils\ParticleSoundMapper;
use pup\placeholderapi\PlaceholderApi;

class TeleportSpell extends Spell
{

    public function canUseInAir()
    : bool
    {
        return true;
    }

    public function cast(Player $caster, Vector3 $target, float $range)
    : bool
    {
        $targetPos = $this->getTargetBlock($caster, $range);

        if ($targetPos === null) {
            $caster->sendMessage(PlaceholderApi::parse(
                $this->getConfigValue('no_target_message', '{red}No valid teleport location found!')
            ));
            return false;
        }

        $targetPos = $targetPos->add(0, 1, 0);
        $world = $caster->getWorld();

        if (!$world->getBlock($targetPos)->isSolid() && !$world->getBlock($targetPos->add(0, 1, 0))->isSolid()) {
            // Play sound at departure
            $soundConfig = $this->getConfigValue('sound');
            if ($soundConfig !== null) {
                ParticleSoundMapper::playSound($soundConfig, $world, $caster->getPosition());
            }

            $caster->teleport($targetPos);

            // Play sound at arrival
            if ($soundConfig !== null) {
                ParticleSoundMapper::playSound($soundConfig, $world, $targetPos);
            }

            $caster->sendMessage(PlaceholderApi::parse(
                $this->getConfigValue('success_message', '{green}Teleported successfully!'),
                $caster
            ));
            return true;
        } else {
            $caster->sendMessage(PlaceholderApi::parse(
                $this->getConfigValue('unsafe_message', '{red}That location is not safe!')
            ));
            return false;
        }
    }

    private function getTargetBlock(Player $player, float $range)
    : ?Vector3
    {
        $start = $player->getEyePos();
        $direction = $player->getDirectionVector();
        $end = $start->addVector($direction->multiply($range));

        $world = $player->getWorld();

        foreach (VoxelRayTrace::betweenPoints($start, $end)
                 as
                 $vector3)
        {
            $block = $world->getBlockAt((int)$vector3->x, (int)$vector3->y, (int)$vector3->z);

            if ($block->isSolid()) {
                return $vector3;
            }
        }

        return $end;
    }

    public function getParticle()
    : Particle
    {
        $particleConfig = $this->getParticleConfig();
        $particle = ParticleSoundMapper::getParticle($particleConfig);

        return $particle ?? new EndermanTeleportParticle();
    }

    public function getAnimationType()
    : string
    {
        return $this->getConfigValue('animation_type', 'pulse');
    }
}