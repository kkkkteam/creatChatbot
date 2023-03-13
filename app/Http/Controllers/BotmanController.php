<?php

namespace App\Http\Controllers;

use BotMan\BotMan\Messages\Incoming\Answer;
use Orhanerday\OpenAi\OpenAi;
// use Model\member;

class BotmanController extends Controller
{
    public function handle()
    {
        $botman = app('botman');

        $botman->hears('{message}', function($botman, $message, $id=1) {

            // $user = Member::where("id", $id )->first();

            $array = [];
            // classicathe intention of the speaker
            $array = $this->intentionClassification($message);

            $intention_highest = "";
            $temp_score = 0;
            foreach ($array as $key => $value) {
                if ($value > $temp_score){
                    $intention_highest = $key;
                    $temp_score = $value ;
                }
            }
            $intention = json_encode($array);

            // use switch to do different reply to 
            switch($intention_highest){
                case "chating":
                    $botman->reply("chating\n\n".$intention);
                    break;

                case "need recommendation":
                    $botman->reply("need recommendation\n\n".$intention);
                    break;
                
                case "wanting coupon":
                    $botman->reply("wanting coupon\n\n".$intention);
                    break;
                
                default:  {
                    $botman->reply($intention_highest."\n\n".$intention);
                    break;
                }
            }

            // if ($message == 'hi') {
            //     $this->askName($botman);
            // }else{
            //     $botman->reply("write 'hi' for testing...");
            // }

        });

        $botman->listen();
    }

    // public function askName($botman)
    // {
    //     $botman->ask('Hello! What is your Name?', function(Answer $answer) {
    //         $name = $answer->getText();
    //         $this->say('Nice to meet you '.$name);
    //     });
    // }

    public function intentionClassification($message){

        $open_ai = new OpenAi(config('app.openai_api_key'));

        $content = "Classify the intention of the speaker from the conversation: ".$message." into categories(select 3 with possibility in percentages and display in JSON format）: chating , asking solution, wanting coupon, need recommendation, need information, need encourage, management, need more detail to action. And then give a suitable reply to the conversation to fulfill the intention of the highest chance in same language of the conversation.";

        // $msg = array(
        //     "role" => "assistant",
        //     "content" => $content
        // );
        // $json = "[".json_encode($msg)."]";

        $complete = $open_ai->chat([
			'model' => 'gpt-3.5-turbo',
			'messages' => [
                "role" => "assistant",
                "content" => $content,
            ],
			'temperature' => 0.2,
			'max_tokens' => 3000,
            "frequency_penalty" => 0.5,
            "presence_penalty" => 0.0,
		]);

		$intention = [];

		// if ($complete){
		// $json = json_decode($complete, true);
		// dd($json['choices'][0]['text'], $json);
		// if (isset($json['choices'][0]['text']) ){
		// 	$chat_02 = $json['choices'][0]['text'];
		// 	$chat_01 = trim($chat_02, '"');
		// 	$chat = substr($chat_01, 2);
		// }
        // $json = stripslashes($temp);
dd($complete);
        $array = json_decode($complete, true);
        if (isset($array["choices"][0]["message"]["content"]) ){
            $chat_01 = $array["choices"][0]["message"]["content"];
            $chat_02 = trim($chat_01, '"');
            $intention = trim($chat_02, '\n');
        }
dd($intention);
        return $intention;
        
    }
}
