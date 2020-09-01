<?php

namespace App\Http\Controllers;

use App\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        return view('home');
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function post(Request $request)
    {
        $v = $request->get("v");
        if ($v != "UAH" && $v != "USD" && $v != "EUR") {
            return view('home');
        }
        $userId = Auth::user()->id;
        $number = random_int(1000, 9999);
        $start = 0;
        switch ($v) {
            case "UAH":
                $start = 5000;
                break;
            case "USD":
                $start = 200;
                break;
            case "EUR":
                $start = 150;
                break;
        }
        $newAccount = new \App\Account();
        $newAccount->user_id = $userId;
        $newAccount->valute = $v;
        $newAccount->number = $number;
        $newAccount->quantity = $start;
        $newAccount->save();
        return view('home');
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function send(Request $request)
    {
        $from = $request->get("from");
        $to = $request->get("to");
        $cash = $request->get("cash");
        $accFrom = Auth::user()->accounts()->where('number', '=', $from)->first();
        if (!$accFrom) {
            return view('send', ["error" => "Счет отправителя указан неверно."]);
        }
        $accTo = \App\Account::where("number", $to)->first();
        if (!$accTo) {
            return view('send', ["error" => "Счет получателя указан неверно."]);
        }
        if ($cash<=0){
            return view('send', ["error" => "Сумма перевода указана неверно"]);
        }
        return view('send');
    }

}
