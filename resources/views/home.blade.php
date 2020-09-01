@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Dashboard') }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif
                        <p>Здравствуйте, {{Auth::user()->name}}</p>
                        <p>Ваши счета:</p>
                        @if (Auth::user()->accounts()->count()>0)
                            @foreach(Auth::user()->accounts()->get() as $account)
                                <p>Валюта: {{ $account->valute }} Номер счета: {{$account->number}}
                                    Балланс: {{$account->quantity}}</p>

                            @endforeach
                        @else
                            <p>У вас пока нет открытых счетов</p>
                        @endif
                        <form action="{{ url('/home') }}" method="POST">
                            @csrf
                            <p>Валюта: <select name="v">
                                    <option selected value="UAH">UAH</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                </select>
                                <input type="submit" value="Открыть счет"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
