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

class CookieStorage extends Storage
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

    public function all()
    {
        $result = [];
        $cookie = $this->request->cookies->get($this->name);
        if ($cookie) {
            $items = unserialize($cookie);
            foreach ($items as $item) {
                if ($item instanceof ItemInterface) {
                    $result[$item->getId()] = $item;
                }
            }
        }
        return $result;
    }

    public function clear()
    {
        $this->response->headers->clearCookie($this->name);
    }

    public function persist($content)
    {
        $cookie = new Cookie($this->name, serialize($content));
        $this->response->headers->setCookie($cookie);
    }
}
