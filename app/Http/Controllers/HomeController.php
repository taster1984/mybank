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
        $valute = $request->get("valute");
        $accFrom = Auth::user()->accounts()->where('number', '=', $from)->first();
        if (!$accFrom) {
            return view('send', ["error" => "Счет отправителя указан неверно."]);
        }
        $accTo = \App\Account::where("number", $to)->first();
        if (!$accTo) {
            return view('send', ["error" => "Счет получателя указан неверно."]);
        }
        if ($cash <= 0) {
            return view('send', ["error" => "Сумма перевода указана неверно"]);
        }
        if ($valute != "UAH" && $valute != "USD" && $valute != "EUR") {
            return view('send', ["error" => "Валюта перевода указана неверно"]);
        }
        $transaction = new \App\TransactionService();
        if (!$transaction->send($accFrom, $accTo, $cash, $valute)) {
            return view('send', ["error" => "Ошибка перевода. Возможно недостаточно средств."]);
        }
        return view('send');
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function close(Request $request)
    {
        $acc = $request->get("number");
        $acc = Auth::user()->accounts()->where("number", "=", $acc)->first();
        if ($acc) {
            $acc->delete();
        }
        return redirect('home');
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function history(Request $request)
    {
        $number = $request->get("number");
        $account = Auth::user()->accounts()->where("number", "=", $number)->first();
        if (!$account) {
            return view("history", ["error" => "Неверный номер счета"]);
        }
        $transactions = \App\Transaction::where("from_number", "=", $number)->orWhere("to_number", "=", $number)->get();
        $trs = [];
        foreach ($transactions as $t) {
            if ($t->from_number == $number) {
                $tr["type"] = "sended";
                $tr["cash_from"] = round($t->cash_from,2);
                $tr["to"] = $t->to_number;
                $tr["date"] = $t->updated_at;
                $tr["valute"]=$account->valute;
                $trs[]=$tr;
            }
            if ($t->to_number == $number) {
                $tr["type"]="recieved";
                $tr["cash_to"]=round($t->cash_to,2);
                $tr["from"]=$t->from_number;
                $tr["date"]=$t->updated_at;
                $tr["valute"]=$account->valute;
                $trs[]=$tr;
            }
        }

        return view("history",["transactions"=>$trs]);

    }


}
