<?php

namespace Rapid\OAS\Console\Commands;

use cebe\openapi\Writer;
use Illuminate\Console\Command;
use Rapid\OAS\OpenApi;

class YamlGenerator extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'oas:yaml';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Write OpenApi specification to .yml file.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        if ($path = config('oas.yaml')) {
            Writer::writeToYamlFile(app(OpenApi::class)->getSpecification(), $path);
        }
    }
}
