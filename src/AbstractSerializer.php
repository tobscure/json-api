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

use Tobscure\JsonApi\Elements\Collection;
use Tobscure\JsonApi\Elements\Resource;

/**
 * This is the abstract serializer class.
 *
 * @author Toby Zerner <toby.zerner@gmail.com>
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * The type.
     *
     * @var string
     */
    protected $type;

    /**
     * The link|links.
     *
     * @var array|null
     */
    protected $link;

    /**
     * The include|includes.
     *
     * @var array|null
     */
    protected $include;

    /**
     * Create a new abstract serializer instance.
     *
     * @param array $include
     * @param array $link
     */
    public function __construct(array $include = array(), array $link = array())
    {
        $this->include = $include;
        $this->link = $link;
    }

    /**
     * Get the attributes array.
     *
     * @param $model
     *
     * @return array
     */
    abstract protected function getAttributes($model);

    /**
     * Get the id.
     *
     * @param $model
     *
     * @return string
     */
    protected function getId($model)
    {
        return $model->id;
    }

    /**
     * Set the include|includes.
     *
     * @param $include
     */
    public function setInclude($include)
    {
        $this->include = $include;
    }

    /**
     * Set the link|links.
     *
     * @param $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * Create a new collection.
     *
     * @param array $data
     *
     * @return \Tobscure\JsonApi\Elements\Collection|null
     */
    public function collection($data)
    {
        if (empty($data)) {
            return;
        }

        $resources = array();

        foreach ($data as $record) {
            $resources[] = $this->resource($record);
        }

        return new Collection($this->type, $resources);
    }

    /**
     * Create a new resource.
     *
     * @param array $data
     *
     * @return \Tobscure\JsonApi\Elements\Resource|null
     */
    public function resource($data)
    {
        if (empty($data)) {
            return;
        }

        if (!is_object($data)) {
            return new Resource($this->type, $data);
        }

        $included = $links = array();

        $relationships = array(
            'link' => $this->parseRelationshipPaths($this->link),
            'include' => $this->parseRelationshipPaths($this->include),
        );

        foreach (array('link', 'include') as $type) {
            $include = $type === 'include';

            foreach ($relationships[$type] as $name => $nested) {
                $method = $this->getRelationshipFromMethod($name);

                if ($method) {
                    $element = $method(
                        $data,
                        $include,
                        isset($relationships['include'][$name]) ? $relationships['include'][$name] : array(),
                        isset($relationships['link'][$name]) ? $relationships['link'][$name] : array()
                    );
                }

                if ($method && $element) {
                    if (!($element instanceof Relationship)) {
                        $element = new Relationship($element);
                    }
                    if ($include) {
                        $included[$name] = $element;
                    } else {
                        $links[$name] = $element;
                    }
                }
            }
        }

        return new Resource($this->type, $this->getId($data), $this->getAttributes($data), $links, $included);
    }

    /**
     * Get relationship from method name.
     *
     * @param string $name
     *
     * @return mixed
     */
    protected function getRelationshipFromMethod($name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        }
    }

    /**
     * Parse relationship paths.
     *
     * Given a flat array of relationship paths like:
     *
     * ['user', 'user.employer', 'user.employer.country', 'comments']
     *
     * create a nested array of relationship paths one-level deep that can
     * be passed on to other serializers:
     *
     * ['user' => ['employer', 'employer.country'], 'comments' => []]
     *
     * @param array $paths
     *
     * @return array
     */
    protected function parseRelationshipPaths(array $paths)
    {
        $tree = array();

        foreach ($paths as $path) {
            list($primary, $nested) = array_pad(explode('.', $path, 2), 2, null);

            if (!isset($tree[$primary])) {
                $tree[$primary] = array();
            }

            if ($nested) {
                $tree[$primary][] = $nested;
            }
        }

        return $tree;
    }
}
