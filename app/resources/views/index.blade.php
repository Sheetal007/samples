@extends('layouts.app')
@section('title', setTitle('Agent listing'))

@section('content')
<!-- center contbx starts-->

@include('layouts.elements.app.partials.body.marque-text')
@include('layouts.elements.app.partials.body.balance-holder')
@inject('users', 'App\Http\Controllers\AgencyManagementController')
<div class="users-info" id="loaduserinfo" >
    {!! $users->getSubUsers(app('request')) !!}
</div>

@stop
