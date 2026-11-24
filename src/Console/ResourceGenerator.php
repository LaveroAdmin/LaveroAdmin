<?php

namespace LaveroAdmin\Admin\Console;

use Illuminate\Database\Eloquent\Model;

class ResourceGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $formats = [
        'form_field'  => "\$form->%s('%s', __('%s'))",
        'show_field'  => "\$show->field('%s', __('%s'))",
        'grid_column' => "\$grid->column('%s', __('%s'))",
    ];

    /**
     * @var array
     */
    private $doctrineTypeMapping = [
        'string' => [
            'enum', 'geometry', 'geometrycollection', 'linestring',
            'polygon', 'multilinestring', 'multipoint', 'multipolygon',
            'point',
        ],
    ];

    /**
     * @var array
     */
    protected $fieldTypeMapping = [
        'ip'          => 'ip',
        'email'       => 'email|mail',
        'password'    => 'password|pwd',
        'url'         => 'url|link|src|href',
        'phonenumber' => 'mobile|phone',
        'color'       => 'color|rgb',
        'image'       => 'image|img|avatar|pic|picture|cover',
        'file'        => 'file|attachment',
    ];

    /**
     * ResourceGenerator constructor.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $this->getModel($model);
    }

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    protected function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (!class_exists($model) || !is_string($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model [$model] !");
        }

        return new $model();
    }

    /**
     * @return string
     */
    public function generateForm()
    {
        $reservedColumns = $this->getReservedColumns();

        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $type = $column->getType()->getName();
            $default = $column->getDefault();

            $defaultValue = '';

            // set column fieldType and defaultValue
            switch ($type) {
                case 'boolean':
                case 'bool':
                    $fieldType = 'switch';
                    break;
                case 'json':
                case 'array':
                case 'object':
                    $fieldType = 'text';
                    break;
                case 'string':
                    $fieldType = 'text';
                    foreach ($this->fieldTypeMapping as $type => $regex) {
                        if (preg_match("/^($regex)$/i", $name) !== 0) {
                            $fieldType = $type;
                            break;
                        }
                    }
                    $defaultValue = "'{$default}'";
                    break;
                case 'integer':
                case 'bigint':
                case 'smallint':
                case 'timestamp':
                    $fieldType = 'number';
                    break;
                case 'decimal':
                case 'float':
                case 'real':
                    $fieldType = 'decimal';
                    break;
                case 'datetime':
                    $fieldType = 'datetime';
                    $defaultValue = "date('Y-m-d H:i:s')";
                    break;
                case 'date':
                    $fieldType = 'date';
                    $defaultValue = "date('Y-m-d')";
                    break;
                case 'time':
                    $fieldType = 'time';
                    $defaultValue = "date('H:i:s')";
                    break;
                case 'text':
                case 'blob':
                    $fieldType = 'textarea';
                    break;
                default:
                    $fieldType = 'text';
                    $defaultValue = "'{$default}'";
            }

            $defaultValue = $defaultValue ?: $default;

            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['form_field'], $fieldType, $name, $label);

            if (trim($defaultValue, "'\"")) {
                $output .= "->default({$defaultValue})";
            }

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateShow()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();

            // set column label
            $label = $this->formatLabel($name);

            $output .= sprintf($this->formats['show_field'], $name, $label);

            $output .= ";\r\n";
        }

        return $output;
    }

    public function generateGrid()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            $label = $this->formatLabel($name);
            $type = $column->getType()->getName();

            $line = sprintf($this->formats['grid_column'], $name, $label);

            if ($name === $this->model->getKeyName() || in_array($name, ['id', 'created_at', 'updated_at'])) {
                $line .= '->sortable()';
            } elseif ($type === 'boolean' || $type === 'bool') {
                $line .= '->switch()';
            } elseif (preg_match('/^(image|img|avatar|pic|picture|cover|logo)$/i', $name)) {
                $line .= "->image('', 40, 40)";
            } elseif (preg_match('/^(url|link|website)$/i', $name)) {
                $line .= '->link()';
            }

            $output .= $line . ";\r\n";
        }

        return $output;
    }

    protected function getReservedColumns()
    {
        return [
            $this->model->getKeyName(),
            $this->model->getCreatedAtColumn(),
            $this->model->getUpdatedAtColumn(),
            'deleted_at',
        ];
    }

    /**
     * Get columns of a giving model.
     *
     * @throws \Exception
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     */
    protected function getTableColumns()
    {
        $connection = $this->model->getConnection();
        $table = $connection->getTablePrefix().$this->model->getTable();

        // 1. Support native Laravel Schema::getColumns() (Laravel 11, 12, 13+)
        $schemaBuilder = $connection->getSchemaBuilder();
        if (method_exists($schemaBuilder, 'getColumns')) {
            $rawColumns = $schemaBuilder->getColumns($table);
            $columns = [];
            foreach ($rawColumns as $col) {
                $columns[] = new class($col) {
                    private $col;
                    public function __construct($col) { $this->col = (array) $col; }
                    public function getName() { return $this->col['name']; }
                    public function getDefault() { return $this->col['default'] ?? null; }
                    public function getType() {
                        $typeName = $this->col['type_name'] ?? $this->col['type'] ?? 'string';
                        if (preg_match('/^([a-z]+)/i', $typeName, $m)) {
                            $typeName = strtolower($m[1]);
                        }
                        return new class($typeName) {
                            private $t;
                            public function __construct($t) { $this->t = $t; }
                            public function getName() { return $this->t; }
                        };
                    }
                };
            }
            return $columns;
        }

        // 2. Fallback to Doctrine DBAL for Laravel <= 10
        $isDoctrineAvailable = method_exists($connection, 'isDoctrineAvailable')
            ? $connection->isDoctrineAvailable()
            : class_exists(\Doctrine\DBAL\Connection::class);

        if (!$isDoctrineAvailable) {
            throw new \Exception(
                'You need to require doctrine/dbal in your composer.json to get database columns.'
            );
        }

        /** @var \Doctrine\DBAL\Schema\MySqlSchemaManager $schema */
        $schema = method_exists($connection, 'getDoctrineSchemaManager')
            ? $connection->getDoctrineSchemaManager($table)
            : (method_exists($connection, 'getDoctrineConnection') ? $connection->getDoctrineConnection()->createSchemaManager() : null);

        if ($schema) {
            $databasePlatform = method_exists($schema, 'getDatabasePlatform')
                ? $schema->getDatabasePlatform()
                : (method_exists($connection, 'getDoctrineConnection') ? $connection->getDoctrineConnection()->getDatabasePlatform() : null);

            if ($databasePlatform && method_exists($databasePlatform, 'registerDoctrineTypeMapping')) {
                foreach ($this->doctrineTypeMapping as $doctrineType => $dbTypes) {
                    foreach ($dbTypes as $dbType) {
                        $databasePlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
                    }
                }
            }

            $database = null;
            if (strpos($table, '.') !== false) {
                list($database, $table) = explode('.', $table);
            }

            return $schema->listTableColumns($table, $database);
        }

        return [];
    }

    /**
     * Format label.
     *
     * @param string $value
     *
     * @return string
     */
    protected function formatLabel($value)
    {
        return ucfirst(str_replace(['-', '_'], ' ', $value));
    }
}
