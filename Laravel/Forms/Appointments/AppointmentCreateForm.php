<?php

namespace App\Forms\Appointments;

use App\Forms\BaseForm;
use App\Repositories\Appointments\AppointmentsRepository;
use Illuminate\Http\Response;

class AppointmentCreateForm extends BaseForm
{
    /**
     * @var string[]
     */
    protected array $rules = [
        'type' => 'required|int|min:1',
        'service_id' => 'required|integer',
        'date' => 'required|date_format:Y-m-d',
        'intervals' => 'required|array',
    ];

    /**
     * @param $result
     * @return object
     */
    public function __requestValid($result): object
    {
        $attributes = $this->request->all();

        $appointment = app(AppointmentsRepository::class)->create($attributes);

        return $this->response($appointment, Response::HTTP_OK);
    }

}
