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

namespace Instride\Bundle\OpenDxpElementManagerBundle\DuplicateChecker\Constraints;

use Symfony\Component\Uid\Exception\InvalidArgumentException;
use Instride\Bundle\OpenDxpElementManagerBundle\DuplicateChecker\Constraints\Normalizer\CompareConditionMySqlNormalizer;
use OpenDxp\Model\DataObject\ClassDefinition;
use OpenDxp\Model\DataObject\ClassDefinition\Data\QueryResourcePersistenceAwareInterface;
use OpenDxp\Model\DataObject\Concrete;
use OpenDxp\Model\DataObject\Listing as Listing;
use OpenDxp\Model\Element\ElementInterface;
use Symfony\Component\Validator\Constraint;

class FieldsValidator extends DuplicateConstraintValidator
{
    private CompareConditionMySqlNormalizer $normalizer;

    public function __construct()
    {
        $this->normalizer = new CompareConditionMySqlNormalizer();
    }

    /**
     * @throws \Exception
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof Fields) {
            throw new InvalidArgumentException(
                \sprintf(
                    'Expected instance of "%s", got "%s"',
                    __NAMESPACE__ . '\Fields',
                    \get_debug_type($constraint)
                )
            );
        }

        $duplicates = $this->getDuplicatesByFields($value, $constraint->fields, $constraint->trim);

        if (null !== $duplicates && $duplicates->getTotalCount() > 0) {
            foreach ($duplicates->getObjects() as $duplicate) {
                $this->context->buildViolation($constraint->message)
                    ->setInvalidValue($duplicate)
                    ->addViolation();
            }
        }
    }

    /**
     * @throws \Exception
     */
    private function getDuplicatesByFields(Concrete $object, array $fields, bool $trim = false): ?Listing
    {
        $data = [];
        foreach ($fields as $field) {
            $getter = 'get' . \ucfirst($field);
            $value = $object->$getter();

            if (null === $value || '' === $value) {
                return null;
            }

            if ($trim) {
                $value = \trim($value);
            }

            $data[$field] = $value;
        }

        $duplicates = $this->getDuplicatesByData($object::getList(), $data);

        if (null !== $duplicates && $object->getId()) {
            $duplicates->addConditionParam('id != ?', $object->getId());
        }

        return $duplicates;
    }

    /**
     * @throws \Exception
     */
    private function getDuplicatesByData(Listing $list, array $data): ?Listing
    {
        if (!\count($data)) {
            return null;
        }

        $list->addConditionParam('published = ?', 1);

        foreach ($data as $field => $value) {
            if (null === $value || '' === $value) {
                return null;
            }

            $this->addNormalizedMysqlCompareCondition($list, $field, $value);
        }

        return $list;
    }

    /**
     * @throws \Exception
     */
    private function addNormalizedMysqlCompareCondition(Listing $list, string $field, mixed $value): void
    {
        $class = ClassDefinition::getById($list->getClassId());

        if (null === $class) {
            return;
        }

        /** @var QueryResourcePersistenceAwareInterface $fd */
        $fd = $class->getFieldDefinition($field);

        if (!$fd) {
            return;
        }

        if ($value instanceof ElementInterface) {
            $this->normalizer->addForSingleRelationFields($list, $field, $value);

            return;
        }

        if (\is_array($value) && ($value[0] instanceof ElementInterface)) {
            $this->normalizer->addForMultiRelationFields($list, $field, $value);

            return;
        }

        if ($value instanceof \DateTime) {
            $this->normalizer->addForDateFields($list, $field, $value);

            return;
        }

        if (\str_contains($fd->getQueryColumnType(), 'char')) {
            $this->normalizer->addForStringFields($list, $field, $value);

            return;
        }

        $type = \get_debug_type($value);

        throw new \InvalidArgumentException(
            \sprintf('Duplicate check for type of field %s not implemented (type of value: %s)', $field, $type)
        );
    }
}
