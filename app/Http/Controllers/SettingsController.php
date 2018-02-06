<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Model\Camp;
use App\User;
use Illuminate\Support\Facades\Session;
use App\Library\General;
use App\Model\Nickname;
use App\Model\Support;
use App\Model\TopicSupport;
use App\Model\SupportInstance;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    

    public function index(){
         $user = User::find(Auth::user()->id);
        return view('settings.index',['user'=>$user]);
    }

    public function profile_update(Request $request){
        $input = $request->all();
        $id = (isset($_GET['id'])) ? $_GET['id'] : '';
        if($id){
            $user = User::find($id);
            $user->first_name = $input['first_name'];
            $user->last_name = $input['last_name'];
            $user->middle_name = $input['middle_name'];
            $user->gender = $input['gender'];
            $user->birthday = date('Y-m-d',strtotime($input['birthday']));
            $user->language = $input['language'];
            $user->address_1 = $input['address_1'];
            $user->address_2 = $input['address_2'];
            $user->city = $input['city'];
            $user->state = $input['state'];
            $user->country = $input['country'];
            $user->postal_code = $input['postal_code'];

            $user->update();
            Session::flash('success', "Profile updated successfully.");
            return redirect()->back();
        }
    }


    public function nickname(){
        $id = Auth::user()->id; 
        $encode = General::canon_encode($id);

        $user = User::find(Auth::user()->id);

        //get nicknames
        $nicknames = Nickname::where('owner_code','=',$encode)->get();
        return view('settings.nickname',['nicknames'=>$nicknames,'user'=>$user]);
    }

    public function add_nickname(Request $request){
        $id = Auth::user()->id;
        if($id){
            $messages = [
                'private.required' => 'Visibility status is required.',
            ];
            

            $validator = Validator::make($request->all(), [
                'nick_name' => 'required',
                'private' => 'required',
            ],$messages);
    
            if ($validator->fails()) {
                return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
            }


            $input = $request->all();
            $nickname = new Nickname();
            $nickname->owner_code = General::canon_encode($id);
            $nickname->nick_name = $input['nick_name'];
            $nickname->private = $input['private'];
            $nickname->create_time = time();
            $nickname->save();

            Session::flash('success', "Nick name created successfully.");
        return redirect()->back();
        }else{
            return redirect()->route('login');
        }


    }
	
	public function support($id=null,$campnums=null){
        
		
		if(isset($id)) {
			$topicnumArray  = explode("-",$id);
			$topicnum       = $topicnumArray[0];
			// get deligated nickname if exist
			$campnumArray  = explode("-",$campnums);
			$campnum       = $campnumArray[0];
			$delegate_nick_name_id = (isset($campnumArray[1])) ? $campnumArray[1] : 0;
			
			$id = Auth::user()->id; 
			$encode = General::canon_encode($id);

			$topic = Camp::where('topic_num',$topicnum)->where('camp_name','=','Agreement')->latest('submit_time')->first();
			//$camp = Camp::where('topic_num',$topicnum)->where('camp_num','=', $campnum)->latest('submit_time','objector')->get();
			$onecamp = Camp::where('topic_num',$topicnum)->where('camp_num','=', $campnum)->latest('submit_time')->first();
			$campWithParents = Camp::campNameWithAncestors($onecamp,'');
			
			if(!count($onecamp)) { return back();}

			//get nicknames
			$nicknames = Nickname::where('owner_code','=',$encode)->get();
			$userNickname=array();
			foreach($nicknames as $nickname) {
				
				$userNickname[] = $nickname->nick_name_id;
			}
		
		 
		    $supportedTopic = TopicSupport::where('topic_num',$topicnum)->whereIn('nick_name_id',$userNickname)->groupBy('topic_num')->orderBy('submit_time','DESC')->first();
		
            return view('settings.support',['userNickname'=>$userNickname,'supportedTopic'=>$supportedTopic,'topic'=>$topic,'nicknames'=>$nicknames,'camp'=>$onecamp,'parentcamp'=>$campWithParents,'delegate_nick_name_id'=>$delegate_nick_name_id]);
	  } else {
		    $id = Auth::user()->id; 
			$encode = General::canon_encode($id);
			
		    $nicknames = Nickname::where('owner_code','=',$encode)->get();
			$userNickname=array();
			foreach($nicknames as $nickname) {
				
				$userNickname[] = $nickname->nick_name_id;
			}
		
		 
		    $supportedTopic = TopicSupport::whereIn('nick_name_id',$userNickname)->groupBy('topic_num')->orderBy('submit_time','DESC')->get();
		
		    return view('settings.mysupport',['userNickname'=>$userNickname,'supportedTopic'=>$supportedTopic,'nicknames'=>$nicknames]);
		  
	  }
	}
	
	public function add_support(Request $request){
        $id = Auth::user()->id;
        if($id){
            $messages = [
                'nick_name.required' => 'Nickname is required.',
            ];
            

            $validator = Validator::make($request->all(), [
                'nick_name' => 'required',
                
            ],$messages);
    
            if ($validator->fails()) {
                return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
            }

            $input = $request->all();
			// Check if camp supported already then remove duplicacy.
			$userNicknames  = unserialize($input['userNicknames']);
			
			$alreadySupport = array();
			
			if(isset($input['topic_support_id']) && $input['topic_support_id'] != 0) {
			  $alreadySupport  = SupportInstance::where('camp_num',$input['camp_num'])->where('topic_support_id',$input['topic_support_id'])->get();
            }
			if(Camp::validateParentsupport($input['topic_num'],$input['camp_num'],$userNicknames)) {
				
				Session::flash('error', "You cant support child camps when you have supported a parent camp.");
                return redirect()->back();
				
			}
			
			if(count($alreadySupport)) {
				
				Session::flash('error', "You have already supported this camp, you cant submit your support again.");
                return redirect()->back();
			}
			
			if(isset($input['topic_support_id']) && $input['topic_support_id'] != 0) {
            
				$support = new SupportInstance();
				$support->topic_support_id = $input['topic_support_id'];
				$support->camp_num = $input['camp_num'];
				$support->submit_time = time();
				
				if(isset($input['firstchoice']))
					$support->support_order = 1;
				else
					$support->support_order = $input['lastsupport_rder'] + 1;
				
				$support->save();

				Session::flash('success', "Your support has been submitted successfully.");
				return redirect()->back();
		  } else {
			  
			  $supportTopic  = new TopicSupport();
			  $supportTopic->topic_num = $input['topic_num'];
			  $supportTopic->nick_name_id = $input['nick_name'];
			  $supportTopic->delegate_nick_id = $input['delegate_nick_name_id'];
			  $supportTopic->submit_time = time();
			  
			  if($supportTopic->save()) {
				  
				$support = new SupportInstance();
				$support->topic_support_id = $supportTopic->id;
				$support->camp_num = $input['camp_num'];
				$support->submit_time = time();
				
				if(isset($input['firstchoice']))
					$support->support_order = 1;
				else
					$support->support_order = $input['lastsupport_rder'] + 1;
				
				$support->save();

				Session::flash('success', "Your support has been submitted successfully.");
				return redirect()->back();
				  
			  } else {
				  
				  Session::flash('error', "Your support has not been submitted successfully.");
				  return redirect()->back();
			  }
		  }	
        }else{
            return redirect()->route('login');
        }


    }
	public function delete_support(Request $request){
        
		$id = Auth::user()->id;
		$input = $request->all();
		
		$support_id = (isset($input['support_id'])) ? $input['support_id'] : 0;
		$topic_support_id = (isset($input['topic_support_id'])) ? $input['topic_support_id'] : 0;
		if($id && $support_id) {
			
			if(SupportInstance::where('id',$support_id)->delete()) {
				
				Session::flash('success', "Your support has been removed successfully.");
				
				$remainingSupport = SupportInstance::where('topic_support_id',$topic_support_id)->get();
				
				if(count($remainingSupport)==0) {
					
					TopicSupport::where('id',$topic_support_id)->delete();					
					
				}
				
				
                return redirect()->back();
				
			} else {
				
				Session::flash('error', "Your support has not been removed.");
                return redirect()->back();
				
			}
		}
		Session::flash('error', "Invalid access.");
        return redirect()->back();
		
	}	
}
