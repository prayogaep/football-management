<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\MatchResult;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoalController extends Controller
{
    protected $goalModel;
    protected $playerModel;
    protected $matchResultModel;

    public function __construct()
    {
        parent::__construct();
        $this->goalModel = new Goal();
        $this->playerModel = new Player();
        $this->matchResultModel = new MatchResult();
    }
    public function index()
    {
        return $this->handleException(function () {
            $goals = $this->goalModel->getOneOrthAllGoal();
            return $this->responseFormatter->format(true, 'Success', $goals);
        });
    }

    public function show($id)
    {
        return $this->handleException(function () use ($id) {
            $goal = $this->goalModel->getOneOrthAllGoal($id);
            if (!$goal) {
                return $this->returnNotFound("Goal");
            }
            return $this->responseFormatter->format(true, 'Success', $goal);
        });
    }
    public function store(Request $request)
    {
        $validation = $this->requestValidation($request);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation) {
            $checkIsDrawZero = $this->matchResultModel->isDrawZero($validation['match_result_id']);
            if ($checkIsDrawZero) {
                return $this->returnErrorBadRequest("Match is zero draw");
            }
            $getPlayer = $this->playerModel->getOneOrAllPlayer($validation['player_id']);
            if (!$getPlayer) {
                return $this->returnNotFound("Player");
            }
            $validation['team_id'] = $getPlayer->team_id;
            $goal = $this->goalModel->create($validation);
            return $this->responseFormatter->format(true, 'Goal created', $goal);
        });
    }


    public function update(Request $request, $id)
    {
        $validation = $this->requestValidation($request, $id);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $id) {
            $getPlayer = $this->playerModel->getOneOrAllPlayer($validation['player_id']);
            if (!$getPlayer) {
                return $this->returnNotFound("Player");
            }
            $validation['team_id'] = $getPlayer->team_id;
            $goal = $this->goalModel->getOneOrthAllGoal($id);
            if (!$goal) {
                return $this->returnNotFound("Goal");
            }

            $goal->update($validation);
            return $this->responseFormatter->format(true, 'Goal updated', $goal);
        });
    }

    public function destroy($id)
    {
        return $this->handleException(function () use ($id) {
            $goal = $this->goalModel->getOneOrthAllGoal($id);
            if (!$goal) {
                return $this->returnNotFound("Goal");
            }
            $goal->is_deleted = 1;
            $goal->save();
            return $this->responseFormatter->format(true, 'Goal deleted', $goal);
        });
    }
    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'match_result_id'  => 'required|between:36,36',
            'player_id'        => 'required|between:36,36',
            'minute'           => 'required|numeric',
        ];
        $rules['created_by'] = !$id ? 'required' : 'nullable' . '|string|between:36,36';
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        return $validation->validated();
    }
}
