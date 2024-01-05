<?php

namespace App\Http\Controllers;

use App\BulkSms;
use App\CustomerProfile;
use App\Employee;
use App\HrManager;
use App\AdvanceApplication;
use App\LoanRepayment;
use App\LoanRequest;
use App\LoanSchedule;
use App\UserGroup;
use App\Wallet;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->user()->user_group == 2) { //HR manager
            $hr = HrManager::where('user_id', auth()->user()->id)->first();

            if (is_null($hr))
                abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

            $employer = $hr->employer;

            if (!is_null($hr))
                $employerId = $hr->employer_id;
            else
                $employerId = 0;

            $total = Employee::where('employer_id',$employerId)->count();

            $totalAdvanceRequests = AdvanceApplication::where('employer_id',$employerId)->count();
            $pendingAdvanceRequests = AdvanceApplication::where('employer_id',$employerId)->where('hr_status','PENDING')->count();
            $approvedAdvanceRequests = AdvanceApplication::where('employer_id',$employerId)->where('hr_status','ACCEPTED')->count();

            $recent = AdvanceApplication::where('employer_id',$employerId)
                ->where('quicksava_status','PROCESSING')
                ->where('hr_status','PENDING')
                ->orderBy('id','asc')
                ->limit(5)
                ->get();

            return view('hr_dashboard')->with([
                'total'=>$total,
                'totalAdvanceRequests'=>$totalAdvanceRequests,
                'pendingAdvanceRequests'=>$pendingAdvanceRequests,
                'approvedAdvanceRequests'=>$approvedAdvanceRequests,
                'recent'=>$recent,
                'hr'=>$hr,
                'employer'=>$employer
            ]);
        }
        elseif (auth()->user()->user_group == 4) { //Customer
            abort(403);
        }
        elseif (auth()->user()->user_group == 5) { //Merchant Accounts Manager
            $hr = HrManager::where('user_id', auth()->user()->id)->first();

            if (is_null($hr))
                abort(403,"You have not been assigned to an organisation yet. Please contact system admin");

            $employer = $hr->employer;



            return view('hr_dashboard')->with([
                'hr'=>$hr,
                'employer'=>$employer
            ]);
        }
        else{
            if (auth()->user()->role->has_perm([25])){
                $topPersonal = Wallet::select( DB::raw('SUM(wallets.current_balance) as total'),
                    'users.name','wallets.id','wallets.id','wallets.active')
                    ->join('users','users.wallet_id','wallets.id')
                    ->where('active',true)
                    ->orderBy('total','desc')
                    ->limit(10)
                    ->groupBy('name')
                    ->groupBy('id')
                    ->get();

                $topPersonalFrozen = Wallet::select( DB::raw('SUM(wallets.current_balance) as total'),
                    'users.name','wallets.id','wallets.id','wallets.active')
                    ->join('users','users.wallet_id','wallets.id')
                    ->where('active',false)
                    ->orderBy('total','desc')
                    ->limit(10)
                    ->groupBy('name')
                    ->groupBy('id')
                    ->get();


                $overdue0 = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                    ->whereDate('payment_date', '>=', Carbon::now()->subDays(15))
                    ->whereDate('payment_date', '<=', Carbon::today())
                    ->get();
                $overdue0Sum = $overdue0->sum('scheduled_payment') - $overdue0->sum('actual_payment_done');

                $overdue1 = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                    ->whereDate('payment_date', '>=', Carbon::now()->subDays(30))
                    ->whereDate('payment_date', '<', Carbon::now()->subDays(15))
                    ->get();
                $overdue1Sum = $overdue1->sum('scheduled_payment') - $overdue1->sum('actual_payment_done');

                $overdue2 = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                    ->whereDate('payment_date', '>=', Carbon::now()->subDays(60))
                    ->whereDate('payment_date', '<', Carbon::now()->subDays(30))
                    ->get();
                $overdue2Sum = $overdue2->sum('scheduled_payment') - $overdue2->sum('actual_payment_done');

                $overdue3 = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                    ->whereDate('payment_date', '>=', Carbon::now()->subDays(90))
                    ->whereDate('payment_date', '<', Carbon::now()->subDays(60))
                    ->get();
                $overdue3Sum = $overdue3->sum('scheduled_payment') - $overdue3->sum('actual_payment_done');

                $overdue4 = LoanSchedule::whereIn('status',['PARTIALLY_PAID','UNPAID'])
                    ->whereDate('payment_date', '<', Carbon::now()->subDays(90))
                    ->get();
                $overdue4Sum = $overdue4->sum('scheduled_payment') - $overdue4->sum('actual_payment_done');

                $overdueLoans["Over 0 - 15 days"] = $overdue0Sum;
                $overdueLoans["Over 15 - 30 Days"] = $overdue1Sum;
                $overdueLoans["Over 30 - 60 days"] = $overdue2Sum;
                $overdueLoans["Over 60 - 90 days"] = $overdue3Sum;
                $overdueLoans["Over 90 days"] = $overdue4Sum;


                return view('admin_dashboard')->with([
                    'overdueLoans'=>$overdueLoans,
                    'topPersonal'=>$topPersonal,
                    'topPersonalFrozen'=>$topPersonalFrozen,
                ]);
            }else{
                return view('normal_dashboard');
            }
        }
    }


    public function get_total_customers()
    {
        $message = CustomerProfile::count();

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_checkoff_customers()
    {
        $message = CustomerProfile::where('is_checkoff', true)->count();

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_approved_today()
    {
        $message = LoanRequest::whereDate('created_at', Carbon::now())->where('approval_status','APPROVED')->sum('amount_requested');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_paid_today()
    {
        $message = LoanRepayment::whereDate('created_at', Carbon::now())->sum('amount_repaid');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_total_disbursed()
    {
        $message = LoanRequest::where('approval_status','APPROVED')->sum('amount_requested');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_total_repaid()
    {
        $message = LoanRepayment::sum('amount_repaid');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_due_today()
    {
        $message = LoanSchedule::where('status','UNPAID')->whereDate('payment_date', Carbon::now())->sum('scheduled_payment');

        foreach (LoanSchedule::where('status','PARTIALLY_PAID')->whereDate('payment_date', Carbon::now())->get() as $todayPartiallypaid){
            $message += ($todayPartiallypaid->scheduled_payment - $todayPartiallypaid->actual_payment_done);
        }

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_overdue()
    {
        $message = LoanSchedule::where('status','UNPAID')->whereDate('payment_date', '<', Carbon::now())->sum('scheduled_payment');

        foreach (LoanSchedule::where('status','PARTIALLY_PAID')->whereDate('payment_date', '<', Carbon::now())->get() as $todayPartiallypaid){
            $message += ($todayPartiallypaid->scheduled_payment - $todayPartiallypaid->actual_payment_done);
        }

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }
    public function get_wallets_amount()
    {
        $message = Wallet::sum('current_balance');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }

    public function get_total_wallet_withdrawals()
    {
        $message = WalletTransaction::where('transaction_type','DR')
            ->where('narration','!=','Withdrawal charge')
            ->sum('amount');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }

    public function get_todays_wallet_withdrawals()
    {
        $message = WalletTransaction::where('transaction_type','DR')
            ->where('narration','!=','Withdrawal charge')
            ->whereDate('created_at',Carbon::now())
            ->sum('amount');

        echo json_encode(["error" => false, "message" => number_format($message)]);
        return;
    }







}
