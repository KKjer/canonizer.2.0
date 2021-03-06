@extends('layouts.app')
@section('content')
<div class="page-titlePnl">
    <h1 class="page-title">Login</h1>
</div>       	
<div class="right-whitePnl">
<div class="col-sm-5 margin-btm-2">
    <form action="{{ url('/login')}}" method="post">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <div class="form-group">
            <label for="firstname">Email</label>
            <input type="email" name="email" class="form-control" id="email" value="{{ old('email')}}">
            @if ($errors->has('email')) <p class="help-block">{{ $errors->first('email') }}</p> @endif
        </div>

        <div class="form-group">
            <label>Password </label>
            <input type="password" name="password" class="form-control" id="password" value="{{ old('password')}}">
            @if ($errors->has('password')) <p class="help-block">{{ $errors->first('password') }}</p> @endif
        </div>


        <div class="form-group">            
            <input type="checkbox" name="remember" class="form-control remember-me" id="remember"> Remember Me
            <a href="{{ url('/forgetpassword') }}" class="pull-right">Forget Password </a>
        </div>
        <button type="submit" id="submit" class="btn btn-login">Log in</button>
    </form>
 </div>   
</div>  <!-- /.right-whitePnl-->
@endsection