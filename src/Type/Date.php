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

use Berlioz\Form\Transformer\DateTimeTransformer;

class Date extends AbstractType
{
    public function __construct(array $options = [])
    {
        $options = array_replace(
            [
                'format' => 'Y-m-d',
                'transformer' => new DateTimeTransformer()
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
        return 'date';
    }

    /**
     * @inheritDoc
     * @todo Add validator for min, max, step attributes
     */
    public function build(): void
    {
        parent::build();
    }
}