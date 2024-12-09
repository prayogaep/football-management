<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TeamController extends Controller
{
    protected $teamModel;

    public function __construct()
    {
        parent::__construct();
        $this->teamModel = new Team();
    }
    public function index()
    {
        return $this->handleException(function () {
            $teams = $this->teamModel->getOneOrAllTeam();
            return $this->responseFormatter->format(true, 'Success', $teams);
        });
    }

    public function show($id)
    {
        return $this->handleException(function () use ($id) {
            $team = $this->teamModel->getOneOrAllTeam($id);
            if (!$team) {
                return $this->returnNotFound("Team");
            }
            return $this->responseFormatter->format(true, 'Success', $team);
        });
    
    }
    public function store(Request $request)
    {
        $validation = $this->requestValidation($request);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation) {
            if (isset($validation['logo_team']) && $validation['logo_team'] instanceof \Illuminate\Http\UploadedFile) {
                $validation['logo_team'] = $this->uploadFileToStorage($validation['logo_team'], $validation['name_team']);
            }

            $team = $this->teamModel->create($validation);
            return $this->responseFormatter->format(true, 'Team created', $team);
        });
    }


    public function update(Request $request, $id)
    {
        $validation = $this->requestValidation($request, $id);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $id) {
            $team = $this->teamModel->getOneOrAllTeam($id);
            if (!$team) {
                return $this->returnNotFound("Team");
            }
            if (isset($validation['logo_team']) && $validation['logo_team'] instanceof \Illuminate\Http\UploadedFile) {
                $validation['logo_team'] = $this->uploadFileToStorage($validation['logo_team'], $validation['name_team'], $id);
            }
            $team->update($validation);
            return $this->responseFormatter->format(true, 'Team updated', $team);
        });
    }

    public function destroy($id)
    {
        return $this->handleException(function () use ($id) {
            $team = $this->teamModel->getOneOrAllTeam($id);
            if (!$team) {
                return $this->returnNotFound("Team");
            }
            $team->is_deleted = 1;
            $team->save();
            return $this->responseFormatter->format(true, 'Team deleted', $team);
        });
    }

    private function uploadFileToStorage($file, $folder_name, $id = null)
    {
        $replaceFolderName = str_replace(' ', '-', $folder_name);
        $lowerCaseFolderName = strtolower($replaceFolderName);
        $relativePath = 'teams/' . $lowerCaseFolderName;
        $absolutePath = base_path('public/uploads/' . $relativePath);

        if (!file_exists($absolutePath)) {
            mkdir($absolutePath, 0777, true);
        }


        $name = time() . '.' . $file->getClientOriginalExtension();
        $file->move($absolutePath, $name);


        $pathFileUpload = $relativePath . '/' . $name;
        if ($id) {
            $team = $this->teamModel->where('id', $id)->first();
            if ($team) {
                $existingFile = str_replace(env('APP_URL') . "/upload" . "/", '', $team->logo_team);
                unlink(base_path('public/uploads/' . $existingFile));
            }
        }
        return env('APP_URL') . "/upload/$pathFileUpload";
    }
    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'name_team'      => 'required|string|max:255',
            'since_team'     => 'required|numeric',
            'address_team'   => 'required|string|between:1,500',
            'base_team_city' => 'required|string|max:255',
        ];
        $rules['logo_team'] = !$id ? 'required' : 'nullable' .'|mimes:jpeg,jpg,png|max:2048';
        $rules['created_by'] = !$id ? 'required' : 'nullable'. '|string|between:36,36';
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        return $validation->validated();
    }
}
