<?php

declare(strict_types=1);

namespace Instride\Bundle\OpenDxpElementManagerBundle\Trait;

interface TimestampableInterface
{
    public function getCreationDate(): ?\DateTimeInterface;

    public function setCreationDate(?\DateTimeInterface $creationDate): void;

    public function getModificationDate(): ?\DateTimeInterface;

    public function setModificationDate(?\DateTimeInterface $modificationDate): void;
}
