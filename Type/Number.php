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

namespace Berlioz\Form\Type;

use Berlioz\Form\Exception\ValidatorException;
use Berlioz\Form\Transformer\NumberTransformer;
use Berlioz\Form\Validator\FormatValidator;
use Berlioz\Form\Validator\IntervalValidator;
use Berlioz\Form\Validator\NumberFormatValidator;

class Number extends AbstractType
{
    public function __construct(array $options = [])
    {
        $options = array_replace(
            [
                'transformer' => new NumberTransformer()
            ],
            $options
        );

        parent::__construct($options);
    }

    /**
     * @inheritDoc
     */
    public function getType(): string
    {
        return 'number';
    }

    /**
     * @inheritDoc
     * @throws ValidatorException
     */
    public function build(): void
    {
        parent::build();

        // Format validator
        if ($this->hasValidator(FormatValidator::class) === false) {
            $this->addValidator(new NumberFormatValidator());
        }

        // Interval validator
        if ($this->hasValidator(IntervalValidator::class) === false) {
            $this->addValidator(new IntervalValidator());
        }
    }
}