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
        $filterTitle = $request->query('title');

        $query = DB::table('booking_data');

        if (!empty($filterCity)) {
            $query->where('city', $filterCity);
        }
        if (!empty($filterTitle)) {
            $query->where('title', $filterTitle);
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
