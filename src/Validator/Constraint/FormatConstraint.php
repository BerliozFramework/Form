<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2017 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

namespace Berlioz\Form\Validator\Constraint;

/**
 * Class FormatConstraint.
 *
 * @package Berlioz\Form\Validator\Constraint
 */
class FormatConstraint extends BasicConstraint
{
    /**
     * FormatConstraint constructor.
     *
     * @param array $context
     */
    public function __construct(array $context = [])
    {
        parent::__construct($context, 'Bad field value format.');
    }
}