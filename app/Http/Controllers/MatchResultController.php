<?php

namespace App\Http\Controllers;

use App\Models\MatchResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MatchResultController extends Controller
{
    protected $matchResultModel;

    public function __construct()
    {
        parent::__construct();
        $this->matchResultModel = new MatchResult();
    }
    public function index()
    {
        return $this->handleException(function () {
            $schedules = $this->matchResultModel->getOneOrthAllMatchResult();
            return $this->responseFormatter->format(true, 'Success', $schedules);
        });
    }

    public function show($id)
    {
        return $this->handleException(function () use ($id) {
            $schedule = $this->matchResultModel->getOneOrthAllMatchResult($id);
            if (!$schedule) {
                return $this->returnNotFound("Match result");
            }
            return $this->responseFormatter->format(true, 'Success', $schedule);
        });
    }
    
    public function store(Request $request)
    {
        $validation = $this->requestValidation($request);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation) {
            $schedule = $this->matchResultModel->create($validation);
            return $this->responseFormatter->format(true, 'Match result created', $schedule);
        });
    }


    public function update(Request $request, $id)
    {
        $validation = $this->requestValidation($request, $id);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $id) {
            $schedule = $this->matchResultModel->getOneOrthAllMatchResult($id);
            if (!$schedule) {
                return $this->returnNotFound("Match result");
            }
            $schedule->update($validation);
            return $this->responseFormatter->format(true, 'Match result updated', $schedule);
        });
    }

    public function destroy($id)
    {
        return $this->handleException(function () use ($id) {
            $schedule = $this->matchResultModel->getOneOrthAllMatchResult($id);
            if (!$schedule) {
                return $this->returnNotFound("Match result");
            }
            $schedule->is_deleted = 1;
            $schedule->save();
            return $this->responseFormatter->format(true, 'Match result deleted', $schedule);
        });
    }
    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'schedule_id'       => 'required|between:36,36',
            'home_team_score'   => 'required|numeric',
            'away_team_score'   => 'required|numeric',
            'notes'             => 'nullable|string|between:1,500',
        ];
        $rules['created_by'] = !$id ? 'required' : 'nullable' . '|string|between:36,36';
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        return $validation->validated();
    }
}
