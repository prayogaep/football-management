<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MatchResult extends Model
{
    use HasFactory;
    protected $table = 'match_results';
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

    public function getOneOrthAllMatchResult($id = null)
    {
        if ($id) {
            return self::with(['schedule', 'goals.player', 'goals.team'])->where('id', $id)->where('is_deleted', 0)->first();
        }
        return self::with(['schedule', 'goals.player', 'goals.team'])->where('is_deleted', 0)->get();
    }

    public function isDrawZero($id) {
        $matchResult = self::where('id', $id)->where('is_deleted', 0)->first();
        $result = $matchResult->home_team_score == 0 && $matchResult->away_team_score == 0 ? true : false;
        return $result;
    }
    public function schedule()
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class, 'match_result_id');
    }
}
