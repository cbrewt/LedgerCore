<?php

namespace Core\Controllers;

use Core\Repositories\CategoryRepository;

class CategoriesController extends NamedEntityCrudController
{
    public function __construct(
        private CategoryRepository $repository
    ) {
    }

    protected function config(): array
    {
        return [
            'field' => 'category_name',
            'collectionKey' => 'categories',
            'indexView' => 'categories/index.view.php',
            'createView' => 'categories/create.view.php',
            'showView' => 'categories/show.view.php',
            'editView' => 'categories/edit.view.php',
            'destroyView' => 'categories/destroy.view.php',
            'indexTitle' => 'Categories',
            'indexHeading' => 'Categories',
            'createTitle' => 'Add Category',
            'createHeading' => 'Add Category',
            'showTitle' => 'Category',
            'showHeading' => 'Category Name',
            'editTitle' => 'Category',
            'editHeading' => 'Edit Category Name',
            'destroyTitle' => 'Categories',
            'destroyHeading' => 'Delete Category',
            'listPath' => '/categories/category',
            'showPath' => '/categories/category_show',
            'editPath' => '/categories/edit',
            'destroyPath' => '/categories/destroy',
            'updatePath' => '/categories/update',
            'deletePath' => '/categories/category',
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

    private function repo(): CategoryRepository
    {
        return $this->repository;
    }
}
