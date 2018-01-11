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

<div class="camp top-head">
    <h3><b>Topic:</b>  {{ $topic->title}}</h3>
    <h3><b>Camp:</b> {{ $parentcamp }}</h3>  
</div>      	
<div class="right-whitePnl">
    <div class="container-fluid">
        
        <div class="Lcolor-Pnl">
            <h3>Canonizer Sorted Camp Tree
            </h3>
            <div class="content">
            <div class="row">
                <div class="tree col-sm-12">
                    <ul class="mainouter">
                        
                      
                       <li>
                        
                         <?php
                         $childs = $topic->childrens($topic->topic_num,$topic->camp_num); ?>
                         <span class="<?php if(count($childs) > 0) echo 'parent'; ?>"><i class="fa fa-arrow-right"></i> 
						 <?php 
						  $title     = preg_replace('/\s+/', '-', $topic->title); 
						  $topic_id  = $topic->topic_num."-".$title;
						 
						 ?>
						 <a href="<?php echo url('topic/'.$topic_id) ?>">
						 {{ $topic->title}} 
						 </a>
						 <div class="badge">48.25</div></span>
                         <?php
                        if(count($childs) > 0){
                            echo $topic->champTree($topic->topic_num,$topic->camp_num);
                        }else{
                            echo '<li class="create-new-li"><span><a href="'.route('camp.create',['topicnum'=>$topic->topic_num,'campnum'=>$topic->camp_num]).'">< Create A New Camp ></a></span></li>';
                        }?>
                           </li>
                       
                    </ul>
                    
                </div>
              
            </div>    
            </div>
        </div>
    </div>
	 <!-- /.container-fluid-->
	 <div class="container-fluid">
        
        <div class="Lcolor-Pnl">
            <h3><?php echo ($parentcamp=="Agreement") ? $parentcamp : "Camp"; ?> Statement
            </h3>
            <div class="content">
            <div class="row">
                <div class="tree col-sm-12">
                    <?php $statement = $topic->statement($topic->topic_num,$topic->camp_num);
					
					  echo ($statement->value!="") ? $statement->value : "No statement available";
					?>
					
				
                </div>
              
            </div>    
            </div>
        </div>
    </div>
	
	<div class="container-fluid">
        
        <div class="Lcolor-Pnl">
            <h3>Support Tree for "<?php echo $topic->camp_name;?>" Camp
            </h3>
            <div class="content">
            <div class="row">
                <div class="tree col-sm-12">
                    Total Support for This Camp (including sub-camps): 40.25
					
				
                </div>
              
            </div>    
            </div>
        </div>
    </div>
	<div class="container-fluid">
        
        <div class="Lcolor-Pnl">
            <h3>Current Topic Record:
            </h3>
            <div class="content">
            <div class="row">
                <div class="tree col-sm-12">
                    Topic Name : <?php echo $topic->topic->topic_name;?> <br/>
					Name Space : <?php echo $topic->topic->namespace;?>
                </div>
              
            </div>    
            </div>
        </div>
    </div>
	<div class="container-fluid">
        
        <div class="Lcolor-Pnl">
            <h3>Current Camp Record:
            </h3>
            <div class="content">
            <div class="row">
                <div class="tree col-sm-12">
                    Camp Name : <?php echo $topic->camp_name;?> <br/>
					Title : <?php echo $topic->title;?><br/>
					Keywords : <?php echo $topic->key_words;?><br/>
					Related URL : <?php echo $topic->url;?><br/>
					
					
					Related Nicknames : <?php echo (isset($topic->nickname->nick_name)) ? $topic->nickname->nick_name : "No nickname associated";?> <br/>
                </div>
              
            </div>    
            </div>
        </div>
    </div>
	
</div>  <!-- /.right-whitePnl-->
	

@endsection