<?php

namespace oleglfed\LaravelDDD\Commands;

use Illuminate\Console\Command;
use File;
use oleglfed\LaravelDDD\Traits\GeneratorTrait;

class GenerateDomain extends Command
{
    use GeneratorTrait;

    public $domainPath;
    public $infrastructurePath;

    public $infrastructureContract =
        __DIR__ . '/../../resources/Infrastructures/Template/Contracts/EloquentTemplateRepositoryInterface';
    public $infrastructureClass = __DIR__ . '/../../resources/Infrastructures/Template/EloquentTemplateRepository';

    public $domainInterfaceContract  = __DIR__ . '/../../resources/Domains/Template/Contracts/TemplateInterface';
    public $domainRepositoryContract = __DIR__
                                        . '/../../resources/Domains/Template/Contracts/TemplateRepositoryInterface';
    public $domainServiceContract    = __DIR__ . '/../../resources/Domains/Template/Contracts/TemplateServiceInterface';

    public $domainEloquent   = __DIR__ . '/../../resources/Domains/Template/TemplateEloquent';
    public $domainRepository = __DIR__ . '/../../resources/Domains/Template/TemplateRepository';
    public $domainService    = __DIR__ . '/../../resources/Domains/Template/TemplateService';

    /**
     * Domain Name
     * @var
     */
    public $name;

    /**
     * Directory Name
     * @var
     */
    public $directory;

    /**
     * Table
     * @var
     */
    public $table;

    /**
     * Table fields
     * @var
     */
    public $fields;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:domain 
                        {name : Domain name}
                        {--table= : Table name to be used for domain}
                        {--directory= : Directory name of Domain}
                        {--domain-path=Domains : Domain directory. Default app/Domains}
                        {--infrastructure-path=Infrastructures : Infrastructure directory. Default app/Infrastructures}
                        ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Domain';

    /**
     * GenerateDomain constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->setDomainPath(app_path($this->option('domain-path')));
        $this->setInfrastructurePath(app_path($this->option('infrastructure-path')));

        $this->name = ucfirst($this->argument('name'));
        $this->table = $this->option('table');
        $this->directory = $this->option('directory');

        //If table is not defined, assumed table name is equal to domain
        if (!$this->table) {
            $this->table = $this->name;
        }

        //If directory is not set we use domain name as directory
        if (!$this->directory) {
            $this->directory = $this->name;
        }

        //Get DB fields
        $this->fields = $this->parseDbTable($this->table);

        //Preparing directories
        $this->createDirectories();

        //Copping Infrastructures
        $this->copyInfrastructure($this->name);

        //Copping Domains
        $this->copyDomain($this->name);

        echo "\033[32m Domain $this->name is created \033[0m \n";
    }

    /**
     * Prepare directories
     * @return bool
     */
    public function createDirectories()
    {
        if (!File::exists($this->getDomainPath())) {
            File::makeDirectory($this->getDomainPath());
        }

        if (!File::exists($this->getInfrastructurePath())) {
            File::makeDirectory($this->getInfrastructurePath());
        }

        $this->setDomainPath($this->getDomainPath(DIRECTORY_SEPARATOR . $this->getDirectory()));
        $this->setInfrastructurePath($this->getInfrastructurePath(DIRECTORY_SEPARATOR . $this->getDirectory()));

        if (!File::exists($this->getDomainPath())) {
            File::makeDirectory($this->getDomainPath());
            File::makeDirectory($this->getDomainPath('/Contracts'));
        }

        if (!File::exists($this->getInfrastructurePath())) {
            File::makeDirectory($this->getInfrastructurePath());
            File::makeDirectory($this->getInfrastructurePath('/Contracts'));
        }

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public function copyInfrastructure($name)
    {
        File::put(
            $this->getInfrastructurePath("/Contracts/Eloquent{$name}RepositoryInterface.php"),
            $this->prepare(File::get($this->infrastructureContract))
        );

        File::put(
            $this->getInfrastructurePath("/Eloquent{$name}RepositoryInterface.php"),
            $this->prepare(File::get($this->infrastructureClass))
        );

        return true;
    }

    /**
     * Copy domains
     * @param $name
     * @return bool
     */
    public function copyDomain($name)
    {
        File::put(
            $this->getDomainPath("/Contracts/{$name}Interface.php"),
            $this->prepare(File::get($this->domainInterfaceContract))
        );

        File::put(
            $this->getDomainPath("/Contracts/{$name}RepositoryInterface.php"),
            $this->prepare(File::get($this->domainRepositoryContract))
        );

        File::put(
            $this->getDomainPath("/Contracts/{$name}ServiceInterface.php"),
            $this->prepare(File::get($this->domainServiceContract))
        );

        File::put($this->getDomainPath("/{$name}Eloquent.php"), $this->prepare(File::get($this->domainEloquent)));
        File::put($this->getDomainPath("/{$name}Repository.php"), $this->prepare(File::get($this->domainRepository)));
        File::put($this->getDomainPath("/{$name}Service.php"), $this->prepare(File::get($this->domainService)));

        return true;
    }
}
