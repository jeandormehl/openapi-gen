<?php

namespace Rapid\OAS\Spec;

use cebe\openapi\spec\Schema;
use cebe\openapi\spec\Type;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ModelSchema
{
    /**
     * @var null|\Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var null|string
     */
    protected $simpleName;

    /**
     * @var array
     */
    protected $properties = [];

    /**
     * @var array
     */
    protected $required = [];

    /**
     * @var array
     */
    protected $hidden = [];

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $fqcn;

    /**
     * @var null|array
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($fqcn, array $config = [])
    {
        $this->fqcn   = $fqcn;
        $this->config = $config;
    }

    /**
     * Populate the model schema.
     */
    public function populate()
    {
        $this->prepare();

        return new Schema([
            'title'       => $this->simpleName,
            'description' => $this->simpleName . ' Model (Auto Generated)',
            'required'    => $this->required,
            'properties'  => $this->properties,
        ]);
    }

    /**
     * Prepare object.
     */
    protected function prepare()
    {
        $this->simpleName = \substr($this->fqcn, \strrpos($this->fqcn, '\\') + 1);
        $this->model      = $this->setModel($this->fqcn);
        $this->table      = $this->model->getTable();

        $this->setHidden();
        $this->getDatabaseProperties();
    }

    /**
     * Set the model schema properties.
     *
     * @param array  $results
     * @param string $name
     * @param string $default
     * @param string $nullable
     * @param string $dataType
     */
    protected function setProperties(array $results, $name, $default, $nullable, $dataType)
    {
        $this->properties = collect($results)->reduce(
            function ($carry, $item) use ($name, $default, $nullable, $dataType) {
                if (!\in_array($item->{$name}, $this->hidden, true)) {
                    $type   = $this->getTypes(Str::lower($item->{$dataType}));
                    $format = $type['format'] ?? null;
                    $type   = $type['type'] ?? 'string';
                    $default = \str_replace(["'", '"'], '', $item->{$default});

                    $data = [
                        'description' => $item->{$name},
                        'type'        => $type,
                        'nullable'    => $this->isNullable($nullable, $item->{$nullable}),
                    ];

                    $data = ($default) ? \array_merge($data, ['default' => $default]) : $data;
                    $data = ($format) ? \array_merge($data, ['format' => $format]) : $data;

                    $carry[$item->{$name}] = $data;
                }

                return $carry;
            }
        );
    }

    /**
     * Set the model schema required values.
     *
     * @param array  $results
     * @param string $name
     * @param string $nullable
     */
    protected function setRequired(array $results, $name, $nullable)
    {
        $this->required = collect($results)->reduce(
            function ($carry, $item) use ($name, $nullable) {
                if (!$this->isNullable($nullable, $item->{$nullable})) {
                    $carry[] = $item->{$name};
                }

                return $carry;
            }
        );
    }

    /**
     * Get the properties from database.
     */
    protected function getDatabaseProperties()
    {
        $connection = env('DB_CONNECTION');
        $database   = env('DB_DATABASE');
        $sql        = '';

        $name     = 'COLUMN_NAME';
        $default  = 'COLUMN_DEFAULT';
        $nullable = 'IS_NULLABLE';
        $dataType = 'DATA_TYPE';

        switch ($connection) {
            case 'mysql':
                $sql .= "SELECT ${name}, ${default}, ${nullable}, ${dataType} ";
                $sql .= 'FROM information_schema.COLUMNS ';
                $sql .= "WHERE TABLE_SCHEMA='${database}' ";
                $sql .= "AND TABLE_NAME='{$this->table}' ";

                break;
            case 'sqlite':
                $name     = 'name';
                $default  = 'dflt_value';
                $nullable = 'notnull';
                $dataType = 'type';

                $sql .= "PRAGMA table_info({$this->table});";

                break;
            case 'pgsql':
                $name     = 'column_name';
                $default  = 'column_default';
                $nullable = 'is_nullable';
                $dataType = 'data_type';

                $sql .= "SELECT ${name}, ${default}, ${nullable}, ${dataType} ";
                $sql .= 'FROM information_schema.columns ';
                $sql .= "WHERE table_name = '{$this->table}'";

                break;
            case 'oci':
            case 'oci8':
            case 'oracle':
                $name     = 'COLUMN_NAME';
                $default  = 'DEFAULT';
                $nullable = 'NULLABLE';
                $dataType = 'DATA_TYPE';

                $sql .= "SELECT ${name}, ${default}, ${nullable}, ${dataType} ";
                $sql .= 'FROM ALL_TAB_COLUMNS ';
                $sql .= "WHERE TABLE_NAME = '{$this->table}'";

                break;
            default:
                return [];
        }

        try {
            if ($results = DB::select($sql)) {
                $this->setProperties($results, $name, $default, $nullable, $dataType);
                $this->setRequired($results, $name, $nullable);
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set the model form the FQCN.
     *
     * @param string $fqcn
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function setModel($fqcn)
    {
        if (\class_exists($fqcn)) {
            $model = new $fqcn();

            if ($model instanceof Model) {
                $this->model = $model;

                return $this->model;
            }
        }

        throw new ModelNotFoundException('Could not find model: ' . $fqcn);
    }

    /**
     * Set the hidden properties.
     */
    protected function setHidden()
    {
        $hidden = $this->model->getHidden() ?? [];

        if ($this->config['hidden'] ?? []) {
            $hidden = \array_merge($hidden, $this->config['hidden']);
        }

        $this->hidden = $hidden;
    }

    /**
     * Determine if column is nullable.
     *
     * @param string     $descriptor
     * @param int|string $value
     *
     * @return bool
     */
    protected function isNullable($descriptor, $value)
    {
        switch ($descriptor) {
            case 'notnull':
                return (int) $value !== 1;

            case 'NULLABLE':
                return $value === 'Y';

            case 'IS_NULLABLE':
            case 'is_nullable':
                return $value === 'YES';

            default:
                return false;
        }
    }

    /**
     * Convert database types to OAS types.
     *
     * @param string $type
     *
     * @return array
     */
    protected function getTypes(string $type)
    {
        switch ($type) {
            case 'bigint':
            case 'mediumint':
                return [
                    'type'   => Type::INTEGER,
                    'format' => Format::INT64,
                ];

            case 'blob':
            case 'bfile':
            case 'bytea':
            case 'clob':
            case 'nclob':
            case 'raw':
            case 'long raw':
                return [
                    'type'   => Type::STRING,
                    'format' => Format::BINARY,
                ];

            case 'date':
                return [
                    'type'   => Type::STRING,
                    'format' => Format::DATE,
                ];

            case 'datetime':
            case 'time with time zone':
            case 'time without time zone':
            case 'timestamp':
                return [
                    'type'   => Type::STRING,
                    'format' => Format::DATETIME,
                ];

            case 'int':
            case 'integer':
            case 'rowid':
            case 'smallint':
            case 'tinyint':
            case 'tinyint(1)':
                return [
                    'type'   => Type::INTEGER,
                    'format' => Format::INT32,
                ];

            case 'float':
                return [
                    'type'   => Type::NUMBER,
                    'format' => Format::FLOAT,
                ];

            case 'decimal':
            case 'double precision':
            case 'double':
            case 'number':
            case 'numeric':
                return [
                    'type'   => Type::NUMBER,
                    'format' => Format::DOUBLE,
                ];

            case 'bool':
            case 'boolean':
                return ['type' => Type::BOOLEAN];

            default:
                return ['type' => Type::STRING];
        }
    }
}
