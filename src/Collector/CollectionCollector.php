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

use Berlioz\Form\Collection;
use Berlioz\Form\Element\ElementInterface;
use Berlioz\Form\Exception\CollectorException;

class CollectionCollector extends AbstractCollector
{
    /** @var Collection Collection */
    private $collection;

    /**
     * CollectionCollector constructor.
     *
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @inheritdoc
     * @return Collection
     */
    public function getElement(): ElementInterface
    {
        return $this->collection;
    }

    public function collect($mapped = null)
    {
        $subMapped = $this->getSubMapped($this->getElement(), $mapped);

        if (is_null($subMapped)) {
            return [];
        }

        if (!(is_array($subMapped) || is_iterable($subMapped))) {
            throw new CollectorException('Collection data must be an array or iterable');
        }

        $collected = [];
        $prototype = $this->collection->getPrototype();

        foreach ($subMapped as $key => $value) {
            if ($this->collection->getOption('mapped', false, true)) {
                $collected[$key] = null;

                if (!is_null($value)) {
                    $collector = $this->locateCollector($prototype);

                    if ($collector instanceof TypeCollector) {
                        $collected[$key] = $value;
                        continue;
                    }

                    $collected[$key] = $collector->collect($value);
                }
            }
        }

        return $collected;
    }
}