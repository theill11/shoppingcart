<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 05-02-2015 15:48
 */

namespace Theill11\Cart\Storage;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Theill11\Cart\ItemInterface;

class SessionStorage implements StorageInterface
{
    /** @var string The key this cart is stored under in the session */
    protected $key;

    /** @var SessionInterface The Session object */
    protected $session;

    public function __construct(SessionInterface $session, $key = '_cart')
    {
        $this->session = $session;
        $this->key = $key;
    }

    public function get($id)
    {
        $cache = $this->session->get($this->key, []);
        if (isset($cache[$id])) {
            $item = $cache[$id];
            if ($item instanceof ItemInterface) {
                return $item;
            }
        }
        return null;
    }

    public function remove($id)
    {
        $cache = $this->session->get($this->key, []);
        if (isset($cache[$id])) {
            $item = $cache[$id];
            if ($item instanceof ItemInterface) {
                unset($cache[$id]);
                $this->session->set($this->key, $cache);
                return true;
            }
        }
        return false;
    }

    public function has($id)
    {
        $cache = $this->session->get($this->key, []);
        return isset($cache[$id]) && $cache[$id] instanceof ItemInterface;
    }


    public function set(ItemInterface $newItem)
    {
        $cache = $this->session->get($this->key, []);
        $newItemId = $newItem->getId();
        $cache[$newItemId] = ($newItem);
        $this->session->set($this->key, $cache);
    }

    public function add(ItemInterface $newItem)
    {
        $cache = $this->session->get($this->key, []);
        $newItemId = $newItem->getId();
        if (isset($cache[$newItemId])) {
            $oldItem = $cache[$newItemId];
            if ($oldItem instanceof ItemInterface === false) {
                return false;
            }
            $newQty = $oldItem->getQuantity() + $newItem->getQuantity();
            $oldItem->setQuantity($newQty);
            $cache[$oldItem->getId()] = ($oldItem);
        } else {
            $cache[$newItemId] = $newItem;
        }
        $this->session->set($this->key, $cache);
        return true;
    }

    public function all()
    {
        $result = [];
        foreach ($this->session->get($this->key, []) as $item) {
            if ($item instanceof ItemInterface) {
                $result[$item->getId()] = $item;
            }
        }
        return $result;
    }

    public function clear()
    {
        $this->session->remove($this->key);
    }
}
