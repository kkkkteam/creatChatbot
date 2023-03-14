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

            $formatConversation = $this->convertDateInFormat($message);

            // use switch to do different reply to 
            switch($intention_highest){
                case "chating":
                    $botman->reply("chating\n\n".$intention."\n\n".$formatConversation);
                    break;

                case "need recommendation":
                    $botman->reply("need recommendation\n\n".$intention."\n\n".$formatConversation);
                    break;
                
                case "wanting coupon":
                    $botman->reply("wanting coupon\n\n".$intention."\n\n".$formatConversation);
                    break;
                
                default:  {
                    $botman->reply($intention_highest."\n\n".$intention."\n\n".$formatConversation);
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
        $content = "Classify the intention of the speaker from the conversation: ".$message." into categories (select 3 with higher possibility in percentages and display in JSON format）: chating, asking solution, wanting coupon, need recommendation, need information, need encourage, management, need more detail to action";
        $complete = $open_ai->chat([
			'model' => 'gpt-3.5-turbo',
			'messages' => [
                [
                    "role" => "assistant",
                    "content" => $content,
                ]
            ],
			'temperature' => 0.2,
			'max_tokens' => 3000,
            "frequency_penalty" => 0.5,
            "presence_penalty" => 0.0,
		]);

		$intention = [];
        $array = json_decode($complete, true);
        if (isset($array["choices"][0]["message"]["content"]) ){
            $result = $array["choices"][0]["message"]["content"];
            $intention = str_replace("\n", "", $result);
        }
        return json_decode($intention, true);
    }

    public function convertDateInFormat($input){
        
        // 處理節日日期 TODO
        // $input = str_replace(['中秋節','聖誕節','元旦'], ['2022-09-10','2021-12-25','2022-01-01'], $input);

        // 處理英文日期
        $input = preg_replace_callback('/\\b(Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|Nov(?:ember)?|Dec(?:ember)?)\\b/i', function($matches) {
            return date('m', strtotime($matches[0])) . '月';
        }, $input);

        $input = preg_replace_callback('/\\b(Mon(?:day)?|Tue(?:sday)?|Wed(?:nesday)?|Thu(?:rsday)?|Fri(?:day)?|Sat(?:urday)?|Sun(?:day)?)\\b/i', function($matches) {
            return date('Y-m-d', strtotime("next {$matches[0]}"));
        }, $input);

        // 處理中文日期
        $input = preg_replace_callback('/(\\d{4})年(\\d{1,2})月(\\d{1,2})日/i', function($matches) {
            return $matches[1] . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[3], 2, '0', STR_PAD_LEFT);
        }, $input);

        $input = preg_replace_callback('/(\\d{1,2})月(\\d{1,2})日/i', function($matches) {
            return date('Y') . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        }, $input);

        $input = preg_replace_callback('/(\\d{1,2})月(\\d{1,2})/i', function($matches) {
            return date('Y') . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        }, $input);

        $input = preg_replace_callback('/(\\d{1,2})日/i', function($matches) {
            return date('Y-m') . '-' . str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        }, $input);

        // 處理英文時間
        $input = preg_replace_callback('/\\b(today|tomorrow|day after tomorrow|next (?:Mon(?:day)?|Tue(?:sday)?|Wed(?:nesday)?|Thu(?:rsday)?|Fri(?:day)?|Sat(?:urday)?|Sun(?:day)?))\\b/i', function($matches) {
            $date = date('Y-m-d');
            if ($matches[1] == 'tomorrow') {
                $date = date('Y-m-d', strtotime('+1 day'));
            } else if ($matches[1] == 'day after tomorrow') {
                $date = date('Y-m-d', strtotime('+2 day'));
            } else if ($matches[1] == 'next Monday') {
                $date = date('Y-m-d', strtotime('next Monday'));
            } else if ($matches[1] == 'next Tuesday') {
                $date = date('Y-m-d', strtotime('next Tuesday'));
            } else if ($matches[1] == 'next Wednesday') {
                $date = date('Y-m-d', strtotime('next Wednesday'));
            } else if ($matches[1] == 'next Thursday') {
                $date = date('Y-m-d', strtotime('next Thursday'));
            } else if ($matches[1] == 'next Friday') {
                $date = date('Y-m-d', strtotime('next Friday'));
            } else if ($matches[1] == 'next Saturday') {
                $date = date('Y-m-d', strtotime('next Saturday'));
            } else if ($matches[1] == 'next Sunday') {
                $date = date('Y-m-d', strtotime('next Sunday'));
            }
            return $date;
        }, $input);

        // 處理中文時間
        $input = preg_replace_callback('/(今日|明日|後日|大後日|昨日|前日|大前日|今天|明天|聽日|後天|大後天|昨天|前天|大前天|\\d{1,2}日|\\d{1,2}月(?:\\d{1,2}日)?|\\d{4}年\\d{1,2}月\\d{1,2}日|下(?:週|星期)\\w{1})/i', function($matches) {
            $date = date('Y-m-d');
            if ($matches[1] == '明天' || $matches[1] == '明日' || $matches[1] == '聽日') {
                $date = date('Y-m-d', strtotime('+1 day'));
            } else if ($matches[1] == '後天' || $matches[1] == '後日') {
                $date = date('Y-m-d', strtotime('+2 day'));
            } else if ($matches[1] == '大後天' || $matches[1] == '大後日') {
                $date = date('Y-m-d', strtotime('+3 day'));
            } else if ($matches[1] == '昨天' || $matches[1] == '昨日') {
                $date = date('Y-m-d', strtotime('-1 day'));
            } else if ($matches[1] == '前天' || $matches[1] == '前日'  ) {
                $date = date('Y-m-d', strtotime('-2 day'));
            } else if ($matches[1] == '大前天' || $matches[1] == '大前日') {
                $date = date('Y-m-d', strtotime('-3 day'));
            } else if (preg_match('/(\\d{4})年(\\d{1,2})月(\\d{1,2})日/i', $matches[1], $match)) {
                $date = $match[1] . '-' . str_pad($match[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($match[3], 2, '0', STR_PAD_LEFT);
            } else if (preg_match('/(\\d{1,2})月(\\d{1,2})日/i', $matches[1], $match)) {
                $date = date('Y') . '-' . str_pad($match[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($match[2], 2, '0', STR_PAD_LEFT);
            } else if (preg_match('/(\\d{1,2})日/i', $matches[1], $match)) {
                $date = date('Y-m') . '-' . str_pad($match[1], 2, '0', STR_PAD_LEFT);
            } else if (preg_match('/下(?:週|星期)(\\w{1})/i', $matches[1], $match)) {
                $weekday = '';
                if ($match[1] == '一') {
                    $weekday = 'Monday';
                } else if ($match[1] == '二') {
                    $weekday = 'Tuesday';
                } else if ($match[1] == '三') {
                    $weekday = 'Wednesday';
                } else if ($match[1] == '四') {
                    $weekday = 'Thursday';
                } else if ($match[1] == '五') {
                    $weekday = 'Friday';
                } else if ($match[1] == '六') {
                    $weekday = 'Saturday';
                } else if ($match[1] == '日') {
                    $weekday = 'Sunday';
                }
                $date = date('Y-m-d', strtotime("next {$weekday}"));
            } else if (preg_match('/上(?:週|星期)(\\w{1})/i', $matches[1], $match)) {
                $weekday = '';
                if ($match[1] == '一') {
                    $weekday = 'Monday';
                } else if ($match[1] == '二') {
                    $weekday = 'Tuesday';
                } else if ($match[1] == '三') {
                    $weekday = 'Wednesday';
                } else if ($match[1] == '四') {
                    $weekday = 'Thursday';
                } else if ($match[1] == '五') {
                    $weekday = 'Friday';
                } else if ($match[1] == '六') {
                    $weekday = 'Saturday';
                } else if ($match[1] == '日') {
                    $weekday = 'Sunday';
                }
                $date = date('Y-m-d', strtotime("last {$weekday}"));
            }
            return $date;
        }, $input);

        return $input;
    }
    
}
