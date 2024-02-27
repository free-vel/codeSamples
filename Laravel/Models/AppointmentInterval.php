<?php

namespace App\Models;

use App\Traits\ScheduleStartEndTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentInterval extends Model
{
    use HasFactory, ScheduleStartEndTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'appointment_id',
        'start',
        'end',
    ];

    protected $table = 'appointment_intervals';

    public function appointment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'id');
    }

}
