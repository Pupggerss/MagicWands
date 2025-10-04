<?php

namespace pup\magicwands\commands;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\exception\ArgumentOrderException;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pup\magicwands\command\args\WandArg;
use pup\magicwands\Main;

final class WandCommand extends BaseCommand
{
    public function __construct(protected Plugin $plugin)
    {
        parent::__construct($plugin, "wand", "Give a wand to a player", ["/wands"]);
        $this->setPermission("magicwands.command.wand");

    }

    /**
     * @throws ArgumentOrderException
     */
    public function prepare()
    : void
    {
        $this->registerArgument(0, new WandArg("wand", true));
        $this->registerArgument(1, new RawStringArgument("player", true));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args)
    : void
    {
        $wandManager = Main::getInstance()->getWandManager();

        if (empty($args)) {
            $sender->sendMessage("§e=== Available Wands ===");
            foreach ($wandManager->getWandNames()
                     as
                     $name)
            {
                $sender->sendMessage("§7- §b" . $name);
            }
            $sender->sendMessage("§eUsage: /wand <type> [player]");
            return;
        }

        $wandType = $args["wand"];
        $targetPlayer = null;

        if (isset($args["player"])) {
            $targetPlayer = $this->plugin->getServer()->getPlayerByPrefix($args["player"]);
            if ($targetPlayer === null) {
                $sender->sendMessage("§cPlayer not found: " . $args["player"]);
                return;
            }
        } else {
            if (!$sender instanceof Player) {
                $sender->sendMessage("§cYou must specify a player when using this command from console!");
                return;
            }
            $targetPlayer = $sender;
        }

        if ($wandManager->giveWand($targetPlayer, $wandType)) {
            if ($sender === $targetPlayer) {
                $sender->sendMessage("§aYou received a §e" . $wandType . "§a!");
            } else {
                $sender->sendMessage("§aGave §e" . $targetPlayer->getName() . " §aa §e" . $wandType . "§a!");
                $targetPlayer->sendMessage("§aYou received a §e" . $wandType . " §afrom §e" . $sender->getName() . "§a!");
            }
        } else {
            $sender->sendMessage("§cInventory is full!");
        }
    }
}