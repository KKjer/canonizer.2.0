@extends('layouts.app')
@section('content')
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

<div class="page-titlePnl">
    <h1 class="page-title">Canonizer Main Page</h1>
    <small>( This is a free open source prototype being developed by volunteers. <br>
        Please be patient with what we have so far and/or be willing to help. )</small> 
</div>       	
<div class="right-whitePnl">
    <div class="container-fluid">
        <div class="Gcolor-Pnl">
            <h3>Canonizer Information</h3>
            <div class="content">
                <p>
                Canonizer.com enables people to build consensus where none has been previously possible. However, Canonizer.com is not meant to measure 'truth' via popular consensus. The overall goal measures both popular and expert consensus, but also assesses new ideas. The reason for including both consensus and new ideas has to do with predictable patterns in the way science progresses. Crowds tend to engage in "herding behavior", looking for approval before expressing a possibly unpopular opinion. Even scientists are highly motivated to dismiss or ignore anything that goes against their beliefs, and the phenomenon of confirmation bias has been widely studied. Scientists may be strongly invested in the theories that they have built their careers upon, and may be loathed to give them up. Day-to-day, scientists work within established boundaries, within their own camps, comfortable with their theories, until those theories are proven wrong. The idea that science progresses under flashes of insight is entirely wrong. Science is a process of consensus building, with new ideas providing disruptions to the standard consensus. New ideas are typically considered to be false, wacky, and without merit. Many times they are, but over time, the best ideas among the radical fringe move their way into the center of what is accepted as truth. It takes time, and to paraphrase Max Plank, "science advances one funeral at a time". The goal of Canonizer.com is to accelerate this process.  It exposes biases, making it easier for the crowd to measure and consider the reliability of a new theory, even if it is counter to their currently preferred beliefs. Our goal is not to measure popularity, but to enable emerging minority theories to be more rapidly heard above any such biased bleating of any herd. 
                </p>
                <p>
                Canonizer.com is a wiki system that solves the critical liabilities of Wikipedia. It solves petty "edit wars" by providing contributors the ability to create and join camps and present their views without having them immediately erased. It also provides ways to standardize definitions and vocabulary, especially important in new fields. It provides a measure of reliability by providing metrics on expert consensus. As the growth of human knowledge doubles every year, we will never see another Da Vinci. Even a narrow field like consciousness has over 20K documents. It is becoming impossible for experts to know their own field without narrowing it down. In this situation, it is inevitable to filter the deluge of information with our own prior biases. Unlike primary literature sources, with far too much information for any individual to fully comprehend, this open survey system provides real time concise and quantitative descriptions of current and emerging leading theories. Theories that have been falsified by new scientific evidence are constantly monitored, measuring the degree to which experts abandon old theories for better ones. The continuous non-repetitive ratchet of evidence significantly accelerates and amplifies the education and wisdom of the entire crowd.
                </p>
            </div>
        </div>
        <div class="Lcolor-Pnl">
            <h3>Canonized list for  
                <select onchange="changeNamespace(this)" id="namespace">
                    @foreach($namespaces as $namespace)
                        <option data-namespace="{{ $namespace->label }}" value="{{ $namespace->id }}" {{ $namespace->id == session('defaultNamespaceId') ? 'selected' : ''}}>{{$namespace->label}}</option>
                    @endforeach
                 
                    
                </select>
            </h3>
            <div class="content">
            <div class="row">
			   @if(count($topics))
			    <div class="tree col-sm-12">
                    <ul class="mainouter" id="load-data">
                      <?php $createCamp = 1; ?> 
                      
                       @foreach($topics as $k=>$topic)
                       <?php 
                       $as_of_time = time();
                        if(isset($_REQUEST['asof']) && $_REQUEST['asof']=='date'){
                            $as_of_time = strtotime($_REQUEST['asofdate']);
                        }
                        
                       ?>
                         {!! $topic->campTreeHtml($createCamp) !!}
                         <?php $createCamp = 0;?>
                       @endforeach
					   <a id="btn-more" class="remove-row" data-id="{{ $topic->id }}"></a>
                    </ul>
                    
                </div>
				@else
				 <h6 style="margin-left:30px;"> No topic available.</h6>
                @endif			 
              </div>
            </div>    
            
        </div>
    </div>
    <!-- /.container-fluid-->
</div>  <!-- /.right-whitePnl-->

<script>
var request = false;
var offset = 10;
   $(document).scroll(function(e){
       var id = $('#btn-more').data('id'); 
       var queryString = "{!! Request::getQueryString() !!}";
	   var scrollTop = $(window).scrollTop();
	   
	   scrollTop = scrollTop + 650;
		  if ( scrollTop > $('.sticky-footer').offset().top && request==false) { 
				  
			   $("#btn-more").html("Please wait loading tree......");
			   request = true;
			   
			   $.ajax({
				   url : '{{ url("loadtopic") }}?'+queryString,
				   method : "POST",
				   data : {id:id,offset:offset, _token:"{{csrf_token()}}"},
				   dataType : "text",
				   success : function (data)
				   {
					  if(data != '') 
					  {
						  $('.remove-row').remove();
						  $('#load-data').append(data);
						  camptree();
						  request = false;
						  offset = offset + 10;
				   
					  }
					  else
					  {
						  $('#btn-more').html("No more topic available.");
					  }
				   }
			   });
		  }  
		  e.stopImmediatePropagation();
}); 

function changeNamespace(element){
    $.ajax({
        url:"{{ url('/change-namespace') }}",
        type:"POST",
        data:{namespace:$(element).val()},
        success:function(response){
            @if(env('APP_DEBUG'))
                window.location.reload();
            @else
            try{
                window.location.href="{{ url('/') }}"+$(element).find('option:selected').attr('data-namespace');
            }catch(err){
                window.location.href="{{ url('/') }}";
            }  
            @endif
        }
    });
}
</script>
@endsection
 