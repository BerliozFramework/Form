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

namespace Berlioz\Form\Transformer;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Type\Date;
use Berlioz\Form\Type\Time;
use DateTime;
use DateTimeInterface;

/**
 * Class DateTimeTransformer.
 *
 * @package Berlioz\Form\Transformer
 */
class DateTimeTransformer implements TransformerInterface
{
    /**
     * @inheritdoc
     */
    public function toForm($data, ElementInterface $element)
    {
        if ($data instanceof DateTimeInterface) {
            return $data->format($element->getOption('format', 'Y-m-d H:i:s'));
        }

        return $data;
    }

    /**
     * @inheritdoc
     * @throws \Exception
     */
    public function fromForm($data, ElementInterface $element)
    {
        if (is_string($data) && $data != '') {
            return new DateTime($data);
        }

        return null;
    }
}