<?php

namespace pup\magicwands\wands;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pup\magicwands\Main;
use pup\magicwands\spells\types\ProjectileSpell;

final class WandListener implements Listener
{

    private Main $plugin;

    public function __construct(Main $plugin)
    {
        $this->plugin = $plugin;
    }

    public function onPlayerItemUse(PlayerItemUseEvent $event)
    : void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();

        $wandManager = $this->plugin->getWandManager();

        if (!$wandManager->isWand($item)) {
            return;
        }

        $wand = $wandManager->getWandFromItem($item);
        if ($wand === null) {
            return;
        }

        if ($wand->canUseInAir()) {
            $event->cancel();
            $wand->onUse($player, $player->getPosition());
        }
    }

    public function onPlayerInteract(PlayerInteractEvent $event)
    : void
    {
        $player = $event->getPlayer();
        $item = $event->getItem();
        $action = $event->getAction();

        if ($action !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) {
            return;
        }

        $wandManager = $this->plugin->getWandManager();

        if (!$wandManager->isWand($item)) {
            return;
        }

        $wand = $wandManager->getWandFromItem($item);
        if ($wand === null) {
            return;
        }

        if ($wand->canUseInAir()) {
            return;
        }

        $event->cancel();
        $wand->onUse($player, $player->getPosition());
    }

    public function onProjectileHit(ProjectileHitEvent $event)
    : void
    {
        ProjectileSpell::handleProjectileHit($event);
    }
}