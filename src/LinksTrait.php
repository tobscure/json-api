<?php

/*
 * This file is part of JSON-API.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tobscure\JsonApi;

trait LinksTrait
{
    private $links = [];

    /**
     * Set the links.
     *
     * @param array $links
     */
    public function setLinks(array $links)
    {
        $this->links = $links;
    }

    /**
     * Set a link.
     *
     * @param string $key
     * @param string|Link $value
     */
    public function setLink($key, $value)
    {
        $this->links[$key] = $value;
    }

    /**
     * Remove a link.
     *
     * @param string $key
     */
    public function removeLink($key)
    {
        unset($this->links[$key]);
    }
}
