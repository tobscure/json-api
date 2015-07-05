<?php namespace Tobscure\JsonApi;

use JsonSerializable;

class Document implements JsonSerializable
{
    protected $links;

    protected $included = [];

    protected $meta;

    protected $data;

    public function addIncluded($link)
    {
<<<<<<< HEAD
	    $resources = [];
	    if ($linkage = $link->getLinkage()) {
		    $resources = $linkage->getResources();
	    }
=======
        $resources = $link->getData()->getResources();
>>>>>>> 194c730df0695326a91ff74d72f9486abb46cb39

        foreach ($resources as $k => $resource) {
            // If the resource doesn't have any attributes, then we don't need to
            // put it into the included part of the document.
            if (! $resource->getAttributes()) {
                unset($resources[$k]);
            } else {
                foreach ($resource->getIncluded() as $link) {
                    $this->addIncluded($link);
                }
            }
        }

        foreach ($resources as $k => $resource) {
            foreach ($this->included as $includedResource) {
                if ($includedResource->getType() === $resource->getType() && $includedResource->getId() === $resource->getId()) {
                    $includedResource->merge($resource);
                    unset($resources[$k]);
                    break;
                }
            }
        }

        if ($resources) {
            $this->included = array_merge($this->included, $resources);
        }

        return $this;
    }

    public function setData($element)
    {
        $this->data = $element;

        if ($element) {
            foreach ($element->getResources() as $resource) {
                foreach ($resource->getIncluded() as $link) {
                    $this->addIncluded($link);
                }
            }
        }

        return $this;
    }

    public function addLink($key, $value)
    {
        $this->links[$key] = $value;

        return $this;
    }

    public function addMeta($key, $value)
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    public function toArray()
    {
        $document = [];

        if (! empty($this->links)) {
            ksort($this->links);
            $document['links'] = $this->links;
        }

        if (! empty($this->data)) {
            $document['data'] = $this->data->toArray();
        }

        if (! empty($this->included)) {
            $document['included'] = [];
            foreach ($this->included as $resource) {
                $document['included'][] = $resource->toArray();
            }
        }

        if (! empty($this->meta)) {
            $document['meta'] = $this->meta;
        }

        return $document;
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }
    
    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
