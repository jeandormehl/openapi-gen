<?php

namespace Rapid\OAS\Spec;

use cebe\openapi\spec\SecurityRequirement;

class SecurityRequirements
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
     * Populate the security requirements object.
     *
     * @return null|\cebe\openapi\spec\SecurityRequirement[]
     */
    public function populate()
    {
        if ($this->config) {
            return collect($this->config)->map(function ($item, $key) {
                return new SecurityRequirement([$key => $item]);
            })->flatten()->toArray();
        }
    }
}
