<?php

namespace Rapid\OAS\Spec;

use cebe\openapi\spec\ExternalDocumentation;
use cebe\openapi\spec\Tag;
use Illuminate\Support\Arr;

class Tags
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Populate the tags object.
     *
     * @return \cebe\openapi\spec\Tag[]
     */
    public function populate()
    {
        return collect($this->config)->map(function ($item) {
            if (Arr::has($item, 'externalDocs')) {
                $item['externalDocs'] = new ExternalDocumentation($item['externalDocs']);
            }

            return new Tag($item);
        })->toArray();
    }
}
