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

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;
use DateTime;
use DateTimeInterface;
use Exception;

class DateTimeTransformer implements TransformerInterface
{
    /**
     * @inheritDoc
     */
    public function toForm(mixed $data, ElementInterface $element): mixed
    {
        if ($data instanceof DateTimeInterface) {
            return $data->format($element->getOption('format', 'Y-m-d H:i:s'));
        }

        return $data;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function fromForm(mixed $data, ElementInterface $element): ?DateTime
    {
        if (is_string($data) && $data != '') {
            return new DateTime($data);
        }

        return null;
    }
}