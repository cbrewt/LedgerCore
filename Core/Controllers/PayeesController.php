<?php

namespace Core\Controllers;

use Core\Repositories\PayeeRepository;

class PayeesController extends NamedEntityCrudController
{
    public function __construct(
        private PayeeRepository $repository
    ) {
    }

    protected function config(): array
    {
        return [
            'field' => 'payee_name',
            'collectionKey' => 'payees',
            'indexView' => 'payees/index.view.php',
            'createView' => 'payees/payee_create.view.php',
            'showView' => 'payees/payee_show.view.php',
            'editView' => 'payees/payee_edit.view.php',
            'destroyView' => 'payees/payee_destroy.view.php',
            'indexTitle' => 'Payees',
            'indexHeading' => 'Payees',
            'createTitle' => 'Add Payee',
            'createHeading' => 'Add Payee',
            'showTitle' => 'Payee',
            'showHeading' => 'Payee Name',
            'editTitle' => 'Payee',
            'editHeading' => 'Edit Payee Name',
            'destroyTitle' => 'Payee',
            'destroyHeading' => 'Delete Payee',
            'listPath' => '/payees/payee',
            'showPath' => '/payees/payee_show',
            'editPath' => '/payees/payee_edit',
            'destroyPath' => '/payees/payee_destroy',
            'updatePath' => '/payees/payee',
            'deletePath' => '/payees/payee',
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

    private function repo(): PayeeRepository
    {
        return $this->repository;
    }
}
