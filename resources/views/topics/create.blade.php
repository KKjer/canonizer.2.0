@extends('layouts.app')
@section('content')
<div class="page-titlePnl">
    <h1 class="page-title">Create Topic</h1>
</div> 

@if(Session::has('error'))
<div class="alert alert-danger">
    <strong>Error!</strong>{{ Session::get('error')}}    
</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success">
    <strong>Success!</strong>{{ Session::get('success')}}    
</div>
@endif


<div class="right-whitePnl">
   <div class="row col-sm-12 justify-content-between">
    <div class="col-sm-6 margin-btm-2">
        <form action="{{ url('/topic')}}" method="post">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div class="form-group">
                <label for="camp_name">Nick Name</label>
                <select name="nick_name" id="nick_name" class="form-control">
                    @foreach($nickNames as $nick)
                    <option value="{{ $nick->id }}">{{ $nick->nick_name}}</option>
                    @endforeach
					
                </select>
                 @if ($errors->has('nick_name')) <p class="help-block">{{ $errors->first('nick_name') }}</p> @endif
				 <a href="<?php echo url('settings/nickname');?>">Add new nickname </a>
            </div> 
            <div class="form-group">
                <label for="topic name">Topic Name </label>
                <input type="text" name="topic_name" class="form-control" id="topic_name" value="">
				@if ($errors->has('topic_name')) <p class="help-block">{{ $errors->first('topic_name') }}</p> @endif
            </div>            
            <div  class="form-group">
                <label for="namespace">Name Space</label>
                <select  onchange="selectNamespace(this)" name="namespace" id="namespace" class="form-control">
                    <option value="">Select Namespace</option>
                    @foreach($namespaces as $namespace)
                    <option value="{{ $namespace->id }}" >{{$namespace->label}}</option>
                    @endforeach
                    <option value="other" @if(old('namespace') == 'other') selected @endif>Other</option>
                </select>
                <!--
                <input type="text" name="namespace" class="form-control" id="" value="">-->
                @if ($errors->has('namespace')) <p class="help-block">{{ $errors->first('namespace') }}</p> @endif
			</div>
            <div id="other-namespace" class="form-group" >
                <label for="namespace">Other Namespace Name</label>
                
                <input type="text" name="create_namespace" class="form-control" id="create_namespace" value="">
                <span class="note-label"><strong>Note</strong>: Name space is categorization of your topic, it can be something like: General,crypto_currency etc.</span>
                @if ($errors->has('create_namespace')) <p class="help-block">{{ $errors->first('create_namespace') }}</p> @endif
			</div>
            <div class="form-group">
                <label for="language">Language</label>
                <select class="form-control" name="language" id="language">
                    <option value="English">English</option>
                    <option value="French">French</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="">Additional Note</label>
                <textarea class="form-control" rows="4" name="note" id="note"></textarea>
            </div>    

            <button type="submit" id="submit" class="btn btn-login">Create Topic</button>
        </form>
    </div>
 </div>   
</div>  <!-- /.right-whitePnl-->

    <script>
        $(document).ready(function () {
            $("#datepicker").datepicker({
                changeMonth: true,
                changeYear: true
            });
            
        })
        function selectNamespace(){
            if($('#namespace').val() == 'other'){
                $('#other-namespace').css('display','block');
            }else{
                $('#other-namespace').css('display','none');
            }
        }
        selectNamespace();
    </script>


    @endsection

