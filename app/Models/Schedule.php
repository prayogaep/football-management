<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Schedule extends Model
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
    public function getOneOrAllSchedule($id = null)
    {
        if ($id) {
            return self::with(['homeTeam', 'awayTeam', 'createdBy'])->where('id', $id)->where('is_deleted', 0)->first();
        }
        return self::with(['homeTeam', 'awayTeam', 'createdBy'])->where('is_deleted', 0)->get();
    }


    public function isScheduleConflict($validatedData, $id = null)
    {
        $query = self::where(function ($query) use ($validatedData) {
            $query->where('home_team_id', $validatedData['home_team_id'])
                ->orWhere('away_team_id', $validatedData['home_team_id'])
                ->orWhere('home_team_id', $validatedData['away_team_id'])
                ->orWhere('away_team_id', $validatedData['away_team_id']);
        })
            ->where('date_schedule', $validatedData['date_schedule'])
            ->where('time_schedule', $validatedData['time_schedule'])
            ->where('is_deleted', 0);

        if ($id) {
            $query->where('id', '!=', $id);
        }

        return $query->exists();
    }

    public function getOneOrAllScheduleReport($id = null)
    {
        $schedules = Schedule::with([
            'homeTeam',
            'awayTeam',
            'matchResult',
            'matchResult.goals.player'
        ]);

        // Jika ID diberikan, ambil satu data, jika tidak ambil semua
        if ($id) {
            $schedules = $schedules->where('id', $id);
        }

        $reports = $schedules->get()->map(function ($schedule) {
            $matchResult = $schedule->matchResult;

            // Default untuk status pertandingan
            $status = 'Draw';
            if ($matchResult && $matchResult->home_team_score > $matchResult->away_team_score) {
                $status = 'Home Team Wins';
            } elseif ($matchResult && $matchResult->home_team_score < $matchResult->away_team_score) {
                $status = 'Away Team Wins';
            }


            $topScorer = null;
            if ($matchResult) {
                $topScorer = $matchResult->goals->where('is_deleted', 0)->groupBy('player_id')->map(function ($goals) {
                    return [
                        'player' => $goals->first()->player->name_player ?? null,
                        'total_goals' => $goals->count(),
                    ];
                })->sortByDesc('total_goals')->first();
            }


            $homeWins = MatchResult::whereHas('schedule', function ($query) use ($schedule) {
                $query->where('home_team_id', $schedule->home_team_id)->where('is_deleted', 0);
            })
                ->whereColumn('home_team_score', '>', 'away_team_score')
                ->count();

            $awayWins = MatchResult::whereHas('schedule', function ($query) use ($schedule) {
                $query->where('away_team_id', $schedule->away_team_id)->where('is_deleted', 0);
            })
                ->whereColumn('away_team_score', '>', 'home_team_score')
                ->count();

            return [
                'date_schedule' => $schedule->date_schedule,
                'time_schedule' => $schedule->time_schedule,
                'home_team' => $schedule->homeTeam->name_team ?? 'Unknown',
                'away_team' => $schedule->awayTeam->name_team ?? 'Unknown',
                'home_team_score' => $matchResult->home_team_score ?? null,
                'away_team_score' => $matchResult->away_team_score ?? null,
                'match_status' => $status,
                'top_scorer' => $topScorer['player'] ?? null,
                'top_scorer_goals' => $topScorer['total_goals'] ?? 0,
                'home_team_total_wins' => $homeWins,
                'away_team_total_wins' => $awayWins,
            ];
        });

        return $reports;
    }



    public function matchResult()
    {
        return self::hasOne(MatchResult::class, 'schedule_id', 'id');
    }
    public function homeTeam()
    {
        return self::belongsTo(Team::class, 'home_team_id', 'id');
    }
    public function awayTeam()
    {
        return self::belongsTo(Team::class, 'away_team_id', 'id');
    }
    public function createdBy()
    {
        return self::belongsTo(User::class, 'created_by', 'id');
    }
}
