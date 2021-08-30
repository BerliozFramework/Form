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

namespace Berlioz\Form\Collector;

use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\CollectorException;

/**
 * Interface CollectorInterface.
 *
 * @package Berlioz\Form\Collector
 */
interface CollectorInterface
{
    /**
     * Get form element.
     *
     * @return ElementInterface
     */
    public function getElement(): ElementInterface;

    /**
     * Collect.
     *
     * @param mixed|null $mapped
     *
     * @return mixed
     * @throws CollectorException
     */
    public function collect($mapped = null);
}