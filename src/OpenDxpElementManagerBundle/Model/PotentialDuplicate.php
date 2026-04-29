<?php

declare(strict_types=1);

/**
 * OpenDxp Element Manager.
 *
 * LICENSE
 *
 * This source file is subject to the GNU General Public License version 3 (GPLv3)
 * For the full copyright and license information, please view the LICENSE.md and gpl-3.0.txt
 * files that are distributed with this source code.
 *
 * @copyright 2026 instride AG (https://instride.ch)
 * @license   https://github.com/instride-ch/opendxp-element-manager/blob/main/gpl-3.0.txt GNU General Public License version 3 (GPLv3)
 */

namespace Instride\Bundle\OpenDxpElementManagerBundle\Model;

use Instride\Bundle\OpenDxpElementManagerBundle\Resource\AbstractResource;
use Instride\Bundle\OpenDxpElementManagerBundle\Trait\TimestampableTrait;

class PotentialDuplicate extends AbstractResource implements PotentialDuplicateInterface
{
    use TimestampableTrait;

    protected int $id;
    public DuplicateObjectInterface $duplicateFrom;
    public DuplicateObjectInterface $duplicateTo;
    public bool $declined = false;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDuplicateFrom(): DuplicateObjectInterface
    {
        return $this->duplicateFrom;
    }

    public function setDuplicateFrom(DuplicateObjectInterface $duplicateFrom): void
    {
        $this->duplicateFrom = $duplicateFrom;
    }

    public function getDuplicateTo(): DuplicateObjectInterface
    {
        return $this->duplicateTo;
    }

    public function setDuplicateTo(DuplicateObjectInterface $duplicateTo): void
    {
        $this->duplicateTo = $duplicateTo;
    }

    public function getDeclined(): bool
    {
        return $this->declined;
    }

    public function setDeclined(bool $declined): void
    {
        $this->declined = $declined;
    }
}
