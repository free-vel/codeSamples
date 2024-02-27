<?php

namespace App\Forms;

use App\Handlers\Services\Abstracts\AbstractCommonRequestService;
use App\Models\User;

abstract class BaseForm extends AbstractCommonRequestService
{
    /**
     * @var array
     */
    protected array $rules = [];

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var string
     */
    protected string $createdByFieldName = 'user_id';

    /**
     * @return string[]
     */
    public function __createValidations(): array
    {
        return array_merge([
            'user_id' => 'sometimes|required|integer',
        ], $this->rules);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param $model
     *
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @param     $data
     * @param int $code
     *
     * @return object
     */
    protected function response($data, int $code): object
    {
        return (object)['data' => $data, 'code' => $code];
    }
}
