<?php

namespace oleglfed\LaravelDDD\Commands;

use File;
use Illuminate\Console\Command;
use oleglfed\LaravelDDD\Traits\GeneratorTrait;

class GenerateDomain extends Command
{
    use GeneratorTrait;

    public $domainPath;
    public $infrastructurePath;
    public $testPath;

    public $infrastructureContract =
        __DIR__.'/../../resources/Infrastructures/Template/Contracts/EloquentTemplateRepositoryInterface';
    public $infrastructureClass = __DIR__.'/../../resources/Infrastructures/Template/EloquentTemplateRepository';

    public $domainInterfaceContract = __DIR__.'/../../resources/Domains/Template/Contracts/TemplateInterface';
    public $domainRepositoryContract = __DIR__
                                        .'/../../resources/Domains/Template/Contracts/TemplateRepositoryInterface';
    public $domainServiceContract = __DIR__.'/../../resources/Domains/Template/Contracts/TemplateServiceInterface';

    public $domainEloquent = __DIR__.'/../../resources/Domains/Template/TemplateEloquent';
    public $domainRepository = __DIR__.'/../../resources/Domains/Template/TemplateRepository';
    public $domainService = __DIR__.'/../../resources/Domains/Template/TemplateService';

    public $domainEloquentTest = __DIR__.'/../../resources/Tests/TemplateInterfaceTest';
    public $domainRepositoryTest = __DIR__.'/../../resources/Tests/TemplateRepositoryTest';
    public $domainServiceTest = __DIR__.'/../../resources/Tests/TemplateServiceTest';

    public $abstractEloquentRepositoryInterface =
        __DIR__.'/../../resources/Infrastructures/Contracts/EloquentRepositoryInterface';
    public $abstractEloquentRepository = __DIR__.'/../../resources/Infrastructures/EloquentRepositoryAbstract';
    public $abstractEloquent = __DIR__.'/../../resources/Infrastructures/EloquentAbstract';
    public $abstractRepository = __DIR__.'/../../resources/Domains/RepositoryAbstract';
    public $abstractService = __DIR__.'/../../resources/Domains/ServiceAbstract';
    public $abstractBaseEntityInterface = __DIR__.'/../../resources/Domains/Contracts/BaseEntityInterface';

    public $binding = __DIR__.'/../../resources/bind';

    /**
     * Domain Name.
     *
     * @var
     */
    public $name;

    /**
     * Directory Name.
     *
     * @var
     */
    public $directory;

    /**
     * Table.
     *
     * @var
     */
    public $table;

    /**
     * Table fields.
     *
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
                        {--test-path=Tests/Domains : Tests path}
                        {--without-abstracts : Allows to skip copying abstract classes}
                        {--forced : Allows to override existing domains}
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
        $this->setTestPath(base_path($this->option('test-path')));

        $this->name = ucfirst($this->argument('name'));
        $this->table = $this->option('table');
        $this->directory = $this->option('directory');

        //If table is not defined, assumed table name is equal to domain
        if (!$this->getTable()) {
            $this->table = $this->getProvidedName();
        }

        //If directory is not set we use domain name as directory
        if (!$this->getDirectory()) {
            $this->directory = $this->getProvidedName();
        }

        if (!$this->option('forced') and
            file_exists($this->getDomainPath("/{$this->name}/Contracts/{$this->name}Interface.php"))) {
            return $this->comment('Domain already exists. Use --forced to override');
        }

        //Get DB fields
        $this->fields = $this->parseDbTable($this->table);

        //Preparing directories
        $this->createDirectories();

        //Copping Infrastructures
        $this->copyInfrastructure($this->name);

        //Copping Domains
        $this->copyDomain($this->name);

        //Copping Tests
        $this->copyTests($this->name);

        //Copy binding
        $this->copyBinding($this->name);

        $this->info("Domain $this->name is created");
    }

    /**
     * Prepare directories.
     *
     * @return bool
     */
    public function createDirectories()
    {
        if (!file_exists($this->getDomainPath())) {
            File::makeDirectory($this->getDomainPath());
        }

        if (!file_exists($this->getInfrastructurePath())) {
            File::makeDirectory($this->getInfrastructurePath());
        }

        if (!file_exists($this->getTestPath())) {
            File::makeDirectory($this->getTestPath());
        }

        if (!$this->option('without-abstracts')) {
            $this->copyAbstractClasses();
        }

        $this->setDomainPath($this->getDomainPath(DIRECTORY_SEPARATOR.$this->getDirectory()));
        $this->setInfrastructurePath($this->getInfrastructurePath(DIRECTORY_SEPARATOR.$this->getDirectory()));
        $this->setTestPath($this->getTestPath(DIRECTORY_SEPARATOR.$this->getDirectory()));

        if (!file_exists($this->getDomainPath())) {
            File::makeDirectory($this->getDomainPath());
            File::makeDirectory($this->getDomainPath('/Contracts'));
        }

        if (!file_exists($this->getInfrastructurePath())) {
            File::makeDirectory($this->getInfrastructurePath());
            File::makeDirectory($this->getInfrastructurePath('/Contracts'));
        }

        if (!file_exists($this->getTestPath())) {
            File::makeDirectory($this->getTestPath());
        }

        return true;
    }

