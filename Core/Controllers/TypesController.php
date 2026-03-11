<?php

namespace Core\Controllers;

use Core\Repositories\TypeRepository;

class TypesController extends NamedEntityCrudController
{
    public function __construct(
        private TypeRepository $repository
    ) {
    }

    protected function config(): array
    {
        return [
            'field' => 'type_name',
            'collectionKey' => 'types',
            'indexView' => 'types/index.view.php',
            'createView' => 'types/create.view.php',
            'showView' => 'types/type_show.view.php',
            'editView' => 'types/type_edit.view.php',
            'destroyView' => 'types/type_destroy.view.php',
            'indexTitle' => 'Transaction Types',
            'indexHeading' => 'Transaction Types',
            'createTitle' => 'Create Type',
            'createHeading' => 'Create a New Type',
            'showTitle' => 'Transaction Type',
            'showHeading' => 'Type Name',
            'editTitle' => 'Transaction Type',
            'editHeading' => 'Edit Type Name',
            'destroyTitle' => 'Transaction Type',
            'destroyHeading' => 'Delete Transaction Type',
            'listPath' => '/types/type',
            'showPath' => '/types/type_show',
            'editPath' => '/types/type_edit',
            'destroyPath' => '/types/type_destroy',
            'updatePath' => '/types/type',
            'deletePath' => '/types/type',
        ];
    }

    protected function allRecords(): array
    {
        return $this->repo()->all();
    }

    protected function findRecord(int $id)
    {
        return $this->repo()->find($id);
    }

    protected function createRecord(string $name): bool
    {
        return $this->repo()->create($name);
    }

    protected function updateRecord(int $id, string $name): bool
    {
        return $this->repo()->update($id, $name);
    }

    protected function deleteRecord(int $id): bool
    {
        return $this->repo()->delete($id);
    }

    private function repo(): TypeRepository
    {
        return $this->repository;
    }
}
