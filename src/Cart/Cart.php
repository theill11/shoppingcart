<?php
/**
 * @author Johnny Theill <j.theill@gmail.com>
 * @date 19-01-2015 02:27
 */

namespace Theill11\Cart;

use Theill11\Cart\Storage\StorageInterface;

/**
 * Class Cart
 *
 * @package Theill11\Cart
 */
class Cart implements \Countable, \ArrayAccess
{
    /** @var ItemInterface[] */
    protected $items = [];

    /** @var StorageInterface */
    protected $storage;

    /**
     * @param Storage\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param $id int Id of the Item to retrieve
     * @return Item|null The given Item or null if not found.
     */
    public function getItem($id)
    {
        return $this->storage->get($id);
    }

    /**
     * Get all Items
     * @return ItemInterface[] An array of Items,  if no Items then an empty array
     */
    public function getItems()
    {
        return $this->storage->all();
    }

    /**
     * Adds an Item to the cart
     * @param ItemInterface $item The Item to add
     */
    public function addItem(ItemInterface $item)
    {
        if ($item->isValid() === false) {
            throw new \InvalidArgumentException('The item is not valid');
        }
        $this->storage->add($item);
    }

    /**
     * Adds multiple Items to the cart
     * @param ItemInterface[] $items Array of Items to add
     */
    public function addItems(array $items)
    {
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * Sets an Item, if there is all ready an Item with same id, it will be replaced.
     * @param $item ItemInterface
     */
    public function setItem(ItemInterface $item)
    {
        $this->storage->set($item);
    }

    /**
     * Sets multiple Items
     * @param ItemInterface[] $items An array of Items
     */
    public function setItems(array $items)
    {
        $this->clear();
        $this->addItems($items);
    }

    /**
     * Removes an Item from the cart
     * @param $id int Id of the Item to remove
     */
    public function removeItem($id)
    {
        $this->storage->remove($id);
    }

    /**
     * @param $id int id of the Item to check
     * @return bool whether the Item is added to the card
     */
    public function hasItem($id)
    {
        return $this->storage->has($id);
    }

    /**
     * Clears the storage
     */
    public function clear()
    {
        $this->storage->clear();
    }

    /**
     * Get the total price for all Items
     * @return float
     */
    public function getTotal()
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            /** @var $item ItemInterface */
            $total += $item->getTotal();
        }
        return $total;
    }

    /**
     * Count the Items added to the cart
     * @return int The count as an integer.
     */
    public function count()
    {
        return (int) count($this->getItems());
    }

    /**
     * Whether an Item with given id is added to the card
     * @param int $id An id to check for.
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($id)
    {
        return $this->hasItem($id);
    }

    /**
     * Offset to retrieve
     * @param int $id The id of the Item to retrieve.
     * @return ItemInterface|null Returns an ItemInterface or null if not found.
     */
    public function offsetGet($id)
    {
        return $this->getItem($id);
    }

    /**
     * Adds or sets an Item to the cart
     * @param int|null $id If given, any existing Item with same id will be replaced.
     * @param ItemInterface $item The value to set.
     * @return void
     */
    public function offsetSet($id, $item)
    {
        /** @var $item Item */
        if (is_null($id)) {
            $this->addItem($item);
        } elseif ($id === $item->getId()) {
            $this->setItem($item);
        } else {
            throw new \InvalidArgumentException('The index and id of the item must be the same');
        }
    }

    /**
     * Unset the Item with given id
     * @param int $id The id to unset.
     * @return void
     */
    public function offsetUnset($id)
    {
        $this->removeItem($id);
    }
}
