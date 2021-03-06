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
use Cookie;

class SettingsController extends Controller
{
    public function __construct(){
		 parent::__construct();
	}
	
    public function index(){
         $user = User::find(Auth::user()->id);
        return view('settings.index',['user'=>$user]);
    }

    public function profile_update(Request $request){
        $input = $request->all();
        $id = (isset($_GET['id'])) ? $_GET['id'] : '';
		$private_flags = array();
        if($id){
            $user = User::find($id);
            $user->first_name = $input['first_name']; 
			if($input['first_name_bit']!='0') $private_flags[] = $input['first_name_bit'];
            $user->last_name = $input['last_name'];
			if($input['last_name_bit']!='0')  $private_flags[] = $input['last_name_bit'];
            $user->middle_name = $input['middle_name'];
			if($input['middle_name_bit']!='0') $private_flags[] = $input['middle_name_bit'];
            $user->gender = isset($input['gender']) ? $input['gender'] : '';
			//if($input['gender_bit']!='0') $private_flags[]=$input['gender_bit'];
            $user->birthday = date('Y-m-d',strtotime($input['birthday']));
			if($input['birthday_bit']!='0') $private_flags[]=$input['birthday_bit'];
            $user->language = $input['language'];
            $user->address_1 = $input['address_1'];
			if($input['address_1_bit']!='0')  $private_flags[]=$input['address_1_bit'];
            $user->address_2 = $input['address_2'];
			if($input['address_2_bit']!='0')  $private_flags[]=$input['address_2_bit'];
            $user->city = $input['city'];
			if($input['city_bit']!='0')  $private_flags[]=$input['city_bit'];
            $user->state = $input['state'];
			if($input['state_bit']!='0')  $private_flags[]=$input['state_bit'];
            $user->country = $input['country'];
			if($input['country_bit']!='0')  $private_flags[]=$input['country_bit'];
            $user->postal_code = $input['postal_code'];
			if($input['postal_code_bit']!='0')  $private_flags[]=$input['postal_code_bit'];

			$flags = implode(",",$private_flags); 
			
			$user->private_flags = $flags;
			 
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
        
		$as_of_time = time();
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
				
				$userNickname[] = $nickname->id;
			}
		
		 
		    $supportedTopic = Support::where('topic_num',$topicnum)
									  ->whereIn('nick_name_id',$userNickname)
									  ->whereRaw("(start < $as_of_time) and ((end = 0) or (end > $as_of_time))")
									  ->groupBy('topic_num')->orderBy('start','DESC')->first();
		
            return view('settings.support',['userNickname'=>$userNickname,'supportedTopic'=>$supportedTopic,'topic'=>$topic,'nicknames'=>$nicknames,'camp'=>$onecamp,'parentcamp'=>$campWithParents,'delegate_nick_name_id'=>$delegate_nick_name_id]);
	  } else {
		    $id = Auth::user()->id; 
			$encode = General::canon_encode($id);
			
		    $nicknames = Nickname::where('owner_code','=',$encode)->get();
			$userNickname=array();
			foreach($nicknames as $nickname) {
				
				$userNickname[] = $nickname->id;
			}
		
		 
		    $supportedTopic = Support::whereIn('nick_name_id',$userNickname)
			                           ->whereRaw("(start < $as_of_time) and ((end = 0) or (end > $as_of_time))")
			                           ->groupBy('topic_num')->orderBy('start','DESC')->get();
		
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
    
            if ($validator->fails()) { dd($validator);
                return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
            }

            $input = $request->all();
			// Check if camp supported already then remove duplicacy.
			$userNicknames  = unserialize($input['userNicknames']);
			
			$alreadySupport  = Support::where('topic_num',$input['topic_num'])->where('camp_num',$input['camp_num'])->where('end','=',0)->where('nick_name_id',$input['nick_name'])->get();
			if($alreadySupport->count() > 0 ) {
				Session::flash('error', "You have already supported this camp, you cant submit your support again.");
                return redirect()->back();
			}
			
			if(Camp::validateParentsupport($input['topic_num'],$input['camp_num'],$userNicknames)) {
				
				Session::flash('error', "You cant support child camps when you have supported a parent camp.");
                return redirect()->back();
				
			}

			if(Camp::validateChildsupport($input['topic_num'],$input['camp_num'],$userNicknames)){
				Session::flash('error', "You cant support parent camps when you have supported a child camp camp.");
                return redirect()->back();
			}

			
			
			$supportTopic  = new Support();
			$supportTopic->topic_num = $input['topic_num'];
			$supportTopic->nick_name_id = $input['nick_name'];
			$supportTopic->delegate_nick_name_id = $input['delegate_nick_name_id'];
			$supportTopic->start = time();
		    $supportTopic->camp_num = $input['camp_num'];
				
			$supportTopic->support_order = $input['lastsupport_order'] + 1;
			$supportTopic->save();
                 
			session()->forget("topic-support-{$input['topic_num']}");
			session()->forget("topic-support-nickname-{$input['topic_num']}");
			session()->forget("topic-support-tree-{$input['topic_num']}");
				
			Session::flash('success', "Your support has been submitted successfully.");
			return redirect()->back();
		 	
        }else{
            return redirect()->route('login');
        }


    }
	public function delete_support(Request $request){
        
		$id = Auth::user()->id;
		$input = $request->all();
		
		$support_id = (isset($input['support_id'])) ? $input['support_id'] : 0;
		$topic_num = (isset($input['topic_num'])) ? $input['topic_num'] : 0;
		$nick_name_id = (isset($input['topic_num'])) ? $input['topic_num'] : 0;
		
		if($id && $support_id && $topic_num) {
				$as_of_time=time();
				$currentSupport = Support::where('support_id',$support_id);
				$currentSupportRec = $currentSupport->first();
				$currentSupportOrder = $currentSupportRec->support_order;
			    $remaingSupportWithHighOrder = Support::where('topic_num',$topic_num)		                    
							//->where('delegate_nick_name_id',0)
							->whereIn('nick_name_id',[$currentSupportRec->nick_name_id])
							->whereRaw("(start < $as_of_time) and ((end = 0) or (end > $as_of_time))")
							->where('support_order','>',$currentSupportRec->support_order)
							->orderBy('support_order','ASC')							
							->get();
				
				if($currentSupport->update(array('end'=>time()))) {
				 foreach($remaingSupportWithHighOrder as $support){
						$support->support_order = $currentSupportOrder;
						$support->save();
						$currentSupportOrder++;
				 }

				session()->forget("topic-support-$topic_num");
				session()->forget("topic-support-nickname-$topic_num");
				session()->forget("topic-support-tree-$topic_num");

				 Session::flash('success', "Your support has been removed successfully.");
				
				} else {
				 Session::flash('error', "Your support has not been removed.");	
				}
                return redirect()->back();
				
			
		}
		Session::flash('error', "Invalid access.");
        return redirect()->back();
	}	

	public function algo(Request $request){
		$user = User::find(Auth::user()->id);
		return view('settings.preferences',compact('user'));
	}

	public function postAlgo(Request $request){
		$user = User::find(Auth::user()->id);
		$user->default_algo = $request->input('default_algo');
		$user->save();
		
		session(['defaultAlgo'=>$user->default_algo]);
		Session::flash('success', "Your default algorithm preference updated successfully.");
		return redirect()->back();
	}

	public function supportReorder(Request $request){

		$data = $request->only(['positions','topicnum']);
		if(isset($data['positions']) && !empty($data['positions'])){
			foreach($data['positions'] as $position=>$support_id){
				Support::where('support_id',$support_id)->update(array('support_order'=>$position+1));
			}
			$topic_num = $data['topicnum'];
			session()->forget("topic-support-$topic_num");
			session()->forget("topic-support-nickname-$topic_num");
			session()->forget("topic-support-tree-$topic_num");
		}
	}
}
