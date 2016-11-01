<?php
/* @var $gen llstarscreamll\CrudGenerator\Providers\TestsGenerator */
/* @var $fields [] */
/* @var $test [] */
/* @var $request Request */
?>
<?='<?php'?>


<?= $gen->getClassCopyRightDocBlock() ?>


namespace {{$gen->studlyCasePlural()}};

use FunctionalTester;
use {{$modelNamespace = config('modules.CrudGenerator.config.parent-app-namespace')."\Models\\".$gen->modelClassName()}};
use {{config('modules.CrudGenerator.config.role-model-namespace')}};
use {{config('modules.CrudGenerator.config.permission-model-namespace')}};
use Page\Functional\{{$gen->studlyCasePlural()}}\Index as Page;
use Page\Functional\{{$gen->studlyCasePlural()}}\Destroy as DestroyPage;
use Page\Functional\{{$gen->studlyCasePlural()}}\Create as CreatePage;
use Page\Functional\{{$gen->studlyCasePlural()}}\Edit as EditPage;

class {{$test}}Cest
{
    /**
     * El id del registro de prueba.
     *
     * @var int
     */
    protected ${{$gen->modelVariableName()}}Id;

    /**
     * Las acciones a realizar antes de cada test.
     *
     * @param  FunctionalTester $I
     */
    public function _before(FunctionalTester $I)
    {
        new Page($I);
        $I->amLoggedAs(Page::$adminUser);

        $permissions = [
            '{{$gen->route()}}.create',
            '{{$gen->route()}}.edit',
            '{{$gen->route()}}.destroy',
@if($gen->hasDeletedAtColumn($fields))
            '{{$gen->route()}}.restore',
@endif
        ];

        // quitamos permisos de edición a los roles
        $permission = Permission::whereIn('{{config('modules.CrudGenerator.config.permission-slug-field-name')}}', $permissions)->get(['id'])->pluck('id')->toArray();
        Role::all()->each(function ($item) use ($permission) {
            $item->permissions()->detach($permission);
        });

        // creamos registro de prueba
        $this->{{$gen->modelVariableName()}}Id = Page::have{{$gen->modelClassName()}}($I);
    }

    /**
     * Prueba que las restricciones con los permisos de creación funcionen
     * correctamente.
     *
     * @param  FunctionalTester $I
@if(!empty($request->get('is_part_of_package')))
     * @group  {{$request->get('is_part_of_package')}}
     */ 
@else
     */
@endif
    public function createPermissions(FunctionalTester $I)
    {
        $I->wantTo('probar permisos de creación en módulo '.Page::$moduleName);

        // no debo ver link de acceso a página de creación en Index
        $I->amOnPage(Page::$moduleURL);
        $I->dontSee(CreatePage::$createBtn, CreatePage::$createBtnElem);

        // si intento acceder a la página soy redirigido al home de la app
        $I->amOnPage(Page::route('/create'));
        $I->seeCurrentUrlEquals(Page::$homeUrl);
        $I->see(Page::$badPermissionsMsg, Page::$badPermissionsMsgElem);
    }

    /**
     * Prueba que las restricciones con los permisos de edición funcionen
     * correctamente.
     *
     * @param  FunctionalTester $I
@if(!empty($request->get('is_part_of_package')))
     * @group  {{$request->get('is_part_of_package')}}
     */ 
@else
     */
@endif
    public function editPermissions(FunctionalTester $I)
    {
        $I->wantTo('probar permisos de edición en módulo '.Page::$moduleName);

        // no debo ver link de acceso a página de edición en Index
        $I->amOnPage(Page::$moduleURL);
        $I->dontSee(EditPage::$linkToEdit, 'tbody tr td '.EditPage::$linkToEditElem);

        // el la página de detalles del registro no debo ver el link a página de
        // edición
        $I->amOnPage(Page::route("/$this->{{$gen->modelVariableName()}}Id"));
        $I->dontSee(EditPage::$linkToEdit, '.form-group '.EditPage::$linkToEditElem);

        // si intento acceder a la página de edición de un registro soy
        // redirigido al home de la app
        $I->amOnPage(Page::route("/$this->{{$gen->modelVariableName()}}Id/edit"));
        $I->seeCurrentUrlEquals(Page::$homeUrl);
        $I->see(Page::$badPermissionsMsg, Page::$badPermissionsMsgElem);
    }

    /**
     * Prueba que las restricciones con los permisos de {{ strtolower($gen->getDestroyBtnTxt()) }}
     * funcionen correctamente.
     *
     * @param  FunctionalTester $I
@if(!empty($request->get('is_part_of_package')))
     * @group  {{$request->get('is_part_of_package')}}
     */ 
@else
     */
@endif
    public function {{ $gen->getDestroyVariableName() }}Permissions(FunctionalTester $I)
    {
        $I->wantTo('probar permisos de {{ strtolower($gen->getDestroyBtnTxt()) }} en módulo '.Page::$moduleName);

        // no debo ver link de acceso a página de edición en Index
        $I->amOnPage(Page::$moduleURL);
        $I->dontSee(DestroyPage::${{ $gen->getDestroyVariableName() }}Btn, DestroyPage::${{ $gen->getDestroyVariableName() }}BtnElem);
        $I->dontSee(DestroyPage::${{ $gen->getDestroyVariableName() }}ManyBtn, DestroyPage::${{ $gen->getDestroyVariableName() }}ManyBtnElem);
        // en página de detalles del registro no debo ver botón "Mover a Papelera"
        $I->amOnPage(Page::route("/$this->{{$gen->modelVariableName()}}Id"));
        $I->dontSee(DestroyPage::${{ $gen->getDestroyVariableName() }}Btn, DestroyPage::${{ $gen->getDestroyVariableName() }}BtnElem);
    }

@if($gen->hasDeletedAtColumn($fields))
    /**
     * Prueba que las restricciones con los permisos de restauración de
     * registros en papelera funcionen correctamente.
     *
     * @param  FunctionalTester $I
@if(!empty($request->get('is_part_of_package')))
     * @group  {{$request->get('is_part_of_package')}}
     */ 
@else
     */
@endif
    public function restorePermissions(FunctionalTester $I)
    {
        $I->wantTo('probar permisos de restauración en módulo '.Page::$moduleName);

        // eliminamos el registro de prueba
        {{$gen->modelClassName()}}::destroy($this->{{$gen->modelVariableName()}}Id);

        // no debo ver link de acceso a página de edición en Index
        $I->amOnPage(Page::$moduleURL.'?trashed_records=withTrashed');
        $I->dontSee(Page::$restoreBtn, Page::$restoreBtnElem);
        $I->dontSee(Page::$restoreManyBtn, Page::$restoreManyBtnElem);
    }
@endif
}