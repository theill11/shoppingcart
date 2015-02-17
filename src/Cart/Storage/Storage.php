<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 17-02-2015 09:05
 */

namespace Theill11\Cart\Storage;

use Theill11\Cart\ItemInterface;

abstract class Storage implements StorageInterface
{

    public function get($id)
    {
        $cache = $this->all();
        return isset($cache[$id]) ? $cache[$id] : null;
    }

    public function remove($id)
    {
        $cache = $this->all();
        if (isset($cache[$id])) {
            unset($cache[$id]);
            $this->persist($cache);
            return true;
        }
        return false;
    }

    public function has($id)
    {
        $cache = $this->all();
        return isset($cache[$id]);
    }

    public function set(ItemInterface $newItem)
    {
        $cache = $this->all();
        $newItemId = $newItem->getId();
        $cache[$newItemId] = ($newItem);
        $this->persist($cache);
    }

    public function add(ItemInterface $newItem)
    {
        $cache = $this->all();
        $newItemId = $newItem->getId();
        if (isset($cache[$newItemId])) {
            /** @var \Theill11\Cart\ItemInterface $oldItem */
            $oldItem = $cache[$newItemId];
            $newQty = $oldItem->getQuantity() + $newItem->getQuantity();
            $oldItem->setQuantity($newQty);
            $cache[$oldItem->getId()] = ($oldItem);
        } else {
            $cache[$newItemId] = $newItem;
        }
        $this->persist($cache);
    }

    abstract public function all();

    abstract public function clear();

    abstract public function persist($cache);
}
