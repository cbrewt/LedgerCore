<?php

namespace Core\Controllers;

abstract class NamedEntityCrudController extends Controller
{
    abstract protected function config(): array;

    abstract protected function allRecords(): array;

    abstract protected function findRecord(int $id);

    abstract protected function createRecord(string $name): bool;

    abstract protected function updateRecord(int $id, string $name): bool;

    abstract protected function deleteRecord(int $id): bool;

    public function index(): void
    {
        $config = $this->config();
        $viewData = [
            'title' => $config['indexTitle'],
            'heading' => $config['indexHeading'],
            $config['collectionKey'] => $this->allRecords(),
        ];

        view($config['indexView'], $viewData);
    }

    public function create(): void
    {
        $this->requireMethod('GET');

        $config = $this->config();

        view($config['createView'], [
            'title' => $config['createTitle'],
            'heading' => $config['createHeading'],
            'error' => null,
            'oldName' => ''
        ]);
    }

    public function store(): void
    {
        $this->requireMethod('POST');

        $config = $this->config();
        $name = $this->sanitizeName($config['field']);

        if ($name === '') {
            $this->renderCreateWithError('Name cannot be empty.', $name);
            return;
        }

        if ($this->createRecord($name)) {
            $this->redirect($config['listPath']);
        }

        $this->renderCreateWithError('Failed to create record.', $name);
    }

    public function show(): void
    {
        $config = $this->config();
        $id = $this->requirePositiveIdFromQuery();
        $row = $this->normalizeRecord($this->findRecord($id), $config['field']);

        if ($row === null) {
            $this->redirect($config['listPath']);
        }

        view($config['showView'], [
            'title' => $config['showTitle'],
            'heading' => $config['showHeading'],
            'row' => $row,
            'showPath' => $config['showPath'],
            'editPath' => $config['editPath'],
            'destroyPath' => $config['destroyPath']
        ]);
    }

    public function edit(): void
    {
        $this->requireMethod('GET');

        $config = $this->config();
        $id = $this->requirePositiveIdFromQuery();
        $row = $this->normalizeRecord($this->findRecord($id), $config['field']);

        if ($row === null) {
            $this->redirect($config['listPath']);
        }

        view($config['editView'], [
            'title' => $config['editTitle'],
            'heading' => $config['editHeading'],
            'row' => $row,
            'field' => $config['field'],
            'formAction' => $config['updatePath'],
            'cancelPath' => $config['showPath'],
            'error' => null
        ]);
    }

    public function update(): void
    {
        $this->requireMethod('PATCH');

        $config = $this->config();
        $id = $this->requirePositiveIdFromPost();
        $name = $this->sanitizeName($config['field']);

        if ($name === '') {
            $this->renderEditWithError($id, 'Name cannot be empty.', $name);
            return;
        }

        if ($this->updateRecord($id, $name)) {
            $this->redirect($config['listPath']);
        }

        $this->renderEditWithError($id, 'Failed to update record.', $name);
    }

    public function destroyForm(): void
    {
        $this->requireMethod('GET');

        $config = $this->config();
        $id = $this->requirePositiveIdFromQuery();
        $row = $this->normalizeRecord($this->findRecord($id), $config['field']);

        if ($row === null) {
            $this->redirect($config['listPath']);
        }

        view($config['destroyView'], [
            'title' => $config['destroyTitle'],
            'heading' => $config['destroyHeading'],
            'row' => $row,
            'field' => $config['field'],
            'formAction' => $config['deletePath'],
            'cancelPath' => $config['showPath']
        ]);
    }

    public function delete(): void
    {
        $this->requireMethod('DELETE');

        $config = $this->config();
        $id = $this->requirePositiveIdFromPost();

        $this->deleteRecord($id);
        $this->redirect($config['listPath']);
    }

    protected function normalizeRecord($record, string $field): ?array
    {
        if ($record === null) {
            return null;
        }

        if (is_array($record)) {
            if (isset($record[0]) && is_array($record[0])) {
                $record = $record[0];
            }
            if (!isset($record['id']) || !array_key_exists($field, $record)) {
                return null;
            }

            return [
                'id' => (int) $record['id'],
                $field => (string) $record[$field]
            ];
        }

        $id = $record->id ?? null;
        $value = $record->{$field} ?? null;
        if ($id === null || $value === null) {
            return null;
        }

        return [
            'id' => (int) $id,
            $field => (string) $value
        ];
    }

    protected function sanitizeName(string $field): string
    {
        $value = filter_input(INPUT_POST, $field, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        return trim((string) $value);
    }

    protected function renderCreateWithError(string $error, string $oldName): void
    {
        $config = $this->config();
        view($config['createView'], [
            'title' => $config['createTitle'],
            'heading' => $config['createHeading'],
            'error' => $error,
            'oldName' => $oldName
        ]);
    }

    protected function renderEditWithError(int $id, string $error, string $name): void
    {
        $config = $this->config();
        $row = $this->normalizeRecord($this->findRecord($id), $config['field']);
        if ($row === null) {
            $this->abort(404, 'Record not found.');
        }

        $row[$config['field']] = $name;

        view($config['editView'], [
            'title' => $config['editTitle'],
            'heading' => $config['editHeading'],
            'row' => $row,
            'field' => $config['field'],
            'formAction' => $config['updatePath'],
            'cancelPath' => $config['showPath'],
            'error' => $error
        ]);
    }

}
