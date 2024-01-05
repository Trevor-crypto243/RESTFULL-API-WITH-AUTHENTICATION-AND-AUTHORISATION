<?php

namespace App\Http\Controllers\API;

use App\BankAccount;
use App\BankBranch;
use App\Http\Controllers\Controller;
use App\Http\Resources\AccountsCollection;
use App\Http\Resources\GenericResource;
use App\Wallet;
use App\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class WalletController extends Controller
{

    public function get_transactions()
    {
        return new AccountsCollection(WalletTransaction::where('wallet_id',auth()->user()->wallet_id)->orderBy('id', 'desc')->paginate(10));
    }

    public function get_company_wallet($wallet_id)
    {
        $wallet = Wallet::find($wallet_id);

        if (is_null($wallet))
            abort(404,"Invalid wallet");

        return new GenericResource($wallet);
    }

    public function get_company_transactions($wallet_id)
    {
        return new AccountsCollection(WalletTransaction::where('wallet_id',$wallet_id)->orderBy('id', 'desc')->paginate(10));
    }

}
