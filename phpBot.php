<?php

set_time_limit(0); //Set no time limit
ignore_user_abort();

require_once('twitteroauth-master/twitteroauth/twitteroauth.php'); //Require Twitter API

$connection = new TwitterOAuth('consumerKey', 'consumerSecretKey', 'applicationKey', 'applicationSecretKey'); //Connect to API/account

$search = $connection->get('search/tweets', array('q' => '#php OR #php5 OR #phpgd OR @phpstorm OR #phpdev -@photoshop00000', 'count' => '60', 'lang' => 'en', 'result_type' => 'recent')); //Search for some tweets

$blackList = array("1390693447", "558800766"); //Set some accounts we don't want to retweet

    foreach($search->statuses as $result){ //Loop through the 60 tweets found
        
if(!in_array($result->user->id_str, $blackList)){
        if(rand(1,2) == 1){
        $retweet = $connection->post('statuses/retweet/'.$result->id_str); //Reweet that tweet!
        }
        else{
        $favorite = $connection->post('favorites/create', array("id" => $result->id_str)); //Favorite that tweet!
        $retweet = $connection->post('statuses/retweet/'.$result->id_str); //Reweet that tweet!
        }
        }
        sleep(60); //Wait one minute
$connection->post('friendships/create', array("user_id" => $result->user->id_str));
    }

?>