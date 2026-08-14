<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceBooking extends Model
{
    protected $fillable = [
        'name', 'phone', 'email',
        'vehicle_year', 'vehicle_model', 'rego', 'odometer',
        'service_type', 'preferred_date', 'preferred_time',
        'notes', 'read_at',
        'staff_reply', 'alt_date_1', 'alt_date_2', 'alt_date_3', 'replied_at',
    ];

    protected $casts = [
        'preferred_date' => 'date',
        'alt_date_1'     => 'date',
        'alt_date_2'     => 'date',
        'alt_date_3'     => 'date',
        'read_at'        => 'datetime',
        'replied_at'     => 'datetime',
    ];

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    public function hasBeenReplied(): bool
    {
        return $this->replied_at !== null;
    }
}
