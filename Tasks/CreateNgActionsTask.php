<?php

namespace App\Containers\Crud\Tasks;

use Illuminate\Http\Request;
use App\Containers\Crud\Traits\DataGenerator;
use App\Containers\Crud\Traits\AngularFolderNamesResolver;

/**
 * CreateNgActionsTask Class.
 *
 * @author Johan Alvarez <llstarscreamll@hotmail.com>
 */
class CreateNgActionsTask
{
    use DataGenerator, AngularFolderNamesResolver;

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
     * Create new CreateNgActionsTask instance.
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
     * @return bool
     */
    public function run()
    {
        $actionFile = $this->slugEntityName();
        $actionFile = $this->actionsDir()."/$actionFile.actions.ts";
        $template = $this->templatesDir().'.Angular2/actions/actions';

        $content = view($template, [
            'gen' => $this,
            'fields' => $this->parseFields($this->request)
        ]);

        file_put_contents($actionFile, $content) === false
            ? session()->push('error', "Error creating Angular Actions file")
            : session()->push('success', "Angular Actions creation success");

        return true;
    }
}
