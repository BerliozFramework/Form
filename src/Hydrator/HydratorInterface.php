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

namespace Berlioz\Form\Hydrator;

use Berlioz\Form\Element\ElementInterface;

/**
 * Interface HydratorInterface.
 *
 * @package Berlioz\Form\Hydrator
 */
interface HydratorInterface
{
    /**
     * Get form element.
     *
     * @return \Berlioz\Form\Element\ElementInterface
     */
    public function getElement(): ElementInterface;

    /**
     * Hydrate object.
     *
     * @param mixed|null $mapped
     *
     * @return mixed
     * @throws \Berlioz\Form\Exception\HydratorException
     */
    public function hydrate(&$mapped = null);
}