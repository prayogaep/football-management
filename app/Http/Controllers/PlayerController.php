<?php

namespace App\Http\Controllers;

use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayerController extends Controller
{
    protected $playerModel;

    public function __construct()
    {
        parent::__construct();
        $this->playerModel = new Player();
    }
    public function index()
    {
        return $this->handleException(function () {
            $players = $this->playerModel->getOneOrAllPlayer();
            return $this->responseFormatter->format(true, 'Success', $players);
        });
    }

    public function show($id)
    {
        return $this->handleException(function () use ($id) {
            $player = $this->playerModel->getOneOrAllPlayer($id);
            if (!$player) {
                return $this->returnNotFound("Player");
            }
            return $this->responseFormatter->format(true, 'Success', $player);
        });
    
    }
    public function store(Request $request)
    {
        $validation = $this->requestValidation($request);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation) {
            if (!$this->existNumberPlayer($validation['team_id'], $validation['number_player'])) {
                 return $this->returnErrorBadRequest("Number player already exists, in this team!");
            }
            $player = $this->playerModel->create($validation);
            return $this->responseFormatter->format(true, 'Player created', $player);
        });
    }


    public function update(Request $request, $id)
    {
        $validation = $this->requestValidation($request, $id);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $id) {
            if (!$this->existNumberPlayer($validation['team_id'], $validation['number_player'])) {
                 return $this->returnErrorBadRequest("Number player already exists, in this team!");
            }
            $player = $this->playerModel->getOneOrAllPlayer($id);
            if (!$player) {
                return $this->returnNotFound("Player");
            }
            $player->update($validation);
            return $this->responseFormatter->format(true, 'Player updated', $player);
        });
    }

    public function destroy($id)
    {
        return $this->handleException(function () use ($id) {
            $player = $this->playerModel->getOneOrAllPlayer($id);
            if (!$player) {
                return $this->returnNotFound("Player");
            }
            $player->is_deleted = 1;
            $player->save();
            return $this->responseFormatter->format(true, 'Player deleted', $player);
        });
    }

    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'name_player'       => 'required|string|max:255',
            'height_player'     => 'required|numeric',
            'weight_player'     => 'required|numeric',
            'position_player'   => 'required|string|max:255',
            'number_player'     => 'required|numeric',
            'team_id'           => 'required|string|max:255',
            'created_by'        => 'required|string|max:255',
        ];
        $rules['created_by'] = !$id ? 'required' : 'nullable'. '|string|between:36,36';
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        return $validation->validated();
    }

    private function existNumberPlayer($team_id, $number_player)
    {
        $player = $this->playerModel->where('team_id', $team_id)->where('number_player', $number_player)->where('is_deleted', 0)->first();
        $result = $player ? false : true;
        return $result;
    }
    
}
