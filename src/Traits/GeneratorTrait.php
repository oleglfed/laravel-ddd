<?php

namespace oleglfed\LaravelDDD\Traits;

use Illuminate\Support\Facades\Schema;

trait GeneratorTrait
{
    /**
     * @param $fields
     * @param bool $isInterface
     * @return null|string
     */
    public function getSettersGetters($fields, $isInterface = false)
    {
        $settersGetters = null;
        foreach ($fields as $field) {
            $getter = 'get' . studly_case($field);
            $setter = 'set' . studly_case($field);
            $settersGetters .= $isInterface ? "\n
    /**
     * Get $field.
     *
     * @return mixed
     */
    public function $getter();
    
    /**
     * Set $field.
     *
     * @param $$field
     *
     * @return mixed
     */
    public function $setter($$field);"

                : "\n
    /**
     * {@inheritdoc}
     */
    public function $getter()
    {
        return \$this->$field;
    }
    
    /**
     * {@inheritdoc}
     */
    public function $setter($$field)
    {
        \$this->$field = $$field;
        return \$this;
    }";
        }

        return $settersGetters;
    }

    /**
     * @param $fields
     * @return null|string
     */
    public function getRepositoryPayloads($fields)
    {
        $payloads = null;
        foreach ($fields as $field) {
            $getter = 'get' . studly_case($field);
            $payloads .= "'$field' => $$this->name->$getter(),\n            ";
        }

        return $payloads;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function parseDbTable()
    {
        try {
            if (Schema::hasTable($this->getTable())) {
                $fields = collect(Schema::getColumnListing($this->getTable()))->diff([
                    'id',
                    'ID',
                    'Id',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]);

                return $fields;
            }

            return collect([]);
        } catch (\Exception $e) {
            throw new \Exception("Database is not connected");
        }
    }

    /**
     * @param $fileContent
     * @return mixed
     */
    public function prepare($fileContent)
    {
        $replacings = [
            '{name}',
            '{namespace}',
            '{table}',
            '{getters}',
            '{interfaceGetters}',
            '{fillable}',
            '{repository}',
        ];

        $replacements = [
            $this->getName(),
            $this->getAppNamespace(),
            $this->getTable(),
            $this->getSettersGetters($this->fields),
            $this->getSettersGetters($this->fields, true),
            $this->getFillable($this->fields),
            $this->getRepositoryPayloads($this->fields)
        ];

        return str_replace($replacings, $replacements, $fileContent);
    }

    /**
     * @param $fields
     * @return string
     */
    public function getFillable($fields)
    {
        return "'" . implode("', '", $fields->toArray()) . "'";
    }

    /**
     * @param $path
     * @return string
     */
    public function getDomainPath($path = null)
    {
        return $this->domainPath . $path;
    }

    /**
     * @param $path
     * @return string
     */
    public function getInfrastructurePath($path = null)
    {
        return $this->infrastructurePath . $path;
    }

    /**
     * @param $path
     * @return string
     */
    public function setDomainPath($path)
    {
        return $this->domainPath = $path;
    }

    /**
     * @param $path
     * @return string
     */
    public function setInfrastructurePath($path)
    {
        return $this->infrastructurePath = $path;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Returns App namespace
     * @return int|string
     */
    protected function getAppNamespace()
    {
        $composer = json_decode(file_get_contents(base_path().'/composer.json'), true);

        foreach ((array) data_get($composer, 'autoload.psr-4') as $namespace => $path) {
            foreach ((array) $path as $pathChoice) {
                if (realpath(app_path()) == realpath(base_path().'/'.$pathChoice)) {
                    return $namespace;
                }
            }
        }

        throw new \RuntimeException("Unable to detect application namespace.");
    }
}
