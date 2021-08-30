<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2019 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Validator;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Validator\Constraint\ConstraintInterface;

/**
 * Interface ValidatorInterface.
 */
interface ValidatorInterface
{
    /**
     * Validate.
     *
     * @param ElementInterface $value
     *
     * @return ConstraintInterface[]
     * @throws ValidatorException
     */
    public function validate(ElementInterface $value): array;
}