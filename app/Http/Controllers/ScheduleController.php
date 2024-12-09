<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    protected $scheduleModel;

    public function __construct()
    {
        parent::__construct();
        $this->scheduleModel = new Schedule();
    }
    public function index()
    {
        return $this->handleException(function () {
            $schedules = $this->scheduleModel->getOneOrAllSchedule();
            return $this->responseFormatter->format(true, 'Success', $schedules);
        });
    }

    public function show($id)
    {
        return $this->handleException(function () use ($id) {
            $schedule = $this->scheduleModel->getOneOrAllSchedule($id);
            if (!$schedule) {
                return $this->returnNotFound("Schedule");
            }
            return $this->responseFormatter->format(true, 'Success', $schedule);
        });
    
    }
    public function reports($id = null)
    {
        return $this->handleException(function () use ($id) {
            $schedule = $this->scheduleModel->getOneOrAllScheduleReport($id);
            if (!$schedule) {
                return $this->returnNotFound("Schedule");
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
            if ($this->scheduleModel->isScheduleConflict($validation)) {
                return $this->returnErrorBadRequest("Schedule conflict");
            }
            $schedule = $this->scheduleModel->create($validation);
            return $this->responseFormatter->format(true, 'Schedule created', $schedule);
        });
    }


    public function update(Request $request, $id)
    {
        $validation = $this->requestValidation($request, $id);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $id) {
            $schedule = $this->scheduleModel->getOneOrAllSchedule($id);
            if (!$schedule) {
                return $this->returnNotFound("Schedule");
            }

            if ($this->scheduleModel->isScheduleConflict($validation)) {
                return $this->returnErrorBadRequest("Schedule conflict");
            }
            $schedule->update($validation);
            return $this->responseFormatter->format(true, 'Schedule updated', $schedule);
        });
    }

    public function destroy($id)
    {
        return $this->handleException(function () use ($id) {
            $schedule = $this->scheduleModel->getOneOrAllSchedule($id);
            if (!$schedule) {
                return $this->returnNotFound("Schedule");
            }
            $schedule->is_deleted = 1;
            $schedule->save();
            return $this->responseFormatter->format(true, 'Schedule deleted', $schedule);
        });
    }
    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'home_team_id'      => 'required|between:36,36',
            'away_team_id'      => 'required|between:36,36',
            'date_schedule'     => 'required|date',
            'time_schedule'     => 'required|string|max:8',
        ];
        $rules['created_by'] = !$id ? 'required' : 'nullable'. '|string|between:36,36';
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        return $validation->validated();
    }
}
