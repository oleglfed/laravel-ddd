<?php

namespace oleglfed\LaravelDDD\Commands;

use Illuminate\Console\Command;
use File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GenerateDomain extends Command
{

    public $domainFolder = 'Domains';
    public $infrastructureFolder = 'Infrastructures';
    public $infrastructureContract = 'domains/Infrastructures/Template/Contracts/EloquentTemplateRepositoryInterface';
    public $infrastructureClass = 'domains/Infrastructures/Template/EloquentTemplateRepository';

    public $domainInterfaceContract = 'domains/Domains/Template/Contracts/TemplateInterface';
    public $domainEloquent = 'domains/Domains/Template/TemplateEloquent';
    public $domainRepositoryContract = 'domains/Domains/Template/Contracts/TemplateRepositoryInterface';
    public $domainRepository = 'domains/Domains/Template/TemplateRepository';
    public $domainServiceContract = 'domains/Domains/Template/Contracts/TemplateServiceInterface';
    public $domainService = 'domains/Domains/Template/TemplateService';
    public $name;
    public $table;
    public $fields;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:domain {name : Domain name}
                            {--table= : Table name to be used fo domain}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Domain (DDD)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->domainFolder = app_path($this->domainFolder);
        $this->infrastructureFolder = app_path($this->infrastructureFolder);

        $this->infrastructureClass = resource_path($this->infrastructureClass);
        $this->infrastructureContract = resource_path($this->infrastructureContract);
        $this->domainInterfaceContract = resource_path($this->domainInterfaceContract);
        $this->domainEloquent = resource_path($this->domainEloquent);
        $this->domainRepository = resource_path($this->domainRepository);
        $this->domainRepositoryContract = resource_path($this->domainRepositoryContract);
        $this->domainServiceContract = resource_path($this->domainService);
        $this->domainService = resource_path($this->domainService);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->name = ucfirst($this->argument('name'));
        $this->table = $this->option('table');

        if (!$this->table) {
            $this->table = $this->name;
        }

        $this->fields = $this->parseDbTable($this->table);

        $this->checkFoldersExist($this->name);

        $this->copyInfrastructure($this->name);
        $this->copyDomain($this->name);
    }

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

    public function getRepositoryPayloads($fields)
    {
        $payloads = null;
        foreach ($fields as $field) {
            $getter = 'get' . studly_case($field);
            $payloads .= "'$field' => $$this->name->$getter(),\n            ";
        }

        return $payloads;
    }


    public function parseDbTable()
    {
        try {
            if (Schema::hasTable($this->table)) {
                $fields = collect(Schema::getColumnListing($this->table))->diff([
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


    public function checkFoldersExist($name)
    {
        if (!File::exists($this->domainFolder)) {
            File::makeDirectory($this->domainFolder);
        }

        if (!File::exists($this->infrastructureFolder)) {
            File::makeDirectory($this->infrastructureFolder);
        }

        $this->domainFolder .= "/$name";
        $this->infrastructureFolder .= "/$name";

        if (!File::exists($this->domainFolder)) {
            File::makeDirectory($this->domainFolder);
            File::makeDirectory($this->domainFolder . '/Contracts');
        }

        if (!File::exists($this->infrastructureFolder)) {
            File::makeDirectory($this->infrastructureFolder);
            File::makeDirectory($this->infrastructureFolder . '/Contracts');
        }
    }


    public function copyInfrastructure($name)
    {
        File::put($this->infrastructurePath("/Contracts/Eloquent{$name}RepositoryInterface.php"), $this->prepare(File::get($this->infrastructureContract)));
        File::put($this->infrastructurePath("/Eloquent{$name}RepositoryInterface.php"), $this->prepare(File::get($this->infrastructureClass)));
    }

    public function copyDomain($name)
    {
        File::put($this->domainPath("/Contracts/{$name}Interface.php"), $this->prepare(File::get($this->domainInterfaceContract)));
        File::put($this->domainPath("/{$name}Eloquent.php"), $this->prepare(File::get($this->domainEloquent)));

        File::put($this->domainPath("/Contracts/{$name}RepositoryInterface.php"), $this->prepare(File::get($this->domainRepositoryContract)));
        File::put($this->domainPath("/{$name}Repository.php"), $this->prepare(File::get($this->domainRepository)));

        File::put($this->domainPath("/Contracts/{$name}ServiceInterface.php"), $this->prepare(File::get($this->domainServiceContract)));
        File::put($this->domainPath("/{$name}Service.php"), $this->prepare(File::get($this->domainService)));
    }












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
            $this->name,
            $this->getAppNamespace(),
            $this->table,
            $this->getSettersGetters($this->fields),
            $this->getSettersGetters($this->fields, true),
            "'" . implode("', '", $this->fields->toArray()) . "'",
            $this->getRepositoryPayloads($this->fields)
        ];

        return str_replace($replacings, $replacements, $fileContent);
    }

    public function domainPath($path)
    {
        return $this->domainFolder . $path;
    }


    public function infrastructurePath($path)
    {
        return $this->infrastructureFolder . $path;
    }


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
