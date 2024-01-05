<?php

namespace App\Http\Controllers;

use App\AuditTrail;
use App\CustomerProfile;
use App\Notifications\UserCreated;
use App\User;
use App\UserGroup;
use App\UserPermission;
use App\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;


class UserController extends Controller
{
    protected $random_pass;
    private $_passed = false;



    public function __construct() {
        $this->middleware(['auth']);
    }


    public function users() {
        $user_roles = UserGroup::all();
        // $users = User::orderBy('id','desc')->paginate(20);
        $users = User::whereNull('deleted_at')->orderBy('id', 'desc')->paginate(20);


        return view('users.index')->with([
            'id_no'=> ' ',
            'phone_no'=> ' ',
            'user_roles' => $user_roles,
            'users' => $users,
            'edit' => false,
        ]);
    }

    public  function search_users(Request  $request){

        $user_roles = UserGroup::all();

        $users = User::where('id_no', $request->id_no)
            ->orWhere('phone_no', $request->phone_no)
            ->orderBy('id','desc')
            ->paginate(20);



        return view('users.index')
            ->with([
                'id_no'=>is_null($request->id_no) ? ' ' : $request->id_no,
                'phone_no'=>is_null($request->phone_no) ? ' ' : $request->phone_no,
                'user_roles' => $user_roles,
                'users' => $users,
                'edit' => false,
            ]);
    }

    public function register_user(Request $request)
    {
        $this->validate($request, [
            'user_group' => 'required',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_no' => 'required|max:255|unique:users,phone_no',
            'id_no' => 'required|max:255|unique:users,id_no',
            'name' => 'required',
            'surname' => 'required',
        ]);

        $exists = User::where('phone_no', ltrim($request->phone_no,"+"))->first();

        if (!is_null($exists)){
            request()->session()->flash('warning', 'The phone number has already been taken');
            return redirect()->back();
        }


        $this->random_pass = $this->randomPassword();

        DB::transaction(function() use($request) {

            $wallet = new Wallet();
            $wallet->current_balance = 0.0;
            $wallet->previous_balance = 0.0;
            $wallet->saveOrFail();


            $user = new User();
            $user->name = $request->name;
            $user->surname = $request->surname;
            $user->wallet_id = $wallet->id;
            $user->user_group = $request->user_group;
            $user->phone_no = ltrim($request->phone_no,"+");
            $user->email = $request->email;
            $user->id_no = $request->id_no;
            $user->password = bcrypt($this->random_pass);

            if ($user->saveOrFail()){

                if ($request->user_group == 4){
                    //customer. insert into customers
                    $customer = new CustomerProfile();
                    $customer->user_id = $user->id;
                    $customer->saveOrFail();
                }

                $user->notify(new UserCreated($this->random_pass));
                send_sms($user->phone_no,"Your account on Quicksava Credit has been created. Use your email to log in. Your password ".$this->random_pass);
                Session::flash("success", "User has been created");
            }
        });

        return redirect('/users');
    }

    public function edit_user($id)
    {
        $user = User::find($id);
        return $user;
    }

