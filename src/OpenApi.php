<?php

namespace Rapid\OAS;

use cebe\openapi\spec\ExternalDocumentation;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi as CebeOpenApi;
use cebe\openapi\spec\Paths;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Rapid\OAS\Spec\Components;
use Rapid\OAS\Spec\SecurityRequirements;
use Rapid\OAS\Spec\Servers;
use Rapid\OAS\Spec\Tags;

class OpenApi
{
    /**
     * @var string
     */
    const VERSION = '3.0.2';

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \cebe\openapi\spec\OpenApi
     */
    protected $oas;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = config('oas', []);
        $this->oas    = new CebeOpenApi([]);

        $this->populate();
    }

    /**
     * Get the Open Api specification.
     */
    public function getSpecification()
    {
        $this->validate();

        return $this->oas;
    }

    /**
     * Populate the specification.
     */
    protected function populate()
    {
        // openapi version
        $this->oas->openapi = Arr::get($this->config, 'openapi');

        // info object
        $this->oas->info = new Info(Arr::get($this->config, 'info'));

        // servers object
        if ($servers = (new Servers(Arr::get($this->config, 'servers', [])))->populate()) {
            $this->oas->servers = $servers;
        }

        // paths object
        $this->oas->paths = new Paths(Arr::get($this->config, 'paths'));

        // components object
        if ($components = (new Components(Arr::get($this->config, 'components', [])))->populate()) {
            $this->oas->components = $components;
        }

        // security object
        if ($security = (new SecurityRequirements(Arr::get($this->config, 'security', [])))->populate()) {
            $this->oas->security = $security;
        }

        // tags object
        if ($tags = (new Tags(Arr::get($this->config, 'tags', [])))->populate()) {
            $this->oas->tags = $tags;
        }

        // external documentation object
        if ($externalDocs = Arr::get($this->config, 'externalDocs', [])) {
            $this->oas->externalDocs = new ExternalDocumentation($externalDocs);
        }
    }

    /**
     * Validate the specification.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validate()
    {
        $this->oas->validate();

        $errors = collect($this->oas->getErrors())->reduce(function ($carry, $item) {
            $key   = \strtok($item, ' ');
            $carry = \array_merge_recursive($carry ?? [], [$key => [$item]]);

            return $carry;
        });

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
