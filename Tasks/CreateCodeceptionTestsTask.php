<?php

namespace App\Containers\Crud\Tasks;

use Illuminate\Http\Request;
use App\Containers\Crud\Traits\FolderNamesResolver;
use App\Containers\Crud\Traits\DataGenerator;

/**
 * CreateCodeceptionTestsTask Class.
 *
 * @author Johan Alvarez <llstarscreamll@hotmail.com>
 */
class CreateCodeceptionTestsTask
{
    use FolderNamesResolver, DataGenerator;

    /**
     * Container name to generate.
     *
     * @var string
     */
    public $container;

    /**
     * Container entity to generate (database table name).
     *
     * @var string
     */
    public $tableName;

    /**
     * The tests files to generate.
     *
     * @var array
     */
    public $files = [
        'Create',
        'Get',
        'Update',
        'Delete',
        'Restore',
        'ListAndSearch',
        'FormModel',
        'FormData',
    ];

    private $codecept = "/home/vagrant/.composer/vendor/bin/codecept";

    /**
     * Create new CreateCodeceptionTestsTask instance.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->container = studly_case($request->get('is_part_of_package'));
        $this->tableName = $this->request->get('table_name');
    }

    /**
     * @param string $container Contaner name
     *
     * @return bool
     */
    public function run()
    {
        $this->bootstrapCodeception();

        $this->createCodeceptApiSuite();

        $this->configureCodeceptSuite('api', $this->getApiSuiteModules());
        $this->configureCodeceptSuite('functional', $this->getFunctionalSuiteModules());
        $this->generateUserHelper();

        // builds Codeception suites
        exec("cd {$this->containerFolder()} && {$this->codecept} build");

        $this->generateApiTests();

        return true;
    }

    /**
     * Bootstraps Codeception on container.
     */
    public function bootstrapCodeception()
    {
        if (!file_exists($this->containerFolder().'/codeception.yml')) {
            exec("cd {$this->containerFolder()} && {$this->codecept} bootstrap --namespace=".$this->containerName()." 2>&1", $output);
            // dd($output); // to debug!!
        } else {
            session()->push('warning', 'Codeception tests already bootstraped!!');
        }
    }

    /**
     * Create Codeception suite for API tests.
     */
    public function createCodeceptApiSuite()
    {
        if (!file_exists($this->apiTestsFolder())) {
            exec("cd {$this->containerFolder()} && {$this->codecept} generate:suite api");
        } else {
            session()->push('warning', 'Codeception tests already bootstraped!!');
        }
    }

    /**
     * Adds Laravel module for Codeception suite.
     *
     * @param string $suite   The codeception suite name
     * @param string $modules The modules to enable on suite
     */
    private function configureCodeceptSuite(string $suite, string $modules)
    {
        $suiteContent = file_get_contents($this->containerFolder()."/tests/$suite.suite.yml");

        if (strpos($suiteContent, $modules) === false) {
            $suiteContent .= $modules;
            file_put_contents($this->containerFolder()."/tests/$suite.suite.yml", $suiteContent);
        } else {
            session()->push('warning', "Codeception setup for $suite.suite.yml already set!!");
        }
    }

    /**
     * [generateUserHelper description]
     * @return [type] [description]
     */
    private function generateUserHelper()
    {
        $helperFile = $this->testsFolder()."/_support/Helper/UserHelper.php";
        $template = $this->templatesDir().'.Porto/tests/_support/Helper/UserHelper';

        $content = view($template, [
            'gen' => $this,
            'fields' => $this->parseFields($this->request)
            ]);

        file_put_contents($helperFile, $content) === false
            ? session()->push('error', "Error creating UserHelper test file")
            : session()->push('success', "UserHelper test file creation success");
    }

    /**
     * Returns the modules to enable on Codeception API suite.
     *
     * @return string
     */
    private function getApiSuiteModules()
    {
        return "\n".
            "        - \\{$this->containerName()}\Helper\UserHelper\n".
            "        - Asserts\n".
            "        - REST:\n".
            "            depends: Laravel5\n".
            "        - Laravel5:\n".
            "            environment_file: .env.testing\n".
            "            root: ../../../\n".
            '            run_database_migrations: true';
    }

    /**
     * Returns the modules to enable on Codeception functional suite.
     *
     * @return string
     */
    private function getFunctionalSuiteModules()
    {
        return "\n".
            "        - \\{$this->containerName()}\Helper\UserHelper\n".
            "        - Asserts\n".
            "        - Laravel5:\n".
            "            environment_file: .env.testing\n".
            "            root: ../../../\n".
            '            run_database_migrations: true';
    }

    private function generateApiTests()
    {
        $this->createEntityApiTestsFolder();

        foreach ($this->files as $file) {
            $plural = ($file == 'ListAndSearch') ? true : false;
            $atStart = in_array($file, ['FormData', 'FormModel']) ? true : false;

            $testFile = $this->apiTestsFolder()."/{$this->entityName()}/".$this->apiTestFile($file, $plural, $atStart);
            $template = $this->templatesDir().'.Porto/tests/api/'.$file;

            $content = view($template, [
                'gen' => $this,
                'fields' => $this->parseFields($this->request)
            ]);

            file_put_contents($testFile, $content) === false
                ? session()->push('error', "Error creating $file api test file")
                : session()->push('success', "$file api test creation success");
        }
    }

    /**
     * Creates the API entity tests folder.
     */
    private function createEntityApiTestsFolder()
    {
        if (!file_exists($this->apiTestsFolder().'/'.$this->entityName())) {
            mkdir($this->apiTestsFolder().'/'.$this->entityName());
        }
    }
}
