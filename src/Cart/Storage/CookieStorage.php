<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 05-02-2015 19:22
 */

namespace Theill11\Cart\Storage;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Theill11\Cart\ItemInterface;

class CookieStorage implements StorageInterface
{
    /** @var string name of the Cookie */
    protected $name;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    public function __construct(Request $request, Response $response, $name = '_cart')
    {
        $this->request = $request;
        $this->response = $response;
        $this->name = $name;
    }

    public function get($id)
    {
        $cache = $this->all();
        if (isset($cache[$id])) {
            $item = $cache[$id];
            if ($item instanceof ItemInterface && $item->getId() === $id) {
                return $item;
            }
        }
        return null;
    }

    public function remove($id)
    {
        $cache = $this->all();
        if (isset($cache[$id])) {
            $item = $cache[$id];
            if ($item instanceof ItemInterface && $item->getId() === $id) {
                unset($cache[$id]);
                $this->setCookie($cache);
                return true;
            }
        }
        return false;
    }

    public function has($id)
    {
        return isset($this->all()[$id]);
    }


    public function set(ItemInterface $newItem)
    {
        $cache = $this->all();
        $newItemId = $newItem->getId();
        $cache[$newItemId] = $newItem;
        $this->setCookie($cache);
    }

    public function add(ItemInterface $newItem)
    {
        $cache = $this->all();
        $newItemId = $newItem->getId();
        if (isset($cache[$newItemId])) {
            /** @var \Theill11\Cart\Item $oldItem */
            $oldItem = $cache[$newItemId];
            if ($oldItem instanceof ItemInterface === false) {
                return false;
            }
            $newQty = $oldItem->getQuantity() + $newItem->getQuantity();
            $oldItem->setQuantity($newQty);
            $cache[$oldItem->getId()] = $oldItem;
        } else {
            $cache[$newItemId] = $newItem;
        }
        $this->setCookie($cache);
        return true;
    }

    public function all()
    {
        $cookie = $this->request->cookies->get($this->name);
        if ($cookie) {
            return unserialize($cookie);
        }
        return [];
    }

    public function clear()
    {
        $this->response->headers->clearCookie($this->name);
    }

    protected function setCookie($content)
    {
        $cookie = new Cookie($this->name, serialize($content));
        $this->response->headers->setCookie($cookie);
    }
}
