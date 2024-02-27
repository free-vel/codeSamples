<?php

namespace App\Forms\Appointments;

use App\Forms\BaseForm;
use App\Repositories\Appointments\AppointmentsRepository;
use App\Repositories\Users\UsersRepository;
use ErrorException;
use Illuminate\Http\Response;

class AppointmentEditForm extends BaseForm
{
    /**
     * @var string[]
     */
    protected array $rules = [
        'service_id' => 'required|integer',
        'date' => 'required|date_format:Y-m-d',
        'intervals' => 'required|array',
    ];

    public function __construct(
        UsersRepository $userService,
    )
    {
        $this->userService = $userService;
    }

    /**
     * @param $result
     * @return object
     * @throws ErrorException
     */
    public function __requestValid($result): object
    {

        $attributes = $this->request->all();

        $appointment = app(AppointmentsRepository::class)->edit($attributes, $this->id);

        return $this->response($appointment, Response::HTTP_OK);
    }

    protected function checkPermissions(): bool
    {
        return $this->userService->checkPermissions($this->id);
    }

}
