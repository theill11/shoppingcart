<?php
/**
 * @author: Johnny Theill <j.theill@gmail.com>
 * @date: 05-02-2015 15:44
 */

namespace Theill11\Cart\Storage;

use Theill11\Cart\ItemInterface;

interface StorageInterface
{
    /**
     * Get Item with given id
     * @param $id int
     * @return ItemInterface|null
     */
    public function get($id);

    /**
     * Remove Item with given id
     * @param $id int The id of the Item
     * @return bool True if the Item was removed, false otherwise
     */
    public function remove($id);

    /**
     * Check whether Item with given id has been added
     * @param $id int The id of the Item
     * @return bool True if an Item with the given id exist, false otherwise
     */
    public function has($id);

    /**
     * Adds or replaces an existing Item
     * @param ItemInterface $item The Item to add/replace
     * @return bool True if the Item was set, false otherwise
     */
    public function set(ItemInterface $item);

    /**
     * Adds an Item
     * @param ItemInterface $item The Item to add
     * @return bool True if the Item was added, false otherwise
     */
    public function add(ItemInterface $item);

    /**
     * Get all Items
     * @return ItemInterface[] Array of ItemInterface
     */
    public function all();

    /**
     * Clear the storage
     * @return void
     */
    public function clear();

}
