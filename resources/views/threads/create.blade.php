{{--  @extends('layouts.forum')  --}}

@extends('layouts.app')

@section('content')
			
            <div class="camp top-head">
    			<h3>Create a new thread for {{ $topicname }}</h3>
			</div>
            <div class="right-whitePnl">
            	 <div class="panel panel-group">
                            
                    <div class="panel-body">
                        <form method="POST" action="{{ URL::to('/')}}/forum/{{ $topicname }}/{{ $campnum }}/threads">
                            {{ csrf_field() }}
                         
                            <div class="form-group">

                                <label for="title">Title of Thread: </label>
                                
                                <input type="text" class="form-control" id="title" placeholder="Title" name="title">
                            
                            </div>

                            <div class="form-group">

                                <label for="body">Content</label>

                                <textarea name="body" id="body" class="form-control" rows="5" placeholder="Write Your Content Here"></textarea>
                           
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>

                            @if (count($errors))
                                <ul class="alert alert-danger">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif

                        </form>

                    </div>
                
                </div>
            
            </div>
        
     
@endsection