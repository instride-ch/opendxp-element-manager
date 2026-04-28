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

namespace Instride\Bundle\OpenDxpElementManagerBundle\Repository;

use Instride\Bundle\OpenDxpElementManagerBundle\Resource\Repository\RepositoryInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateInterface;
use Instride\Bundle\OpenDxpElementManagerBundle\Model\DuplicateObjectInterface;
use OpenDxp\Model\DataObject\Concrete;

interface DuplicateObjectRepositoryInterface extends RepositoryInterface
{
    public function deleteForObject(Concrete $concrete);

    /**
     * @return DuplicateObjectInterface[]
     */
    public function findExactMatches(string $className): array;

    /**
     * @return DuplicateObjectInterface[]
     */
    public function findByDuplicateAndAlgorithmValue(int $currentId, string $algorithm, string $value): array;

    /**
     * @return DuplicateObjectInterface[]
     */
    public function findByDuplicate(DuplicateInterface $duplicate): array;
}
