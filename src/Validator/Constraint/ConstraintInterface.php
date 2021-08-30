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

namespace Berlioz\Form\Validator\Constraint;

/**
 * Interface ConstraintInterface.
 */
interface ConstraintInterface
{
    /**
     * ConstraintInterface constructor.
     *
     * @param array $context
     */
    public function __construct(array $context = []);

    /**
     * Get context.
     *
     * @return array
     */
    public function getContext(): array;

    /**
     * __toString() PHP magic method.
     *
     * @return string
     */
    public function __toString(): string;
}