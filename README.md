# MagicWands Plugin

A PocketMine-MP plugin that adds customizable
magic wands with various spell types to your
server.

## Features

- **Multiple Spell Types**: AOE, Projectile,
  Target, and Teleport spells
- **Customizable Wands**: Configure damage,
  cooldowns, effects, particles, and sounds
- **Visual Effects**: Particle animations (circle,
  spiral, pulse) and sound effects
- **Status Effects**: Apply potion effects to
  targets
- **Permission System**: Control who can use wands
  and commands

## Installation

1. Download the plugin
2. Place the `.phar` file in your server's
   `plugins` folder
3. Restart your server
4. Configure wands in
   `plugin_data/MagicWands/config.yml`

## Commands

| Command                 | Description              | Permission                |
|-------------------------|--------------------------|---------------------------|
| `/wand`                 | List all available wands | `magicwands.command.wand` |
| `/wand <type>`          | Give yourself a wand     | `magicwands.command.wand` |
| `/wand <type> <player>` | Give a player a wand     | `magicwands.command.wand` |

## Spell Types

### 1. AOE (Area of Effect)

Affects all entities within a radius of the target
location.

**Configuration Options:**

- `damage`: Damage dealt to entities
- `radius`: Effect radius in blocks
- `fire_ticks`: Duration of fire effect
- `affect_caster`: Whether the caster is affected
- `spawn_lightning`: Visual lightning effect
- `lightning_count`: Number of lightning bolts
- `effects`: Array of potion effects to apply

### 2. Projectile

Launches a projectile that affects entities on
hit.

**Configuration Options:**

- `damage`: Damage on hit
- `speed`: Projectile speed multiplier
- `fire_ticks`: Fire duration on hit
- `effects`: Potion effects applied on hit

### 3. Target

Targets a specific entity in the caster's line of
sight.

**Configuration Options:**

- `heal_amount`: Amount of health restored
- `can_self_cast`: Allow casting on self
- `effects`: Potion effects to apply

### 4. Teleport

Teleports the caster to a targeted location.

**Configuration Options:**

- Custom messages for success/failure

## Configuration Guide

### Basic Wand Structure

```yaml
wands:
  wand_id:
    name: "Wand Display Name"
    description: "Wand description"
    cooldown: 5          # Cooldown in seconds
    range: 20.0          # Range in blocks

    spell:
      type: "aoe"        # Spell type
      # Spell-specific options here
```

### Particle Configuration

Particles can be simple strings or detailed
configurations:

```yaml
# Simple particle
particle: "flame"

# Advanced particle with color
particle:
  type: "dust"
  color: [ 255, 0, 0 ]   # RGB values

# Block particle
particle:
  type: "block"
  block: "stone"
```

**Available Particles:**

- `flame`, `heart`, `smoke`, `critical`, `enchant`
- `water`, `lava`, `explode`, `enderman_teleport`
- `dust` (customizable color), `potion` (
  customizable color)
- `block` (customizable block type)

### Animation Types

- `circle`: Expanding circle animation
- `spiral`: Spiraling upward animation
- `pulse`: Pulsing wave animation
- `none`: No animation (projectiles)

### Sound Configuration

```yaml
# Simple sound
sound: "random.explode"

# Advanced sound
sound:
  name: "random.explode"
  volume: 1.5
  pitch: 1.0
```

**Available Sound Shortcuts:**

- `blaze_shoot`, `explode`, `thunder`, `lightning`
- `teleport`, `bow_shoot`, `levelup`, `click`
- `door_open`, `portal`, `fizz`, `pop`

### Potion Effects

```yaml
effects:
  - name: "regeneration"
    duration: 10        # Duration in seconds
    amplifier: 2        # Effect level (0 = I, 1 = II, etc.)
  - name: "speed"
    duration: 15
    amplifier: 0
```

**Common Effects:**

- `regeneration`, `speed`, `slowness`,
  `resistance`
- `poison`, `wither`, `weakness`, `strength`
- `blindness`, `nausea`, `levitation`,
  `absorption`

### Message Placeholders

Messages
support [PlaceholderAPI](https://github.com/Pupggerss/PlaceholderApi)
colors and variables:

- `{red}`, `{green}`, `{yellow}`, `{aqua}`, etc.
- `{count}`: Number of affected entities (AOE
  spells)
- `{target}`: Target entity name
- `{caster}`: Caster name
- `{heal_amount}`: Amount healed

## Examples

See the `config.yml` file for complete examples of
each spell type.

## Troubleshooting

**Wands not working:**

- Ensure you have the required dependencies
  installed
- Check that players have the correct permissions
- Verify your config.yml has no syntax errors

**Particles not showing:**

- Some particles require specific configurations
- Check that particle type names are correct
- Ensure players have particles enabled in
  settings

**Effects not applying:**

- Verify effect names are spelled correctly
- Check that duration and amplifier values are
  valid
- Some effects may not work on certain entity
  types

## Support

For issues, suggestions, or contributions, please
open an issue on the plugin's repository.

## License

This plugin is provided as-is for use on
PocketMine-MP servers.