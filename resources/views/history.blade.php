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
                        @if(isset($error))
                            <p>{{$error}}</p>
                        @else
                            @foreach($transactions as $t)
                                @if($t["type"]=="recieved")
                                    <p>{{$t["date"]}} c {{$t["from"]}} пришла сумма {{$t["cash_to"]}}{{$t["valute"]}}</p>
                                @else
                                    <p>{{$t["date"]}} на {{$t["to"]}} отправлена сумма {{$t["cash_from"]}}{{$t["valute"]}}</p>
                                @endif
                            @endforeach
                        @endif
                        <a href="{{url('/home')}}">На домашнюю</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
