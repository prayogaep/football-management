<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Team extends Model 
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false; 

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string'; 

    /**
     * Boot function to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate UUID during creation
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid(); // Generate UUID
            }
        });
    }

    public function getOneOrAllTeam($id = null) {
        if ($id) {
            return self::with(['players', 'createdBy','schedules'])->where('id', $id)->where('is_deleted', 0)->first();
        }
        return self::with(['players', 'createdBy', 'schedules'])->where('is_deleted', 0)->get();
    }

    public function players() {
        return self::hasMany(Player::class)->where('is_deleted', 0);
    }
    public function schedules() {
        $homeSchedule = self::hasMany(Schedule::class, 'home_team_id','id')->where('is_deleted', 0);
        $unionAwalSchedule = self::hasMany(Schedule::class, 'away_team_id','id')->union($homeSchedule)->where('is_deleted', 0);
        return $unionAwalSchedule;
    }
    public function createdBy() {
        return self::belongsTo(User::class, 'created_by', 'id');
    }
}
