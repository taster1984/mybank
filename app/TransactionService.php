<?php


namespace App;


use Facade\Ignition\Support\Packagist\Package;
use Illuminate\Support\Facades\DB;

class TransactionService
{

    private $curses;

    /**
     * Transaction constructor.
     */
    public function __construct()
    {
        $curs = file_get_contents("https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5");
        if (!$curs) {
            return false;
        }
        $curs = json_decode($curs, true);
        $this->curses = $curs;

    }

    public function send($from, $to, $cash, $v)
    {
        $this->recalc($from);
        $from = Account::where("number", "=", $from->number)->first();
        if ($from->quantity < $this->convert($v, $from->valute, $cash)) {
            return false;
        }
        $trans = new Transaction();
        $trans->from = $from->number;
        $trans->to = $to->number;
        $trans->cash_from = $this->convert($v, $from->valute, $cash);
        $trans->cash_to = $this->convert($v, $to->valute, $cash);
        $trans->save();
        $this->recalc($from);
        $this->recalc($to);
        return true;
    }

    private function recalc($acc)
    {
        $start = 0;
        if ($acc->valute == "UAH") {
            $start = 5000;
        }
        if ($acc->valute == "USD") {
            $start = 200;
        }
        if ($acc->valute == "EUR") {
            $start = 150;
        }
        $sends = DB::select(
            "select sum(cash_from) as sm from transactions where transactions.from=?",
            [$acc->number]
        );
        $recievs = DB::select(
            "select sum(cash_to) as sm from transactions where transactions.to=?",
            [$acc->number]
        );
        $res = 0;
        foreach ($recievs as $r){
            $res=$r->sm;
        }
        $s = 0;
        foreach ($sends as $send){
            $s=$send->sm;
        }
        $recievs = $res;
        $sends = $s;
        $acc->quantity = $start - $sends + $recievs;
        $acc->save();
    }

    private function convert($valFrom, $valTo, $cash)
    {
        if ($valFrom == $valTo) {
            return $cash;
        }
        if ($valFrom == "UAH") {
            foreach ($this->curses as $c) {
                if ($c["ccy"] == $valTo) {
                    $curs = ($c["buy"] + $c["sale"]) / 2;
                    return $cash / $curs;
                }
            }
        }
        if ($valTo == "UAH") {
            foreach ($this->curses as $c) {
                if ($c["ccy"] == $valFrom) {
                    $curs = ($c["buy"] + $c["sale"]) / 2;
                    return $cash * $curs;
                }
            }
        }
        $uahFrom = 0;
        foreach ($this->curses as $c) {
            if ($c["ccy"] == $valFrom) {
                $curs = ($c["buy"] + $c["sale"]) / 2;
                $uahFrom = $cash * $curs;
            }
        }
        return $this->convert("UAH", $valTo, $uahFrom);
    }
}
