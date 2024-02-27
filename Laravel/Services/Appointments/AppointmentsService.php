<?php

namespace App\Services\Appointments;

use App\Models\User;
use Carbon\Carbon;
use App\Models\UserSchedule;
use App\Models\UserScheduleOverrides;

class AppointmentsService
{
    /**
     * @param $time
     * @return int
     */
    public function transformTimeToMinutesDiff($time): int
    {
        $dt = Carbon::today();
        return $time ? Carbon::createFromFormat('Y-m-d H:i:s', $dt->format('Y-m-d').' '.$time)->diffInMinutes($dt) : 0;
    }

    /**
     * @param $builder
     * @param $startDate
     * @param $endDate
     * @return array
     */
    public function getTotalsForInterval($builder, $startDate, $endDate): array
    {
        $totals = ['0:00', '$0'];

        $query = clone $builder;
        $total = $query->where('date', '>=' , $startDate)->where('date', '<=' , $endDate)->first();
        if($total){
            $durationInMinutes = $total->total_duration%60 >= 10 ? $total->total_duration%60 : '0'.$total->total_duration%60;
            $totalHours = intdiv( (int) $total->total_duration,60) .':'.$durationInMinutes;
            $totalPrice = '$'.number_format($total->total_price);
            $totals = [$totalHours, $totalPrice];
        }

        return $totals;
    }

    /**
     * @param $appointment
     * @return bool
     */
    public function checkRateUs($appointment): bool
    {
        $user = $appointment->user();
        if($user->is_rate_us){
            return $user->is_rate_us < Carbon::now();
        }
        User::where('user_id', $user->id)->update(['is_rate_us' => Carbon::now()]);

        return true;
    }


    /**
     * @param $user
     * @param $date
     * @return mixed
     */
    public function getActualScheduleByDate($user, $date)
    {
        $scheduleOverrides = UserScheduleOverrides::where('user_id', $user->id)->where('date', $date)->get();
        if($scheduleOverrides->count()) return $scheduleOverrides;

        $day = (int) Carbon::createFromFormat('Y-m-d', $date)->format('w') + 1;

        return UserSchedule::where('user_id', $user->id)->where('day', $day)->get();
    }

    /**
     * @param $user
     * @param $appointment
     * @param $attributes
     * @return bool
     */
    public function checkIsIntervalInSchedule($user, $appointment, $attributes): bool
    {
        $schedule = $this->getActualScheduleByDate($user, $appointment->date);

        $isIntervalInSchedule = false;
        $start = $this->transformTimeToMinutesDiff($attributes['start']);
        $end = $this->transformTimeToMinutesDiff($attributes['end']);

        foreach($schedule as $interval) {
            $intervalStart = $this->transformTimeToMinutesDiff($interval->start);
            $intervalEnd = $this->transformTimeToMinutesDiff($interval->end);
            if($intervalStart <= $start && $intervalEnd >= $end) {
                $isIntervalInSchedule = true;
                break;
            }
        }

        return $isIntervalInSchedule;
    }

    /**
     * @param $user
     * @param $appointments
     * @param $attributes
     * @return bool
     */
    public function checkIsIntervalOccupied($appointments, $attributes): bool
    {
        $start = $this->transformTimeToMinutesDiff($attributes['start']);
        $end = $this->transformTimeToMinutesDiff($attributes['end']);

        $isIntervalOccupied = false;
        //$appointments = $this->datatable(['tab' => self::TAB_CALENDAR, 'date' => $appointment->date, 'user_id' => $user->id]);
        foreach($appointments as $appointment) {
            $appointmentStart = $this->service->transformTimeToMinutesDiff($appointment->start);
            $appointmentEnd = $this->service->transformTimeToMinutesDiff($appointment->end);
            if( ($appointmentStart <= $start && $appointmentEnd > $start) ||
                ($appointmentStart >= $start && $appointmentEnd <= $end) ||
                ($appointmentStart < $end && $appointmentEnd >= $end)
            ) {
                $isIntervalOccupied = true;
                break;
            }
        }

        return $isIntervalOccupied;
    }

}
