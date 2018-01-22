<?php

/*
 * This file is part of the DreamCommerce Shop AppStore package.
 *
 * (c) DreamCommerce
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DreamCommerce\Component\ShopAppstore\Model;

use ArrayAccess;
use Countable;
use Iterator;

abstract class AbstractItemList implements Iterator, Countable, ArrayAccess
{
    /**
     * @var ItemInterface[]
     */
    protected $items;

    /**
     * @var int
     */
    protected $pointer = 0;

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @param array $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        $array = [];
        foreach($this->items as $item) {
            $array[] = $item->getId();
        }

        return $array;
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }

    public function current(): ItemInterface
    {
        if ($this->valid() === false) {
            return null;
        }

        return $this->items[$this->pointer];
    }

    public function key(): int
    {
        return $this->pointer;
    }

    public function next(): void
    {
        ++$this->pointer;
    }

    public function valid(): bool
    {
        return $this->pointer < $this->count;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function offsetExists($offset): bool
    {
        return isset($this->items[(int) $offset]);
    }

    public function offsetGet($offset): ItemInterface
    {
        $this->pointer = (int) $offset;

        return $this->current();
    }

    public function offsetSet($offset, $value): void
    {
        if(null === $offset) {
            $this->items[] = $value;
            $this->count++;
        }
    }

    public function offsetUnset($offset): void
    {
        if(isset($this->items[(int) $offset])) {
            unset($this->items[(int) $offset]);
            $this->items = array_values($this->items);

            $this->count--;
            if($this->pointer > 0) {
                $this->pointer--;
            }
        }
    }
}