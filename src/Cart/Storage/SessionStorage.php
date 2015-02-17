<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 05-02-2015 15:48
 */

namespace Theill11\Cart\Storage;

use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Theill11\Cart\ItemInterface;

class SessionStorage extends Storage
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

    public function persist($cache)
    {
        $this->session->set($this->key, $cache);
    }
}
