@extends('layouts.master')
@section('content')

<div class="container-fluid">

    <div class="row flex-column justify-content-center text-center align-items-center mt-4 pb-5">
        <h3 class="pb-4">BTC Price subscription</h3>

        <div class="section contact d-flex w-100">
            <div class="col-md-6 col-xs-12 form">
                <form action="subscribe" method="POST">
                    @csrf
                    <div class="form-group text-left">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="email" placeholder="email" value="webi.aleks@gmail.com">
                    </div>
                    <div class="form-group text-left">
                        <label for="target_price">Price</label>
                        <input type="text" class="form-control" id="target_price" name="target_price" placeholder="target_price" value="95000">
                    </div>

                    <select name="symbol" class="form-control">
                        @foreach ($symbols as $symbol)
                              <option value={{ $symbol }}> {{ $symbol }}</option>
                        @endforeach
                    </select>

                    <input type="hidden" name="type" value="price">
                    <button type="submit" class="btn primary-bg mt-3"><strong>Send</strong></button>

                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div>{{$error}}</div>
                        @endforeach
                    @endif

                    @if (Session::has('message'))
                        <div class="alert alert-success">
                            <ul>
                                <li>{{ Session::get('message') }}</li>
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>


    <div class="row flex-column justify-content-center text-center align-items-center mt-4 pb-5">
        <h3 class="pb-4">BTC Percentage subscription</h3>

        <div class="section contact d-flex w-100">
            <div class="col-md-6 col-xs-12 form">
                <form action="subscribe" method="POST">
                    @csrf
                    <div class="form-group text-left">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" aria-describedby="email" placeholder="email">
                    </div>
                    <div class="form-group text-left">
                        <label for="percent_change">Percentage</label>
                        <input type="number" class="form-control" id="percent_change" name="percent_change" aria-describedby="percentage" placeholder="percentage">
                    </div>

                    <div class="form-group text-left">
                        <label for="time_interval">Time period</label>
                        <select name="time_interval" class="form-control">
                            @foreach ($timeIntervals as $key => $interval )
                                <option value={{ $interval }}> {{ $interval }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group text-left">
                        <label for="symbol">Available symbols</label>
                        <select name="symbol" class="form-control">
                            @foreach ($symbols as $symbol)
                                <option value={{ $symbol }}> {{ $symbol }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="type" value="percentage">

                    <button type="submit" class="btn primary-bg mt-3"><strong>Send</strong></button>

                    @if ($errors->any())
                        @foreach ($errors->all() as $error)
                            <div>{{$error}}</div>
                        @endforeach
                    @endif

                    @if (Session::has('messagePercentage'))
                        <div class="alert alert-success">
                            <ul>
                                <li>{{ Session::get('messagePercentage') }}</li>
                            </ul>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>

</div>

@stop
