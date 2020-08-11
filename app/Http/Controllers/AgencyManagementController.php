<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Traits\ResponseHandler;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class AgencyManagementController extends Controller
{
    /**
    * @Trait Traits\ResponseHandler
    */
    use ResponseHandler;

    public function downline(Request $request)
    {
        return view('agency-management.index');
    }

    public function create(Request $request)
    {
        $id = $request->input('id');
        return view('agency-management.create');
    }

    /**
     * @param \Illuminate\Http\Request $request
     * Saves a new user to DB
     */

    public function store(Request $request)
    {
        $messages = [
            'username.required' => 'Please enter your username',
            'username.unique' => 'This username is already taken',
            'password.required' => 'Please enter your password',
            'confirm_password.confirmed' => 'Please type your password again',
            'status.required' => 'Please enter status for user',
            'credit_limt.required' => 'Please enter status for user',
            'credit_limt.integer' => 'Please enter a numeric value',
        ];
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'password' => 'required',
            'confirm_password' => 'required',
            'status' => 'required',
            'credit_limt' => 'required|integer'

        ], $messages);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors());
        }

        try {
            $user = new User();
            $user->username = $request->input('username');
            $user->password = Hash::make($request->input('password'));
            $user->status = $request->input('status');
            $user->credit_limit = $request->input('credit_limt');
            $user->pt_own = $request->input('pt_own');
            $user->pt_down = $request->input('pt_down');
            $user->parent_id = Auth::user()->id;
            $user->notes = $request->input('notes');
            $user->save();
            $this->updateAllParentIds($user);
            return $this->successResponse([], route('agency-management-create-user'), 'User successfully created');
        } catch (Exception $e) {
            return $this->errorResponse([$e->getMessage()], null, $e->getCode());
        }
    }

    /**
     * @param \Illuminate\Http\Request $request
     * Returns a list of all sub users
     */
    public function getSubUsers(Request $request)
    {

        if ($request->has('pid')) {
            $parent_id = $request->input('pid');
        } else {
            $parent_id = Auth::user()->id;
        }
        $users = User::where('parent_id', $parent_id)->orderBy('id', 'desc')->paginate();
        return $this->response($users, 'agency-management._index-users-list', true);
    }
}
