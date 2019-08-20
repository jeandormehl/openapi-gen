<?php

namespace Rapid\OAS\Spec;

use cebe\openapi\spec\Components as CebeComponents;
use cebe\openapi\spec\Header;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Response;
use cebe\openapi\spec\Schema;
use cebe\openapi\spec\SecurityScheme;
use Illuminate\Support\Arr;

class Components
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $modelSchemas;

    /**
     * @var array
     */
    protected $schemas;

    /**
     * @var array
     */
    protected $commonResponses;

    /**
     * @var array
     */
    protected $responses;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $requestBodies;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $securitySchemes;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        $this->modelSchemas    = Arr::pull($this->config, 'schemas.models', []);
        $this->commonResponses = Arr::pull($this->config, 'responses.statusCodes', []);

        $this->schemas         = Arr::get($this->config, 'schemas', []);
        $this->responses       = Arr::get($this->config, 'responses', []);
        $this->parameters      = Arr::get($this->config, 'parameters', []);
        $this->requestBodies   = Arr::get($this->config, 'requestBodies', []);
        $this->headers         = Arr::get($this->config, 'headers', []);
        $this->securitySchemes = Arr::get($this->config, 'securitySchemes', []);
    }

    /**
     * Populate the security requirements object.
     *
     * @return null|\cebe\openapi\spec\Components
     */
    public function populate()
    {
        if ($this->config) {
            $components = new CebeComponents([]);

            // schemas
            if ($this->schemas) {
                $components->schemas = collect($this->schemas)->map(function ($item) {
                    return new Schema($item);
                })->toArray();
            }

            // auto generated schemas from models
            if ($this->modelSchemas) {
                $components->schemas = \array_merge(
                    $components->schemas,
                    (new ModelSchemas($this->modelSchemas))->populate()
                );
            }

            // responses
            if ($this->responses) {
                $components->responses = collect($this->responses)->map(function ($item) {
                    return new Response($item);
                })->toArray();
            }

            // common responses
            if ($this->commonResponses) {
                $components->responses = ($components->responses ?? [])
                    + (new Responses($this->commonResponses))->populate();
            }

            // parameters
            if ($this->parameters) {
                $components->parameters = collect($this->parameters)->map(function ($item) {
                    return new Parameter($item);
                })->toArray();
            }

            // requestBodies
            if ($this->requestBodies) {
                $components->requestBodies = collect($this->requestBodies)->map(function ($item) {
                    return new RequestBody($item);
                })->toArray();
            }

            // headers
            if ($this->headers) {
                $components->headers = collect($this->headers)->map(function ($item) {
                    return new Header($item);
                })->toArray();
            }

            // securitySchemes
            if ($this->securitySchemes) {
                $components->securitySchemes = collect($this->securitySchemes)->map(
                    function ($item) {
                        return new SecurityScheme($item);
                    }
                )->toArray();
            }

            return $components;
        }
    }
}