    public function delete($id)
    {
        $user = User::find($id);
        return $user;
    }
    public function delete_user(Request $request)
    {
        $id = $request->id_detail;        
    
        $user = User::find($id);
        $user->deleted_at = Carbon::now()->toDateTimeString();
        $user->deleted_by_id = auth()->user()->id;
        $user->saveOrFail();

        //Saving the details in the audit trail table
        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => auth()->user()->name.'  Updated deletion status for User with id '.$id        
        ]);

        request()->session()->flash('success', 'User has been deleted.');
        return redirect('/users');
    }


    public function update_user(Request $request)
    {
        $data = request()->validate([
            'id' => 'required',
            'user_group' => 'required',
            'email' => ['required','email','max:255',\Illuminate\Validation\Rule::unique('users')->ignore($request->id)],
            'phone_no' => ['required','max:255'],
            'id_no' => ['required','max:255',\Illuminate\Validation\Rule::unique('users')->ignore($request->id)],
            'name' => 'required',
            'surname' => 'required',
        ]);

        if (!is_null(User::where('phone_no',ltrim($request->phone_no,"+"))->where('id','!=',$request->id)->first())){
            request()->session()->flash('warning', 'The phone number has already been taken');
            return redirect()->back();
        }

        $updateData = [
            'id' => $request->id,
            'user_group' => $request->user_group,
            'email' =>$request->email,
            'phone_no' =>ltrim($request->phone_no,"+"),
            'id_no' => $request->id_no,
            'name' => $request->name,
            'surname' => $request->surname,
        ];

        $usr = User::find($request->id);

        AuditTrail::create([
            'created_by' => auth()->user()->id,
            'action' => auth()->user()->name.' - Updated profile details for user ID #'.$request->id.'. Old values:: E-MAIL-'.
                $usr->email.", PHONE - ".$usr->phone_no.", IDNO - ".$usr->id_no.", NAME - ".$usr->name.", SURNAME - ".$usr->surname.". New Values:: E-MAIL - ".
                $request->email.", PHONE - ".ltrim($request->phone_no,"+").", IDNO - ".$request->id_no.", NAME - ".$request->name.", SURNAME - ".
                $request->surname
        ]);

        User::where('id', $request->id)->update($updateData);



        request()->session()->flash('success', 'User has been updated.');
        return redirect('/users');
    }



    public function randomPassword()
    {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    public function myProfile() {
        $user = auth()->user();

        $photo = asset('assets/img/default-avatar.png');


        $profile_data = [
            'user' => $user,
            'photo' => $photo,
        ];

        return view('auth.my-profile', $profile_data);
    }
    public function editProfile() {
        $user = auth()->user();
        $photo = asset('assets/img/default-avatar.png');

        if ($user->photo) {
            $photo = Storage::disk('public')->url($user->photo);
        }



        return view('auth.edit-profile', [
            'user' => $user,
            'photo' => $photo
        ]);
    }
    public function updateProfile(Request $request) {
        $user = $request->user();
        request()->session()->flash('update_profile', true);

        $this->validate($request, [
            'surname' => 'required',
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'id_no' => 'required|unique:users,id_no,'.$user->id,
            'phone_no' => 'required|unique:users,phone_no,'.$user->id,
        ]);


        DB::transaction(function() use($request, $user) {

            $user->surname = $request->surname;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->id_no = $request->id_no;
            $user->phone_no = $request->phone_no;
            $user->update();


            $this->_passed = true;
        });

        if ($this->_passed)
            request()->session()->flash('success', 'Profile has been updated.');
        else
            request()->session()->flash('warning', 'Failed to update profile!');

        return redirect('edit-profile');
    }
    public function updatePassword(Request $request) {
        $request->session()->flash('update_password', true);

        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|confirmed|min:6'
        ]);

        $check = auth()->validate(['email' => $request->user()->email, 'password' => request('current_password')]);

        if($check) {
            User::where('id', $request->user()->id)->update(['password' => bcrypt(request('password'))]);
            $request->session()->flash('success', 'You have changed your password.');
        } else {
            $request->session()->flash('warning', 'The current password is incorrect, please try again.');
        }

        return redirect('edit-profile');
    }

    public function user_groups()
    {
        $user_groups = UserGroup::all();

        return view('users.user_groups')->with([
            'user_groups' => $user_groups,
        ]);
    }
    public function new_user_group(Request $request)
    {
        $this->validate($request, [
            'group_name' => 'required|unique:user_groups,name',
        ]);


        $user_group = new UserGroup();
        $user_group->name = $request->group_name;
        $user_group->saveOrFail();


        Session::flash("success", "Group has been created");


        return redirect()->back();
    }
    public function get_group_details($id)
    {

        return UserGroup::find($id);
    }
    public function update_group_details(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:user_groups,id',
            'group_name' => 'required|unique:user_groups,name',
        ]);


        $user_group = UserGroup::find($request->id);
        $user_group->name = $request->group_name;
        $user_group->update();


        Session::flash("success", "Group has been updated");


        return redirect()->back();
    }
    public function delete_group(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|exists:user_groups,id',
        ]);


        $user_group = UserGroup::find($request->id);
        $name = $user_group->name;

        if ($user_group->delete()){
            AuditTrail::create([
                'created_by' => auth()->user()->id,
                'action' => 'Deleted loan product FEE ('.$name.') with ID '.$request->id,
            ]);
        }



        Session::flash("success", "Group has been deleted");


        return redirect()->back();
    }

    public function user_group_details($_id)
    {
        $ug = UserGroup::find($_id);

        if(is_null($ug))
            abort(404);

        $users = User::where('user_group',$_id)->get();

        $user_permissions = UserPermission::where('group_id',$_id)->get();



        return view('users.group_details')->with([
            'group' => $ug,
            'users' => $users,
            'user_permissions' => $user_permissions
        ]);

    }
    public function userGroupDetailsDT($_id) {

        $users = User::where('user_group',$_id)->get();

        return DataTables::of($users)
            ->editColumn('id', function ($user) {
                return $user->id;
//                return '<a href="'.url('users/details/'.$user->id) .'" title="View User" >#'. $user->id .' </a>';
            })

            ->addColumn('group',function ($user) {
                return optional($user->role)->name;
            })
            ->editColumn('name',function ($user) {
                return $user->name;
            })
            ->editColumn('email',function ($user) {
                return $user->email;
            })

            ->addColumn('actions', function($user) {
                $actions = '<div class="pull-left">';
//                $actions .= '<a title="Edit User" class="btn btn-link btn-sm btn-warning btn-just-icon"><i class="material-icons">edit</i> </a>';
//                $actions .= '<a title="View User" href="'.url('users/details/'.$user->id) .'" class="btn btn-info btn-sm pull-right"><i class="material-icons">list</i> View</a>';
//                $actions .= '<a title="Manage User" class="btn btn-link btn-sm btn-info btn-just-icon"><i class="material-icons">dvr</i> </a>';
                $actions .= '</div>';

                return $actions;
            })
            ->rawColumns(['id','actions'])
            ->make(true);

    }
    public function add_group_permission(Request $request)
    {
        $this->validate($request, [
            'permission' => 'bail|required',
            'group_id' => 'bail|required',
        ]);

        foreach ($_POST['permission'] as $perm) {
            $userPermission = new userPermission();
            $userPermission->group_id = $request->group_id;
            $userPermission->permission_id = $perm;
            $userPermission->saveOrFail();
        }

        request()->session()->flash('success', 'Permissions added successfully');

        return redirect()->back();
    }
    public function delete_group_permission($group_id)
    {
        $userPermission = UserPermission::find($group_id);
        if ($data = $userPermission->delete()) {
            request()->session()->flash("success", "Permission deleted successfully.");
        }
        return redirect()->back();
    }

    public function audit_logs() {
        $auditLogs = AuditTrail::orderBy('id','desc')->paginate(20);

        return view('users.audit_logs')->with([
            'auditLogs' => $auditLogs
        ]);
    }
}
