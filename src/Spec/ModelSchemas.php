<?php

namespace Rapid\OAS\Spec;

class ModelSchemas
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
     * Populate the model schemas.
     */
    public function populate()
    {
        if ($this->config) {
            return collect($this->config)->flatMap(function ($item, $key) {
                $item = (new ModelSchema($key, $this->config[$key]))->populate();
                $key  = \substr($key, \strrpos($key, '\\') + 1);

                return [$key => $item];
            })->toArray();
        }
    }
}
