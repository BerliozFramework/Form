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

interface TransformerInterface
{
    /**
     * Transform data to form.
     *
     * @param mixed $data
     * @param ElementInterface $element
     *
     * @return mixed
     */
    public function toForm(mixed $data, ElementInterface $element): mixed;

    /**
     * Transform data from form.
     *
     * @param mixed $data
     * @param ElementInterface $element
     *
     * @return mixed
     */
    public function fromForm(mixed $data, ElementInterface $element): mixed;
}