    /**
     * @return bool
     */
    public function copyAbstractClasses()
    {
        if (!file_exists("$this->infrastructurePath/Contracts/EloquentRepositoryInterface.php")) {
            File::makeDirectory("$this->infrastructurePath/Contracts");

            File::put(
                "$this->infrastructurePath/Contracts/EloquentRepositoryInterface.php",
                $this->prepare(File::get($this->abstractEloquentRepositoryInterface))
            );
        }

        if (!file_exists("$this->infrastructurePath/EloquentRepositoryAbstract.php")) {
            File::put(
                "$this->infrastructurePath/EloquentRepositoryAbstract.php",
                $this->prepare(File::get($this->abstractEloquentRepository))
            );
        }

        if (!file_exists("$this->infrastructurePath/EloquentAbstract.php")) {
            File::put(
                "$this->infrastructurePath/EloquentAbstract.php",
                $this->prepare(File::get($this->abstractEloquent))
            );
        }

        if (!file_exists("$this->domainPath/RepositoryAbstract.php")) {
            File::makeDirectory("$this->domainPath/Contracts");

            File::put(
                "$this->domainPath/Contracts/BaseEntityInterface.php",
                $this->prepare(File::get($this->abstractBaseEntityInterface))
            );
        }

        if (!file_exists("$this->domainPath/RepositoryAbstract.php")) {
            File::put(
                "$this->domainPath/RepositoryAbstract.php",
                $this->prepare(File::get($this->abstractRepository))
            );
        }

        if (!file_exists("$this->domainPath/ServiceAbstract.php")) {
            File::put(
                "$this->domainPath/ServiceAbstract.php",
                $this->prepare(File::get($this->abstractService))
            );
        }

        return true;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function copyInfrastructure($name)
    {
        File::put(
            $this->getInfrastructurePath("/Contracts/Eloquent{$name}RepositoryInterface.php"),
            $this->prepare(File::get($this->infrastructureContract))
        );

        File::put(
            $this->getInfrastructurePath("/Eloquent{$name}Repository.php"),
            $this->prepare(File::get($this->infrastructureClass))
        );

        return true;
    }

    /**
     * Copy domains.
     *
     * @param $name
     *
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

    /**
     * Copy domains.
     *
     * @param $name
     *
     * @return bool
     */
    public function copyTests($name)
    {
        File::put($this->getTestPath("/{$name}InterfaceTest.php"), $this->prepare(File::get($this->domainEloquentTest)));
        File::put($this->getTestPath("/{$name}RepositoryTest.php"), $this->prepare(File::get($this->domainRepositoryTest)));
        File::put($this->getTestPath("/{$name}ServiceTest.php"), $this->prepare(File::get($this->domainServiceTest)));

        return true;
    }

    public function copyBinding($name)
    {
        $name = strtolower($name);
        if (!file_exists(config_path('domains'))) {
            File::makeDirectory(config_path('domains'));
        }

        File::put(config_path("domains/$name-binding.php"), $this->prepare(File::get($this->binding)));

        return true;
    }
}
