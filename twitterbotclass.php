<?php

class twitterbot {

    var $connection;
    var $blacklist = array();

    var $m = null;
    var $db = null;
    var $ignorelist = null;
    var $botid = null;
    
    function instring($inArray, $inString){
        
        if(is_array($inArray)){
            foreach($inArray as $e){
                if(strpos($inString, $e)!==false)
                    return true;
            }
            return false;
        }else{
            return (strpos( $inString , $inArray )!==false);
        }
        
    }

    function connect($consumerkey, $consumersecret, $secretkey, $secretsecret){
        global $ignorelist;

        $this->connection = new TwitterOAuth($consumerkey, $consumersecret, $secretkey, $secretsecret);
        $botid = $this->connection->get('account/verify_credentials')->id_str;
        
        $m = new MongoClient();
        $db = $m->bots;
        $ignorelist = $db->$botid;
        
       return $this->connection;
        
    }

    function addblacklist($userids){
       
       $this->blacklist = array_merge($this->blacklist, $userids);
       
       return $this->blacklist;
       
    }

    function tweet($msg, $userid = "00000"){
        
        if(!$this->hasbeenignored($userid)){
        $tweet = $this->connection->post('statuses/update', array("status" => $msg));
        }
        
        return $tweet;
    }

    function reply($msg, $to, $userid = "00000"){
        
        if(!$this->hasbeenignored($userid)){
        $tweet = $this->connection->post('statuses/update', array("status" => $msg, "in_reply_to_status_id" => $to));
        }
        
        return $tweet;
    }

    function retweet($id, $userid = "00000"){
        
        if(!$this->hasbeenignored($userid)){
        $retweet = $this->connection->post('statuses/retweet/'.$id);
        }
        
        return $retweet;
    }

    function favorite($id, $userid = "00000"){
        
        if(!$this->hasbeenignored($userid)){
        $favorite = $this->connection->post('favorites/create', array("id" => $id));
        }
        
        return $favorite;
    }

    function search($query, $count = 60, $lang = "en", $resulttype = "recent"){
        
        $search = $this->connection->get('search/tweets', array('q' => $query, 'count' => $count, 'lang' => $lang, 'result_type' => $resulttype));
        
        return $search;
    }

    function action($function, $tweets){
        
        return $function($tweets);
        
    }

    function follow($id){
        
        if(!$this->hasbeenignored($id)){
        $follow = $this->connection->post('friendships/create', array("user_id" => $id));
        }
        
        return $follow;
        
    }
    
    function last_tweet ($screenname, $count = 1, $exclude_replies = true)
	
	{
		$last_tweet = $this->connection->get('statuses/user_timeline', array('screen_name' => $screename, 'count' => $count, 'exclude_replies' => $exclude_replies = true));
		return $last_tweet;
	}

    function inblacklist($id){
        
        if(in_array($id, $this->blacklist)){
            return true;
        }
        else{
            return false;
        }
        
        return $follow;
        
    }

    function hasbeenignored($userid){
        global $ignorelist;
        
        $ignoreedsearch = $ignorelist->find(array("user" => $userid));
        $ignoreedsearch = iterator_to_array($ignoreedsearch);
        
        if(empty($ignoreedsearch)){
            return false;
        }
        else{
            return true;
        }
    }

    function checkignore($words, $thereply){
        global $ignorelist;

        $mentions = $this->connection->get('statuses/mentions_timeline', array('count' => '200'));
        
        foreach($mentions as $mention){
            if($this->instring($words, strtolower($mention->text)) && !$this->hasbeenignored($mention->user->id_str)){
                $reply = $this->reply("@".$mention->user->screen_name.", ".$thereply, $mention->id_str, $mention->user->id_str);
                $ignorelist->insert(array("user" => $mention->user->id_str));
            }
            
        }
        
    }

}

?>
