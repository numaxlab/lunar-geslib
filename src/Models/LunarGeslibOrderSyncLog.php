<?php

namespace NumaxLab\LunarGeslib\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LunarGeslibOrderSyncLog extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lunar_geslib_order_sync_log';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'geslib_endpoint_called',
        'status',
        'message',
        'payload_to_geslib',
        'payload_from_geslib',
    ];
}
