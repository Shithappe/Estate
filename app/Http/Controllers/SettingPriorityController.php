<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingPriorityController extends Controller
{
    private function getPriorityBooking($additionalElement = null) 
    {
        $hotels = DB::table('booking_data as bd')
            ->leftJoin('rooms_id as ri', 'bd.id', '=', 'ri.booking_id')
            ->select('bd.*')
            ->where(function ($query) {
                $query->whereNotNull('bd.forecast_price')
                      ->orWhere('bd.priority', '>', 0)
                      ->orWhereNotNull('ri.estimated_price');
            })
            ->groupBy('bd.id')
            ->orderBy('bd.priority', 'desc')
            ->get();
    
        // Для каждого отеля получаем все его комнаты
        foreach ($hotels as $hotel) {
            $hotel->rooms = DB::table('rooms_id')
                ->select('room_id', 'room_type', 'estimated_price')
                ->where('booking_id', $hotel->id)
                ->get();
        }
    
        // Если передан дополнительный элемент, добавляем его в начало массива
        if ($additionalElement !== null) {
            $additionalHotel = DB::table('booking_data')
                ->where('id', $additionalElement)
                ->orWhere('title', $additionalElement)
                ->first();
    
            if ($additionalHotel) {
                $additionalHotel->rooms = DB::table('rooms_id')
                    ->select('room_id', 'room_type', 'estimated_price')
                    ->where('booking_id', $additionalHotel->id)
                    ->get();
    
                $hotels->prepend($additionalHotel);
            }
        }
    
        return $hotels;
    }
    

    public function setting_priority () 
    {
        return Inertia::render('SettingPriorityPage', [
            'priority' => $this->getPriorityBooking()
        ]);
    }

    public function findBooking(Request $request)
    {
        return $this->getPriorityBooking($request->input('value'));
    }

    public function update_booking (Request $request)
    {
        DB::table('booking_data')->where('id', $request->id)
            ->update([
                'priority' => $request->priority,
                'forecast_price' => $request->forecast_price
            ]);

        return $this->getPriorityBooking();
    }
    
    public function setEstimatedPriceForRoom (Request $request)
    {
        DB::table('rooms_id')->where('room_id', $request->room_id)
            ->update([
                'estimated_price' => $request->estimated_price
            ]);

        return $this->getPriorityBooking();
    }

    public function change_images_order(Request $request)
    {
        DB::table('booking_data')
        ->where('id', $request->input('id')) // Условие поиска по booking_id
        ->update([
            'static_images' => $request->input('static_images') // Обновление поля static_images
        ]);

        return response()->json(['status' => 'success']);
    }

    public function addBooking(Request $request)
    {
        $cleanUrl = explode('?', $request->link)[0];
        $pattern = '/(?<!\.en-gb)(\.\w+)?\.html$/';
        $modifiedUrl = preg_replace($pattern, '.en-gb.html', $cleanUrl);

        if (preg_match('/\/hotel\/\w+\/([^.]+)/', $modifiedUrl, $matches)) {
            $slug = $matches[1];
        } else {
            return response()->json([
                'error' => 'Invalid URL format'
            ], 400);
        }

        try {
            $id = DB::table('booking_data')->insertGetId([
                'slug' => $slug,
                'link' => $modifiedUrl
            ]);
    
            // Подготавливаем команду
            $arg = "-a mode=$id";
            // $scrapy = "scrapy crawl";
            $scrapy = "/usr/bin/proxychains /usr/local/bin/scrapy crawl";
            $scriptPath = env('PARSE_PATH');
            $command = "cd $scriptPath && $scrapy booking $arg && $scrapy rooms_id $arg";
            
            // Возвращаем ответ пользователю сразу
            $response = response()->json([
                'link' => url("complex/$slug")
            ]);
            
            $response->send();
            
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            
            exec($command, $output, $returnVar);
            return;
    
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                $existingSlug = DB::table('booking_data')
                    ->where('link', $modifiedUrl)
                    ->value('slug');
    
                return response()->json([
                    'link' => url("complex/$existingSlug")
                ]);
            }
            throw $e;
        }

        // $arg = "-a mode=$id";
        // $scrapy = "/usr/bin/proxychains /usr/local/bin/scrapy crawl";
        // $scriptPath = env('PARSE_PATH');

        // $command = "cd $scriptPath && $scrapy booking $arg && $scrapy rooms_id $arg";

        // // Возвращаем результат
        //  return response()->json([
        //      'url' => $modifiedUrl,
        //      'command' => $command,
        //      'output' => [$output, $returnVar]
        //  ]);
    }
}
