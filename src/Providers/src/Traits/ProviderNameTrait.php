<?php


namespace rollun\datahandler\Providers;

/**
 * Class ProviderNameTrait
 * @package rollun\datahandler\Providers
 * FIXME: need for test
 */
trait ProviderNameTrait
{
    /**
     * @var string
     */
    private $name;

    public function name(): string
    {
        return $this->name;
    }
}