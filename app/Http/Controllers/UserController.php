<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    public function index()
    {
        return $this->handleException(function () {
            $users = $this->userModel->getOneOrAllUser();
            return $this->responseFormatter->format(true, 'Success', $users);
        });
    }

    public function show($id)
    {
        return $this->handleException(function () use ($id) {
            $user = $this->userModel->getOneOrAllUser($id);
            if (!$user) {
                return $this->returnNotFound("User");
            }
            return $this->responseFormatter->format(true, 'Success', $user);
        });
    }

    public function store(Request $request)
    {
        $validation = $this->requestValidation($request);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $request) {
            $isFirstUser = $request->is('api/firstUser');
            if ($isFirstUser && $this->userModel->count() > 0) {
                return $this->responseFormatter->format(false, 'Cannot create user. Users already exist.', null, $this->responseFormatter->statusCodeBadRequest);
            }
            $userModel = $this->userModel->create($validation);
            $message = $isFirstUser ? 'First User Created' : 'User Created';
            return $this->responseFormatter->format(true, $message, $userModel);
        });
    }

    public function update(Request $request, $id)
    {
        $validation = $this->requestValidation($request, $id);
        if (isset($validation['errors'])) {
            return $this->returnErrorRequest($validation['errors']);
        }
        return $this->handleException(function () use ($validation, $id) {
            $userModel = $this->userModel->getOneOrAllUser($id);
            if (!$userModel) {
                return $this->returnNotFound("User");
            }
            $userModel->update($validation);
            return $this->responseFormatter->format(true, 'User Updated', $userModel);
        });
    }

    public function destroy($id)
    {
        return $this->handleException(function () use ($id) {
            $user = $this->userModel->getOneOrAllUser($id);
            if (!$user) {
                return $this->returnNotFound("User");
            }
            $user->is_deleted = 1;
            $user->save();
            return $this->responseFormatter->format(true, 'User Deleted', $user);
        });
    }


    private function requestValidation(Request $request, $id = null)
    {
        $rules = [
            'name'      => 'required|string|max:255',
            'email'     => [
                'required',
                'email',
                Rule::unique('users')->ignore($id),
            ],
            'password'  => 'required|string|min:8',
        ];
        if ($this->userModel->count() > 0 && !$id) {
            $rules['created_by'] = 'required|string|max:255';
        }
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return ['errors' => $validation->errors()->toArray()];
        }

        $validatedData = $validation->validated();
        $validatedData['password'] = Hash::make($validatedData['password']);
        if (!$id) {
            $validatedData['created_by'] = $request->has('created_by') ? $request->created_by : "FIRSTUSER";
        }
        return $validatedData;
    }
}
