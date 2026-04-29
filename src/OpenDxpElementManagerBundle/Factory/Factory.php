<?php

declare(strict_types=1);

namespace Instride\Bundle\OpenDxpElementManagerBundle\Factory;

final readonly class Factory implements FactoryInterface
{
    public function __construct(private string $className)
    {
    }

    public function createNew(): object
    {
        return new ($this->className)();
    }
}
