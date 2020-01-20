<?php


namespace rollun\datahandler\Providers;


use rollun\datahandler\Providers\Source\SourceInterface;

interface ProviderInterface
{
    public function name(): string;

    public function attach($observer, string $id, $observerId = null): void;

    public function notify($source, string $id): void;

    public function provide(SourceInterface $source, string $id, array $options = []);
}