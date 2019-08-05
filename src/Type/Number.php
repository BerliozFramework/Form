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

namespace Berlioz\Form\Type;

use Berlioz\Form\Transformer\NumberTransformer;
use Berlioz\Form\Validator\FormatValidator;
use Berlioz\Form\Validator\IntervalValidator;

/**
 * Class Number.
 *
 * @package Berlioz\Form\Type
 */
class Number extends AbstractType
{
    const DEFAULT_TRANSFORMER = NumberTransformer::class;

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return 'number';
    }

    /**
     * @inheritdoc
     * @throws \Berlioz\Form\Exception\ValidatorException
     */
    public function build()
    {
        parent::build();

        // Format validator
        if ($this->hasValidator(FormatValidator::class) === false) {
            $this->addValidator(new FormatValidator('/^-?[0-9]+(\.[0-9]+)?$/'));
        }

        // Interval validator
        if ($this->hasValidator(IntervalValidator::class) === false) {
            $this->addValidator(new IntervalValidator());
        }
    }
}