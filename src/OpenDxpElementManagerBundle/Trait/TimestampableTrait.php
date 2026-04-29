<?php

declare(strict_types=1);

namespace Instride\Bundle\OpenDxpElementManagerBundle\Trait;

trait TimestampableTrait
{
    protected ?\DateTimeInterface $creationDate = null;
    protected ?\DateTimeInterface $modificationDate = null;

    public function onPrePersist(): void
    {
        $now = new \DateTime();
        if ($this->creationDate === null) {
            $this->creationDate = $now;
        }
        $this->modificationDate = $now;
    }

    public function onPreUpdate(): void
    {
        $this->modificationDate = new \DateTime();
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creationDate;
    }

    public function setCreationDate(?\DateTimeInterface $creationDate): void
    {
        $this->creationDate = $creationDate;
    }

    public function getModificationDate(): ?\DateTimeInterface
    {
        return $this->modificationDate;
    }

    public function setModificationDate(?\DateTimeInterface $modificationDate): void
    {
        $this->modificationDate = $modificationDate;
    }
}
