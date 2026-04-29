<?php

declare(strict_types=1);

namespace Instride\Bundle\OpenDxpElementManagerBundle\Factory;

interface FactoryInterface
{
    public function createNew(): object;
}
