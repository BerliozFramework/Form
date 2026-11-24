<?php
/*
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2026 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Form\Hydrator;

use Closure;
use Countable;

/**
 * Class HydrationQueue.
 *
 * Ordered list of write operations planned by hydrators. Nothing is written on mapped data
 * until the queue is committed.
 */
final class HydrationQueue implements Countable
{
    /** @var Closure[] */
    private array $operations = [];

    /**
     * Add a write operation.
     *
     * @param Closure $operation
     *
     * @return void
     */
    public function add(Closure $operation): void
    {
        $this->operations[] = $operation;
    }

    /**
     * Commit queued operations, in order.
     *
     * The queue is emptied before running operations, so a committed operation is never run twice.
     *
     * @return void
     */
    public function commit(): void
    {
        $operations = $this->operations;
        $this->operations = [];

        foreach ($operations as $operation) {
            $operation();
        }
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->operations);
    }
}
