<?php

declare(strict_types=1);

namespace Tooling\Composer\Plugins;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Tooling\Composer\Plugins\Features\DiscoverTooling;

class PublishConfigurations implements EventSubscriberInterface, PluginInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_AUTOLOAD_DUMP => 'handlePostAutoloadDump',
        ];
    }

    public function handlePostAutoloadDump(Event $event): void
    {
        new DiscoverTooling($event->getComposer())->run();
    }

    public function activate(Composer $composer, IOInterface $io): void {}

    public function deactivate(Composer $composer, IOInterface $io): void {}

    public function uninstall(Composer $composer, IOInterface $io): void {}
}
