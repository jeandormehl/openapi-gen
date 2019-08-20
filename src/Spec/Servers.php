<?php

namespace Rapid\OAS\Spec;

use cebe\openapi\spec\Server;
use cebe\openapi\spec\ServerVariable;
use Illuminate\Support\Arr;

class Servers
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
     * Populate the servers object.
     *
     * @return \cebe\openapi\spec\Server[]
     */
    public function populate()
    {
        return collect($this->config)->map(function ($item) {
            if (Arr::has($item, 'variables')) {
                $item['variables'] = \array_merge(
                    $item['variables'],
                    collect($item['variables'])->map(function ($item) {
                        return new ServerVariable($item);
                    })->toArray()
                );
            }

            return new Server($item);
        })->toArray();
    }
}
