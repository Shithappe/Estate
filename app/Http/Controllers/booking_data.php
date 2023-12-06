<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class booking_data extends Controller
{
    public function index(Request $request)
    {

        $filterCity = $request->query('city');

        $query = DB::table('booking_data');

        if (!empty($filterCity)) {
            $query->where('city', $filterCity); // Добавляем условие в запрос, если фильтр по городу задан
        }
    
        $data = $query->paginate(5); 


        // $data = DB::table('booking_data')->paginate(5);

        foreach ($data as $item) {
            $item->rooms = DB::table('rooms')->where('booking_id', $item->id)->get();
        }


        $cities = DB::table('booking_data')
        ->select('city')
        ->distinct()
        ->pluck('city')
        ->toArray();

        

        return Inertia::render('BookingData', [
            'data' => $data,
            'cities' => $cities
        ]);
    }
}
