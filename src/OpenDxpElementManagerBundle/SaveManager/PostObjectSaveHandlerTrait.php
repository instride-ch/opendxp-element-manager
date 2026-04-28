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

namespace Instride\Bundle\OpenDxpElementManagerBundle\SaveManager;

use OpenDxp\Model\DataObject\Concrete;

trait PostObjectSaveHandlerTrait
{
    public function postPreSave(Concrete $object, array $options): void
    {
    }

    public function postPostSave(Concrete $object, array $options): void
    {
    }

    public function postPreAdd(Concrete $object, array $options): void
    {
    }

    public function postPostAdd(Concrete $object, array $options): void
    {
    }

    public function postPreUpdate(Concrete $object, array $options): void
    {
    }

    public function postPostUpdate(Concrete $object, array $options): void
    {
    }

    public function postPreDelete(Concrete $object, array $options): void
    {
    }

    public function postPostDelete(Concrete $object, array $options): void
    {
    }
}
