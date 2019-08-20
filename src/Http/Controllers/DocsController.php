<?php

namespace Rapid\OAS\Http\Controllers;

use Illuminate\Http\Response;
use Rapid\OAS\OpenApi;

class DocsController
{
    /**
     * Documentation action.
     *
     * @return string
     */
    public function docs()
    {
        return new Response(
            \json_encode(app(OpenApi::class)->getSpecification()->getSerializableData()),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}
