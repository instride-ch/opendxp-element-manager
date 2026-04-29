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

namespace Instride\Bundle\OpenDxpElementManagerBundle\Metadata\DuplicatesIndex;

class Metadata implements MetadataInterface
{
    /**
     * @param GroupMetadataInterface[] $groups
     * @param string[]                 $listFields
     */
    public function __construct(private string $className, private array $groups, private array $listFields)
    {
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * @inheritDoc
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getListFields(): array
    {
        return $this->listFields;
    }

    public function getGroup(string $name): ?GroupMetadataInterface
    {
        $filteredGroups = \array_filter(
            $this->groups,
            static fn (GroupMetadataInterface $groupMetadata) => $groupMetadata->getName() === $name
        );

        return \reset($filteredGroups);
    }
}
