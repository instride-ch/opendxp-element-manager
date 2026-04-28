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

namespace Instride\Bundle\OpenDxpElementManagerBundle\DuplicateChecker;

use OpenDxp\Model\Element\ElementInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class DuplicateService implements DuplicateServiceInterface
{
    public function __construct(private ValidatorInterface $duplicateChecker) {}

    /**
     * @inheritDoc
     */
    public function findDuplicates(ElementInterface $element, array $groups = null): array
    {
        $result = $this->duplicateChecker->validate($element, null, $groups);

        if ($result->count()) {
            return \array_map(
                static fn (ConstraintViolationInterface $result) => $result->getInvalidValue(),
                \iterator_to_array($result)
            );
        }

        return [];
    }
}
