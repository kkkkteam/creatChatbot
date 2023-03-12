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

        $content = "Classify the intention of the speaker from the conversation: ".$message." into categories(select 3 with possibility in percentages and display in JSON format）: chating , asking solution, wanting coupon, need recommendation, need information, need encourage, management, need more detail to action. And then give a suitable reply to the conversation to fulfill the intention of the highest chance in same language of the conversation.";

        $msg["role"] = "assistant";
        $msg["content"] = $content;
        $json = json_encode($msg);

        $send["model"] = "gpt-3.5-turbo";
        $send["messages"] = $json ;
        $send["temperature"] = 0.1;
        $send["max_tokens"] = 2000;
        $send["top_p"] = 1.0;
        $send["frequency_penalty"] = 0.5; 
        $send["presence_penalty"] = 0.5;
        $packget = json_encode($send);

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $send,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer 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'
        ),
        ));

        $temp = curl_exec($curl);
dd($temp);
        $json = stripslashes($temp);
        $intention = json_decode($json, true);
        // extract the intention from $response
        

        return $intention;
        
    }
}
