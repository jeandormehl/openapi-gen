<?php

namespace Rapid\OAS\Spec;

use cebe\openapi\spec\Response;
use cebe\openapi\spec\Type;
use Illuminate\Http\Response as IlluminateResponse;

class Responses
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $codes;

    /**
     * @var array
     */
    protected $schema;

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
     * Populate the responses object with common responses.
     *
     * @return \cebe\openapi\spec\Response[]
     */
    public function populate()
    {
        return collect($this->config)->reduce(function ($carry, $item) {
            $carry[$item] = new Response([
                'description' => IlluminateResponse::$statusTexts[$item],
                'content'     => [
                    'application/json' => [
                        'schema' => [
                            'type'       => Type::OBJECT,
                            'properties' => [
                                'message' => [
                                    'type'    => Type::STRING,
                                    'example' => IlluminateResponse::$statusTexts[$item],
                                ],
                                'status_code' => [
                                    'type'    => Type::INTEGER,
                                    'example' => $item,
                                ],
                            ],
                        ],
                    ],
                ],
            ]);

            return $carry;
        }, []);
    }
}
