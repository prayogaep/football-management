<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Goal extends Model
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

    public function getOneOrthAllGoal($id = null)
    {
        if ($id) {
            return self::with(['matchResult', 'player', 'team'])->where('id', $id)->where('is_deleted', 0)->first();
        }
        return self::with(['matchResult', 'player', 'team'])->where('is_deleted', 0)->get();
    }
    public function matchResult()
    {
        return $this->belongsTo(MatchResult::class, 'match_result_id');
    }

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }
}
