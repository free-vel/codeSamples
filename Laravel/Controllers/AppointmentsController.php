<?php

namespace App\Http\Controllers\Api;

use App\Forms\Appointments\AppointmentCreateForm;
use App\Forms\Appointments\AppointmentEditForm;
use App\Forms\Therapists\TherapistDeleteForm;
use App\Handlers\Services\Classes\Datatable;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\Appointments\AppointmentsRepository;
use App\Requests\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AppointmentsController extends Controller
{
    /**
     * Service Model
     *
     * @var AppointmentsRepository
     */
    public AppointmentsRepository $model;

    /**
     * Initialize Attributes
     *
     * @param AppointmentsRepository $model
     */
    public function __construct(AppointmentsRepository $model)
    {
        $this->model = $model;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request): JsonResponse
    {
        try {
            $result = (new Datatable($this->model, $request))->run();

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTotals(Request $request): JsonResponse
    {
        try {
            $result = $this->model->getTotals();

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function accept($id, Request $request): JsonResponse
    {
        try {
            $request = $request->all();
            $result = $this->model->accept($id, $request);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function cancel($id, Request $request): JsonResponse
    {
        try {
            $request = $request->all();
            $result = $this->model->cancel($id, $request);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function start($id): JsonResponse
    {
        try {
            $result = $this->model->start($id);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function finish($id): JsonResponse
    {
        try {
            $result = $this->model->finish($id);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function pay($id): JsonResponse
    {
        try {
            $result = $this->model->pay($id);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function review($id, Request $request): JsonResponse
    {
        try {
            $request = $request->all();
            $result = $this->model->review($id , $request);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @return JsonResponse
     */
    public function customerTestimonial($id, Request $request): JsonResponse
    {
        try {
            $request = $request->all();
            $request['author'] = User::TYPE_CLIENT;
            $request['testimonial'] = true;

            $result = $this->model->review($id , $request);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableSchedule(Request $request): JsonResponse
    {
        try {
            $request = $request->all();
            $result = $this->model->getAvailableScheduleForDate($request);

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function get($id): JsonResponse
    {
        try {
            $result = $this->model->byId($id, true, ['therapist', 'service', 'therapist.reviews', 'user.details', 'intervals']);
            if($result->therapist) {
                $result->therapist->reviews_count = $result->therapist->reviews_count();
                $result->therapist->rating = $result->therapist->rating();
            }

            return new JsonResponse($result, Response::HTTP_OK);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        try {
            $result = app(AppointmentCreateForm::class)
                ->run();

            return new JsonResponse($result->data, $result->code);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        try {
            $result = app(AppointmentEditForm::class)
                ->setId($id)
                ->run();

            return new JsonResponse($result->data, $result->code);
        } catch (\Exception $e) {

            return new JsonResponse([
                'error' => 'Something went wrong.',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


}
