<?php

namespace App\Models;

use App\Traits\ScheduleStartEndTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, ScheduleStartEndTrait;

    const STATUS_NEW = 0;
    const STATUS_PENDING = 1;
    const STATUS_APPROVED = 2;
    const STATUS_ACTIVE = 3;
    const STATUS_FINISHED = 4;
    const STATUS_CANCELLED = 5;
    const STATUS_DELETED = 6;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'therapist_id',
        'service_id',
        'date',
        'start',
        'end',
        'status',
        'latitude',
        'longitude',
        'address',
        'address_description',
        'phone',
        'details',
        'description',
        'preferred_therapist_id',
        'price',
        'duration'
    ];

    protected $table = 'appointments';

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function service(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id', 'id');
    }

    public function therapist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'therapist_id', 'id');
    }

    public function preferred_therapist(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'preferred_therapist_id', 'id');
    }

    public function intervals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AppointmentInterval::class);
    }

    public function cancels(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AppointmentCancel::class);
    }
    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        $user = auth()->user();
        return $this->hasOne(AppointmentReview::class)->where('user_id', $user->id);
    }



}
