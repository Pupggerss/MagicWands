<?php

namespace pup\magicwands\spells;

use InvalidArgumentException;
use pup\magicwands\spells\types\AOESpell;
use pup\magicwands\spells\types\ProjectileSpell;
use pup\magicwands\spells\types\TargetSpell;
use pup\magicwands\spells\types\TeleportSpell;

class SpellManager
{

    /** @var Spell[] Registered spells by ID */
    private array $spells = [];

    /** @var array<string, string> Spell type name to class mapping */
    private array $spellTypes = [];

    public function __construct()
    {
        $this->registerSpellType('aoe', AOESpell::class);
        $this->registerSpellType('projectile', ProjectileSpell::class);
        $this->registerSpellType('target', TargetSpell::class);
        $this->registerSpellType('teleport', TeleportSpell::class);
    }

    public function registerSpellType(string $typeName, string $className)
    : void
    {
        if (!is_subclass_of($className, Spell::class)) {
            throw new InvalidArgumentException("Class {$className} must extend Spell");
        }
        $this->spellTypes[$typeName] = $className;
    }

    public function createSpell(string $id, string $name, string $type, array $config)
    : ?Spell
    {
        if (!isset($this->spellTypes[$type])) {
            return null;
        }

        $className = $this->spellTypes[$type];
        $spell = new $className($id, $name, $config);
        $this->spells[$id] = $spell;

        return $spell;
    }

    public function registerSpell(Spell $spell)
    : void
    {
        $this->spells[$spell->getId()] = $spell;
    }

    /**
     * @param string $id
     * @return Spell|null
     */
    public function getSpell(string $id)
    : ?Spell
    {
        return $this->spells[$id] ?? null;
    }

    /**
     * @return Spell[]
     */
    public function getAllSpells()
    : array
    {
        return $this->spells;
    }

    /**
     * @return string[]
     */
    public function getSpellTypes()
    : array
    {
        return array_keys($this->spellTypes);
    }

    public function hasSpellType(string $type)
    : bool
    {
        return isset($this->spellTypes[$type]);
    }
}