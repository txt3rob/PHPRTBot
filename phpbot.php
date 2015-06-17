<?php

require "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;
require_once('twitterbotclass.php');

set_time_limit(0);
ignore_user_abort();

function run($tweets){
    
    global $bot;
    
    foreach($tweets->statuses as $tweet){
        
        if(!$bot->inblacklist($tweet->user->id_str)){
            if(rand(1,2) == 2){
                $bot->retweet($tweet->id_str, $tweet->user->id_str);
            }
            else{
                $bot->favorite($tweet->id_str, $tweet->user->id_str);
                $bot->retweet($tweet->id_str, $tweet->user->id_str);
            }
            $bot->follow($tweet->user->id_str);
        }
        sleep(60);
    }
    
}

$bot = new twitterbot;
$bot->connect('', '', '', '');

$bot->addblacklist(array("1390693447", "558800766", "2198265000"));

$bot->checkignore(array("ignore", "stop"), "You have been added to the ignore list. Any more problems? Contact https://twitter.com/jdf221");

$bot->action("run", $bot->search("#php OR #php5 OR #phpgd OR @phpstorm OR #phpdev"));

?>
