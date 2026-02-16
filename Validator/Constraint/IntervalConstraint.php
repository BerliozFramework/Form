<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Validator\Constraint;

class IntervalConstraint extends BasicConstraint
{
    /**
     * IntervalConstraint constructor.
     *
     * @param array $context
     */
    public function __construct(array $context = [])
    {
        $message = 'Bad field value.';

        if (is_null($context['min']) && !is_null($context['max'])) {
            $message = 'The value of the field must be less than or equal to %max%.';
        }
        if (!is_null($context['min']) && is_null($context['max'])) {
            $message = 'The value of the field must be greater than or equal to %min%.';
        }
        if (!is_null($context['min']) && !is_null($context['max'])) {
            $message = 'The value of the field must be between %min% and %max%.';
        }

        parent::__construct($context, $message);
    }
}