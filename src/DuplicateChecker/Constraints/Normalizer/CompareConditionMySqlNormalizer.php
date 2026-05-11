<?php

declare(strict_types=1);

/**
 * OpenDXP Element Manager.
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

namespace Instride\Bundle\OpenDxpElementManagerBundle\DuplicateChecker\Constraints\Normalizer;

use OpenDxp\Model\DataObject\Listing;
use OpenDxp\Model\Element\ElementInterface;

class CompareConditionMySqlNormalizer
{
    public function addForStringFields(
        Listing $list,
        string $field,
        string $value,
        array $duplicateCheckTrimmedFields = []
    ): void {
        if (\in_array($field, $duplicateCheckTrimmedFields)) {
            $list->addConditionParam($field . ' LIKE ?', \mb_strtolower(\trim($value), 'UTF-8'));
        } else {
            $list->addConditionParam('TRIM(LCASE(' . $field . ')) = ?', \mb_strtolower(\trim($value), 'UTF-8'));
        }
    }

    public function addForDateFields(Listing $list, $field, \DateTime $value): void
    {
        $list->addConditionParam($field . ' = ?', $value->getTimestamp());
    }

    public function addForSingleRelationFields(Listing $list, $field, ElementInterface $value): void
    {
        $list->addConditionParam($field . '__id = ?', $value->getId());
    }

    public function addForMultiRelationFields(Listing $list, $field, $value): void
    {
        $ids = [];

        /** @var ElementInterface $row */
        foreach ($value as $row) {
            $ids[] = $row->getId();
        }

        $list->addConditionParam($field . ' = ?', \implode(',', $ids) . ',');
    }
}
