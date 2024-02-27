<?php

namespace App\Forms;

class DeleteForm extends BaseForm
{

    /**
     * @param $validation
     * @return object
     */
    public function __requestValid($validation): object
    {

        $record = $this->model->where('id', $this->id)->first();

        if (!$record) {
            return (object)
            [
                'data' => [
                    'error' => 'Something went wrong.',
                    'message' => 'Not Found',
                    'code' => '404'
                ],
                'code' => 404
            ];
        }

        $record->delete();
        $this->deleted($record->id);

        return (object)['data' => 'Deleted Successfully', 'code' => 200];
    }

    protected function deleted($id)
    {
    }
}
