<?php

namespace App\Traits;

use Carbon\Carbon;

trait ScheduleStartEndTrait
{
    /**
     * Start Time Accessor
     *
     * @param $value integer
     * @return string
     */
    public function getStartAttribute(int $value): string
    {
        return $value && $value != 0 ? Carbon::today()->add($value, 'minutes')->format('H:i:s') : '';
    }

    /**
     * Start Time Mutator
     *
     * @param $value string
     */
    public function setStartAttribute(string $value)
    {
        try {
            $dt = Carbon::today();
            $this->attributes['start'] = $value ? Carbon::createFromFormat('Y-m-d H:i:s', $dt->format('Y-m-d').' '.$value)->diffInMinutes($dt) : 0;
        } catch (\Exception $e) {
            $this->attributes['start'] = 0;
        }
    }

    /**
     * End Time Accessor
     *
     * @param $value integer
     * @return string
     */
    public function getEndAttribute(int $value): string
    {
        return $value && $value != 0 ? Carbon::today()->add($value, 'minutes')->format('H:i:s') : '';
    }

    /**
     * End Time Mutator
     *
     * @param $value string
     */
    public function setEndAttribute(string $value)
    {
        try {
            $dt = Carbon::today();
            $this->attributes['end'] = $value ? Carbon::createFromFormat('Y-m-d H:i:s', $dt->format('Y-m-d').' '.$value)->diffInMinutes($dt) : 0;
        } catch (\Exception $e) {
            $this->attributes['end'] = 0;
        }
    }
}
