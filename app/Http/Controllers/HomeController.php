<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Topic;
use App\Model\Camp;
use App\Model\Support;
use App\Model\TopicSupport;
use App\Model\SupportInstance;
use DB;
use App\Model\Namespaces;
use Auth;

class HomeController extends Controller {

	public function __construct(){
		 parent::__construct();

		 
	}

    public function index(Request $request,$params = null) {
		if(Auth::check()){
			if(!session('defaultUserAlgo')){
				$defaultAlgo = Auth::user()->default_algo;
				session(['defaultAlgo'=>$defaultAlgo]);
				session(['defaultUserAlgo'=>$defaultAlgo]);
			}
		}
		$namespaces= Namespaces::all();
		$topics = Camp::getAllAgreementTopic(10,$_REQUEST);
        
        return view('welcome', ['topics' => $topics,'namespaces'=>$namespaces]);
    }
	
	public function loadtopic(Request $request){
		
		$output = '';
        $id = $request->id;
		$topics = Camp::getAllLoadMoreTopic($request->offset,$_REQUEST,$id);
		
		
		
		  foreach($topics as $k=>$topicdata) {
			  
			   $output .= $topicdata->campTreeHtml();
			   						
		  }
		  ($output != '') ? $output .='<a id="btn-more" class="remove-row" data-id="'.$topicdata->id.'"></a>' : '';
      
	  echo $output;		  
		
	}
	public function browse() {

      
        $topics = Camp::getBrowseTopic();
		$namespaces= Namespaces::all();
        
        return view('browse', compact('topics','namespaces'));
    }
    
    public function recusriveCampDisp($childs){ 
        foreach($childs as $child){
            echo "child --" . $child->title . "<br/>";
            if(count($child->childrens($child->topic_num,$child->camp_num))>0){
                $this->recusriveCampDisp($child->childrens($child->topic_num,$child->camp_num));
            }
        }
    }
	public function supportmigration() {

     if(!isset($_REQUEST['instance'])) {        
		$counter = 0;
		 $stored = array();
        $topics = Support::where('delegate_nick_name_id','!=',0)->groupBy('topic_num','nick_name_id')->orderBy('start','DESC')->get();
		
		$topics1 = Support::groupBy('topic_num','nick_name_id')->orderBy('start','DESC')->get();
		
        $alreadyMigrated = TopicSupport::get();
	     //echo "total topic migrating is :".count($topics)." </br>";
		 
		if(count($alreadyMigrated)==0) { 
	    foreach($topics as $key=>$topic) {
			
			$topicSupport = new TopicSupport();
			
			$topicSupport->topic_num = $topic->topic_num;
			$topicSupport->nick_name_id = $topic->nick_name_id;
			$topicSupport->delegate_nick_id = $topic->delegate_nick_name_id;
			$topicSupport->submit_time = $topic->start;
			
			$topicSupport->save();
			
			$stored[] = $topic->topic_num.'-'.$topic->nick_name_id;
			
			$counter++;
			
		}
		
		foreach($topics1 as $key=>$topic) {
			
			
			if(!in_array($topic->topic_num.'-'.$topic->nick_name_id,$stored)) {
			$topicSupport = new TopicSupport();
			
			$topicSupport->topic_num = $topic->topic_num;
			$topicSupport->nick_name_id = $topic->nick_name_id;
			$topicSupport->delegate_nick_id = $topic->delegate_nick_name_id;
			$topicSupport->submit_time = $topic->start;
			
			$topicSupport->save();
			
			
			 $counter++;
			}	
			
		}
		echo "total ".$counter." topic migrated."; exit;
	  }	else { echo "Data already migrated."; exit;}
	 } else {
		 
		$topic = TopicSupport::orderBy('id','ASC')->get();
        
		$alreadyMigrated = SupportInstance::get();
		
		if(count($topic) > 0 && count($alreadyMigrated) == 0 ) {
		
        foreach($topic as $data) {
			
			$supportCamp = Support::where('topic_num',$data->topic_num)->where('nick_name_id',$data->nick_name_id)->orderBy('support_order','ASC')->get();
			
			foreach($supportCamp as $d) {
				
				$sinstance = new SupportInstance();
				
				$sinstance->topic_support_id = $data->id;
				$sinstance->camp_num = $d->camp_num;
				$sinstance->support_order = $d->support_order + 1;
				$sinstance->submit_time  = $d->start;
				$sinstance->status = 0;
				
				$sinstance->save();
			}
			
		}	

        echo "Data migrated successfully....";	exit;
		} else {
			
			echo "There is no topic available to migrate or data already migrated."; exit;
		}		
		 
	 }	
		
        
    }

	public function changeAlgorithm(Request $request){
		session(['defaultAlgo'=>$request->input('algo')]);
		
	}

	public function changeNamespace(Request $request){
		$namespace = Namespaces::find($request->input('namespace'));

		session(['defaultNamespaceId'=>$namespace->id]);
		
	}

}
