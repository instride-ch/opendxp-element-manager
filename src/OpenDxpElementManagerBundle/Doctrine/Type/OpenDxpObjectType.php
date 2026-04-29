<?php

declare(strict_types=1);

namespace Instride\Bundle\OpenDxpElementManagerBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\IntegerType;
use OpenDxp\Model\DataObject\Concrete;

final class OpenDxpObjectType extends IntegerType
{
    public const string NAME = 'opendxpObject';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Concrete
    {
        if ($value === null || $value === '') {
            return null;
        }

        $object = Concrete::getById((int) $value);

        return $object instanceof Concrete ? $object : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Concrete) {
            return $value->getId();
        }

        if (\is_int($value) || \ctype_digit((string) $value)) {
            return (int) $value;
        }

        throw ConversionException::conversionFailedInvalidType($value, self::NAME, ['null', Concrete::class, 'int']);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
