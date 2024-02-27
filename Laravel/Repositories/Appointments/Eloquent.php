<?php

namespace App\Repositories\Appointments;

use App\Models\Appointment;
use App\Models\AppointmentCancel;
use App\Models\AppointmentInterval;
use App\Models\AppointmentReview;
use App\Models\Media;
use App\Models\Service;
use App\Models\User;
use App\Models\UserBlocked;
use App\Models\UserDetails;
use App\Models\UserSchedule;
use App\Models\UserScheduleOverrides;
use App\Repositories\Media\MediaRepository;
use App\Repositories\Services\ServicesRepository;
use App\Repositories\Users\UsersRepository;
use App\Services\Appointments\AppointmentsService;
use App\Services\DataTableService;
use App\Services\Mail\MailService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Eloquent implements AppointmentsRepository
{
    public const EARTH_RADIUS = 6371008;

    public const TAB_FIND = 'find';
    public const TAB_CART = 'cart';
    public const TAB_REBOOKING = 'rebooking';
    public const TAB_UPCOMING = 'upcoming';
    public const TAB_PAST = 'past';
    public const TAB_CANCELLED = 'cancelled';
    public const TAB_CALENDAR = 'calendar';
    public const TAB_ALL = 'all';

    public const TABS_ARRAY = [
        self::TAB_FIND => [Appointment::STATUS_PENDING],
        self::TAB_CART => [Appointment::STATUS_NEW],
        self::TAB_REBOOKING => [Appointment::STATUS_PENDING],
        self::TAB_UPCOMING => [Appointment::STATUS_APPROVED, Appointment::STATUS_PENDING],
        self::TAB_PAST => [Appointment::STATUS_FINISHED, Appointment::STATUS_APPROVED, Appointment::STATUS_ACTIVE],
        self::TAB_CANCELLED => [Appointment::STATUS_CANCELLED],
        self::TAB_CALENDAR => [Appointment::STATUS_APPROVED, Appointment::STATUS_ACTIVE, Appointment::STATUS_FINISHED],
        self::TAB_ALL => [],
    ];

    const DATATABLE_TYPE = 'appointments';

	/**
	 * Model
	 *
	 * @var Appointment
	 */
	protected Appointment $model;

    /**
     * Cancel Model
     *
     * @var AppointmentCancel
     */
    protected AppointmentCancel $modelCancel;

    /**
     * Review Model
     *
     * @var AppointmentReview
     */
    protected AppointmentReview $modelReview;

    /**
     * Review Model
     *
     * @var UserBlocked
     */
    protected UserBlocked $modelBlocked;

    /**
     * Review Model
     *
     * @var AppointmentsService
     */
    protected AppointmentsService $service;

    /**
     * Review Model
     *
     * @var ServicesRepository
     */
    protected ServicesRepository $serviceService;

    /**
     * Data Table Service
     *
     * @var DataTableService
     */
    protected DataTableService $dataTableService;

    /**
     * Mail
     *
     * @var MailService
     */
    protected MailService $mailService;

    /**
     * User repository
     *
     * @var UsersRepository
     */
    protected UsersRepository $userService;


    /**
     * Initialize Attributes
     *
     * @param Appointment $model
     * @param AppointmentCancel $modelCancel
     * @param AppointmentReview $modelReview
     * @param UserBlocked $modelBlocked
     * @param AppointmentsService $service
     * @param ServicesRepository $serviceService
     * @param DataTableService $dataTableService
     * @param MailService $mailService
     * @param UsersRepository $userService
     */
    public function __construct(Appointment $model, AppointmentCancel $modelCancel, AppointmentReview $modelReview,
                                UserBlocked $modelBlocked, AppointmentsService $service, ServicesRepository $serviceService,
                                DataTableService $dataTableService, MailService $mailService, UsersRepository $userService)
	{
		$this->model = $model;
        $this->modelCancel = $modelCancel;
        $this->modelReview = $modelReview;
        $this->modelBlocked = $modelBlocked;
        $this->service = $service;
        $this->serviceService = $serviceService;
        $this->dataTableService = $dataTableService;
        $this->mailService = $mailService;
        $this->userService = $userService;
	}

    /**
     * @param $id
     * @param bool $withRelations
     * @param array $with
     * @return Collection|Model
     */
	public function byId($id, bool $withRelations = true, array $with = []): Model|Collection
    {
        if ($withRelations) {
            return $this->model->with($with)
                ->findOrFail($id);
        }
        return $this->model->findOrFail($id);
	}

    /**
     * @param array $ids
     * @return mixed
     */
    public function getByIds(array $ids = []): mixed
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    public function changeStatusToPendingByIds(array $ids = [])
    {
        $this->model->whereIn('id', $ids)->update(['status' => Appointment::STATUS_PENDING]);
    }

    /**
     * Datatable Request
     *
     * @param array $request
     * @param boolean $total
     * @return mixed
     */
    public function datatable(array $request = [], bool $total = false): mixed
    {
        $request['tab'] = isset($request['tab']) && isset(self::TABS_ARRAY[$request['tab']]) ? $request['tab'] : self::TAB_FIND;
        $request['filter'] = $request['filter'] ?? [];

        $request['filter']['statuses'] = self::TABS_ARRAY[$request['tab']];

        $builder = $this->model->with(['therapist', 'service', 'user.details', 'intervals', 'review'])
            ->select([
                'appointments.*'
            ]);
        switch ($request['tab']){
            case (self::TAB_FIND):
                $request['filter']['date'] = $request['date'] ?? Carbon::today()->format('Y-m-d');
                $builder = $this->datatableBlocked($builder);
                $builder = $this->datatableGender($builder);
                $builder = $this->datatableSchedule($builder, $request);
                $builder = $this->datatableDistance($builder, $request);
                break;
            case (self::TAB_REBOOKING):
                $builder = $this->datatableBlocked($builder);
                break;
            case (self::TAB_UPCOMING):
                $builder = $this->datatableAfterDate($builder);
                break;
            case (self::TAB_PAST):
                $builder = $this->datatableBeforeDate($builder);
                break;
            case (self::TAB_CANCELLED):
                $builder = $this->datatableCancelled($builder, $request);
                return !$total ? $builder->get() : $builder->count();
            case (self::TAB_CALENDAR):
                $request['filter']['date'] = $request['filter']['date'] ?? Carbon::today()->format('Y-m-d');
                break;
            default:
                break;
        }

        $builder = $this->datatableFilter($builder, $request);
        $builder = $this->datatableUser($builder, $request, 'therapist_id');
        $builder = $this->datatableRebooked($builder, $request);
        $builder = $this->dataTableService->datatableOrderBy($builder, $request, self::DATATABLE_TYPE);
        $builder = $this->dataTableService->datatableLimit($builder, $request, $total);

        $builder = $builder
            ->groupBy('appointments.id')
            ->get();

        return !$total ? $builder : $builder->count();
    }

    /**
     * @param $id
     * @param $request
     * @return Model|Collection
     * @throws Exception
     */
    public function accept($id, $request): Model|Collection
    {
        $user = auth()->user();
        $model = $this->byId($id, false);
        if(!$user || $model->therapist_id !== null ||
            ($model->status !== Appointment::STATUS_PENDING)){
            throw new Exception('This Appointment is already approved.');
        }

        $attributes['therapist_id'] = $user->id;
        $attributes['start'] = $request['start'] ?? '00:00:00';
        $attributes['end'] = $request['end'] ?? '00:00:00';
        $attributes['status'] = Appointment::STATUS_APPROVED;

        $isIntervalInSchedule = $this->service->checkIsIntervalInSchedule($user, $model, $attributes);
        if(!$isIntervalInSchedule){
            throw new Exception('Wrong time interval.');
        }

        $appointments = $this->datatable(['tab' => self::TAB_CALENDAR, 'date' => $model->date, 'user_id' => $user->id]);
        $isIntervalOccupied = $this->service->checkIsIntervalOccupied($appointments, $attributes);
        if($isIntervalOccupied){
            throw new Exception('This time is occupied.');
        }

        $model->fill($attributes);
        $model->save();

        $model->refresh();

        try {
            $this->mailService->newAppointmentCustomer($model);
        } catch (\Exception $e) {
        }

        return $model;
    }

    /**
     * @throws Exception
     */
    public function cancel($id, $request): AppointmentCancel
    {
        $user = auth()->user();
        $model = $this->byId($id, false);
        if(!$user || ($model->therapist_id !== $user->id && !$user->isAdmin()) ) throw new Exception('Wrong User.');

        $modelCancel = clone $this->modelCancel;

        $attributes['comment'] = $request['comment'] ?? '';
        $attributes['appointment_id'] = $id;
        $attributes['therapist_id'] = $model->therapist_id;

        $modelCancel->fill($attributes);
        $modelCancel->save();

        $attributes['therapist_id'] = null;
        if($model->status === Appointment::STATUS_APPROVED) $attributes['status'] = Appointment::STATUS_PENDING;

        $model->fill($attributes);
        $model->save();

        return $modelCancel;
    }

    /**
     * @param $id
     * @return Model|Collection
     * @throws Exception
     */
    public function start($id): Model|Collection
    {
        $user = auth()->user();
        $model = $this->byId($id, false);

        if(!$user || $model->therapist_id !== $user->id) throw new Exception('Wrong User.');
        if($model->status !== Appointment::STATUS_APPROVED) throw new Exception('Wrong Status.');

        $attributes['status'] = Appointment::STATUS_ACTIVE;

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @return Model|Collection
     * @throws Exception
     */
    public function finish($id): Model|Collection
    {
        $user = auth()->user();
        $model = $this->byId($id, false);

        if(!$user || $model->therapist_id !== $user->id) throw new Exception('Wrong User.');
        if($model->status !== Appointment::STATUS_ACTIVE) throw new Exception('Wrong Status.');

        $attributes['status'] = Appointment::STATUS_FINISHED;

        $model->fill($attributes);
        $model->save();

        $model->is_show_rate_us = $this->service->checkRateUs($model);

        return $model;
    }

    /**
     * @throws Exception
     */
    public function pay($id): Model|Collection
    {
        $user = auth()->user();
        $model = $this->byId($id, false);

        if(!$user || $model->user_id !== $user->id) throw new Exception('Wrong User.');
        if($model->status !== Appointment::STATUS_NEW) throw new Exception('Wrong Status.');

        $attributes['status'] = Appointment::STATUS_PENDING;

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * @param $id
     * @param $attributes
     * @return AppointmentReview
     * @throws Exception
     */
    public function review($id, $attributes): AppointmentReview
    {
        $user = auth('api')->user();
        $model = $this->byId($id, false);

        $authorId = $user->type === User::TYPE_THERAPIST ? $model->therapist_id : $model->user_id;
        $targetId = $user->type === User::TYPE_THERAPIST ? $model->user_id : $model->therapist_id;

        if(!$user || $authorId !== $user->id) throw new Exception('Wrong User.');
        if($model->status !== Appointment::STATUS_FINISHED) throw new Exception('Wrong Status.');

        $existingReview = $this->modelReview->where('user_id', $user->id)->where('appointment_id', $id)->first();

        if($existingReview) {
            throw new Exception('You have already left a review!');
        }

        $modelReview = clone $this->modelReview;

        $attributes['appointment_id'] = $model->id;
        $attributes['target_id'] = isset($attributes['testimonial']) ? null : $targetId;
        $attributes['user_id'] = $user->id;
        $attributes['comment'] = $attributes['comment'] ?? '';
        $attributes['rating'] = $attributes['rating'] ?? 0;

        $modelReview->fill($attributes);
        $modelReview->save();

        return $modelReview;
    }

	/**
	 * Stores a resource.
	 *
	 * @param  array $attributes
	 * @return Appointment
	 */
	public function store(array $attributes): Appointment
    {
        $model = clone $this->model;

        $model->fill($attributes);
        $model->save();

		return $model;
	}

    /**
     * @param array $attributes
     * @return Appointment
     */
    public function create(array $attributes): Appointment
    {
        $user = auth()->user();
        $attributes['user_id'] = $user->id;
        $attributes['latitude'] = $attributes['latitude'] ?? $user->details->latitude;
        $attributes['longitude'] = $attributes['longitude'] ?? $user->details->longitude;
        $attributes['address'] = $attributes['address'] ?? $user->details->address;
        $attributes['address_description'] = $attributes['address_description'] ?? $user->details->address_description;
        $attributes['phone'] = $attributes['phone'] ?? $user->details->phone;
        $attributes['type'] = $attributes['type'] ?? Service::TYPE_INITIAL;
        $attributes['duration'] = Service::TYPES[$attributes['type']]['duration'] ?? 60;
        $attributes['price'] = Service::TYPES[$attributes['type']]['price'] ?? 199;

        $model = $this->store($attributes);
        foreach($attributes['intervals'] as $interval){
            $interval['appointment_id'] = $model->id;
            $intervalModel = new AppointmentInterval();
            $intervalModel->fill($interval);
            $intervalModel->save();
        }

        $this->userService->checkAddressIsset($user, $attributes);

        return $model;
    }

    /**
     * @param array $attributes
     * @param $id
     * @return Model|Collection
     * @throws Exception
     */
    public function edit(array $attributes, $id): Model|Collection
    {
        $user = auth()->user();
        $model = $this->byId($id, true, ['intervals']);
        if( !($model->status === Appointment::STATUS_NEW || $model->status === Appointment::STATUS_PENDING || $user->isAdmin()) ) {
            throw new Exception('Wrong Appointment.');
        }

        if( !($user->isAdmin() || $model->user_id === $user->id || $model->therapist_id === $user->id) ){
            throw new Exception('Permission error');
        }

        foreach($model->intervals as $item){
            $item->delete();
        }

        foreach($attributes['intervals'] as $interval){
            $interval['appointment_id'] = $model->id;
            $intervalModel = new AppointmentInterval();
            $intervalModel->fill($interval);
            $intervalModel->save();
        }

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Updates the Resource
     *
     * @param array $attributes
     * @param  $id
     * @return Collection|Model
     */
    public function update(array $attributes, $id): Model|Collection
    {
        $model = $this->byId($id, false);

        $model->fill($attributes);
        $model->save();

        return $model;
    }

    /**
     * Destroy a resource.
     *
     * @param  $id
     */
	public function destroy($id)
    {
        $model = $this->byId($id, false);
        $model->delete();
	}

    private function datatableFilter($query, $request)
    {
        $query->when(isset($request['filter']['status']) && $request['filter']['status'] !== '',
            function ($query) use ($request) {
                return $query->where('appointments.status', $request['filter']['status']);
            })
            ->when(isset($request['filter']['therapist_id']) && $request['filter']['therapist_id'] !== '',
                function ($query) use ($request) {
                    return $query->where('appointments.therapist_id', $request['filter']['therapist_id']);
                })
            ->when(isset($request['filter']['date']) && $request['filter']['date'] !== '',
                function ($query) use ($request) {
                    return $query->where('appointments.date', $request['filter']['date']);
                })
            ->when(isset($request['filter']['start_date']) && $request['filter']['start_date'] !== '' && isset($request['filter']['end_date']) && $request['filter']['end_date'] !== '',
                function ($query) use ($request) {
                    return $query->where('appointments.date', '>=', $request['filter']['start_date'])->where('appointments.date', '<=', $request['filter']['end_date']);
                })
            ->when(isset($request['filter']['statuses']) && is_array($request['filter']['statuses']) && $request['filter']['statuses'] !== [],
                function ($query) use ($request) {
                    $query->where(function($qOr) use ($request) {
                        foreach($request['filter']['statuses'] as $status) {
                            $qOr->orWhere('appointments.status', $status);
                        }
                    });

                    return $query;
                })
            ->when(isset($request['filter']['services_ids']) && $request['filter']['services_ids'] !== [] ,
                function ($query) use ($request) {
                    return $query->whereIn('appointments.service_id', $request['filter']['services_ids']);
                });


        return $query;
    }

    private function datatableSchedule($query, $request)
    {
        $value = $request['date'] ?? Carbon::today()->format('Y-m-d');

        if($value !== '') {
            $user = auth()->user();
            $schedule = $this->service->getActualScheduleByDate($user, $value);
            if(!$schedule->count()) return $query->where('id', 0);

            $query->whereHas('intervals', function($qInterval) use ($schedule){
                $qInterval->where(function($qInt) use ($schedule){
                    $qInt->orWhere(function($q) use ($schedule){
                        foreach($schedule as $interval){
                            $dt = Carbon::today();
                            $start = $interval->start ? Carbon::createFromFormat('Y-m-d H:i:s', $dt->format('Y-m-d').' '.$interval->start)->diffInMinutes($dt) : 0;
                            $end = $interval->end ? Carbon::createFromFormat('Y-m-d H:i:s', $dt->format('Y-m-d').' '.$interval->end)->diffInMinutes($dt) : 0;

                            $q->where('start', '>=', $start); // user 8-12, therapist 7-9
                            $q->where('start', '<=', $end - 60);

                            $q->orWhere(function($qOr) use ($start, $end) {
                                $qOr->where('end', '>=', $start + 60); // user 8-12, therapist 11-14
                                $qOr->where('end', '<=', $end);
                            });
                            $q->orWhere(function($qOr) use ($start, $end) {
                                $qOr->where('start', '>=', $start); // user 8-12, therapist 7-13
                                $qOr->where('end', '<=', $end);
                            });
                            $q->orWhere(function($qOr) use ($start, $end) {
                                $qOr->where('start', '<=', $start); // user 8-12, therapist 9-11
                                $qOr->where('end', '>=', $end);
                            });
                        }
                    });
                });
            });
        }

        return $query;
    }

    private function datatableDistance($query, $request)
    {

        $user = auth()->user();
        $details = $user?->details;
        if($details && !is_null($details->longitude) && !is_null($details->latitude) ) {
            $query->select("appointments.*" , DB::raw("(" . self::EARTH_RADIUS . " * acos(cos(radians('" . $details->latitude . "')) * cos(radians(appointments.latitude)) * cos( radians(appointments.longitude) - radians('" . $details->longitude . "')) + sin(radians('" . $details->latitude . "')) * sin(radians(appointments.latitude))))
            AS distance"))
                ->whereRaw("(" . self::EARTH_RADIUS . " * acos(cos(radians('" . $details->latitude . "')) * cos(radians(appointments.latitude)) * cos( radians(appointments.longitude) - radians('" . $details->longitude . "')) + sin(radians('" . $details->latitude . "')) * sin(radians(appointments.latitude))))
            <= ?", [$details->radius]);
        }

        return $query;
    }

    private function datatableBlocked($query)
    {
        $user = auth()->user();
        $blockedUserIds = $this->modelBlocked->where('user_id', $user->id)->get()->pluck('target_id');
        if($blockedUserIds->count()){
            $query->whereRaw('appointments.user_id NOT IN(?)', [$blockedUserIds]);
        }

        return $query;
    }

    private function datatableRebooked($query, $request)
    {
        if($request['tab'] === self::TAB_REBOOKING) {
            $user = auth()->user();
            $query->where('appointments.preferred_therapist_id', $user->id);
        }else{
            $query->whereRaw('appointments.preferred_therapist_id IS NULL', []);
        }

        return $query;
    }

    private function datatableUser($query, $request, $field = 'therapist_id')
    {
        $user = auth()->user();
        if(isset($request['user_id'])) $user = (object) ['id' => $request['user_id']];

        $query->where(function ($query) use ($request, $user, $field) {
            if( $request['tab'] === self::TAB_FIND || $request['tab'] === self::TAB_REBOOKING){
                $query->whereRaw('appointments.'.$field.' IS NULL', []);
            }else{
                $query->where('appointments.'.$field, $user->id);
            }
        });

        return $query;
    }

    private function datatableCancelled($query, $request)
    {
        $user = auth()->user();

        $query->orWhere(function ($qOr) use ($user) {
            $qOr->whereHas('cancels', function ($q) use ($user) {
                $q->where('therapist_id', $user->id);
            });
        });

        return $query;
    }

    private function datatableCancelledCustomer($query, $request)
    {
        $query->orWhere(function ($qOr) {
            $qOr->whereHas('cancels');
        });

        return $query;
    }

    private function datatableGender($query)
    {
        $user = auth()->user();

        $query->whereHas('user.details', function ($q) use ($user) {
            if( $user->details->preferred_gender !== UserDetails::GENDER_ALL ){
                $q->where('gender', $user->details->preferred_gender);
            }
            $q->where(function ($qGender) use ($user) {
                $qGender->where('preferred_gender', $user->details->gender);
                $qGender->orWhere('preferred_gender', UserDetails::GENDER_ALL);
            });
        });

        return $query;
    }

    private function datatableSearch($query, $request)
    {
        $value = $request['search']['value'] ?? '';
        if($value !== '') {
            $query->where(function ($query) use ($value, $request) {
                $query->where('appointments.name', 'LIKE', '%' . $value . '%');
            });
        }

        return $query;
    }

    private function datatableBeforeDate($query)
    {
        $value = Carbon::today()->format('Y-m-d');
        $query->where(function ($query) use ($value) {
            $query->where('appointments.date', '<=', $value);
        });

        return $query;
    }

    private function datatableAfterDate($query)
    {
        $value = Carbon::today()->format('Y-m-d');
        $query->where(function ($query) use ($value) {
            $query->where('appointments.date', '>=', $value);
        });

        return $query;
    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function getAvailableScheduleForDate($attributes): mixed
    {
        $user = (object) ['id' => $attributes['therapist_id']];
        $date = $attributes['date'] ?? Carbon::today()->format('Y-m-d');
        $schedule = $this->service->getActualScheduleByDate($user, $date);
        $appointments = $this->datatable(['tab' => self::TAB_CALENDAR, 'date' => $date, 'user_id' => $user->id]);

        foreach($appointments as $appointment) {
            $appointmentStart = $this->service->transformTimeToMinutesDiff($appointment->start);
            $appointmentEnd = $this->service->transformTimeToMinutesDiff($appointment->end);

            foreach($schedule as $key => &$interval){
                $start = $this->service->transformTimeToMinutesDiff($interval->start);
                $end = $this->service->transformTimeToMinutesDiff($interval->end);

                if( ($appointmentStart <= $start && $appointmentEnd > $start) ||
                    ($appointmentStart >= $start && $appointmentEnd <= $end) ||
                    ($appointmentStart < $end && $appointmentEnd >= $end)
                ) {
                    if($appointmentStart <= $start && $appointmentEnd > $start){
                        $interval->start = $appointment->end;
                    }
                    if($appointmentStart < $end && $appointmentEnd >= $end){
                        $interval->end = $appointment->start;
                    }
                    if($appointmentStart >= $start && $appointmentEnd <= $end){
                        $schedule[] = (object) ['start' => $appointment->end, 'end' => $interval->end];
                        $interval->end = $appointment->start;
                    }
                }

            }

        }

        foreach($schedule as $key => $interval) {
            $start = $this->service->transformTimeToMinutesDiff($interval->start);
            $end = $this->service->transformTimeToMinutesDiff($interval->end);

            if($start >= ($end - 60) ){
                unset($schedule[$key]);
            }
        }

        return $schedule;
    }

    /**
     * @return array
     */
    public function getTotals(): array
    {
        $user = auth()->user();

        $totals = [];

        $builder = $this->model
            ->selectRaw(
                'SUM(duration) as total_duration, SUM(price) as total_price'
            )
            ->where('therapist_id', $user->id)
            ->where('status', Appointment::STATUS_FINISHED);

        $dt = Carbon::today();

        $startDate = $dt->format('Y-m-d');
        $endDate = $dt->format('Y-m-d');
        $totals['TODAY'] = $this->service->getTotalsForInterval($builder, $startDate, $endDate);

        $startDate = $dt->startOfWeek()->format('Y-m-d');
        $endDate = $dt->endOfWeek()->format('Y-m-d');
        $totals['THIS WEEK'] = $this->service->getTotalsForInterval($builder, $startDate, $endDate);

        $startDate = $dt->startOfMonth()->format('Y-m-d');
        $endDate = $dt->endOfMonth()->format('Y-m-d');
        $totals['THIS MONTH'] = $this->service->getTotalsForInterval($builder, $startDate, $endDate);

        $startDate = '2022-12-12';
        $endDate = '2050-12-12';
        $totals['TOTAL'] = $this->service->getTotalsForInterval($builder, $startDate, $endDate);

        return $totals;
    }
}
