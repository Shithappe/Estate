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

        $query = DB::table('booking_data')->orderBy('score', 'desc');

        if (!empty($filterCity)) {
            $query->where('city', $filterCity);
        }
        if (!empty($filterTitle)) {
            $query->where('title', $filterTitle);
        }
    
        $data = $query->paginate(10); 


        foreach ($data as $item) {
            $rooms = DB::table('room_cache')
                ->where('booking_id', $item->id)
                ->get();
        
            $item->rooms = DB::table('room_cache')->where('booking_id', $item->id)->get();
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


    public function booking_page($booking_id)
    {
        $booking = DB::table('booking_data')->where('id', $booking_id)->get();

        $rooms = DB::table('room_cache')
            ->where('booking_id', $booking_id)
            // ->whereDate('date', 'YOUR_DATE_HERE') 
            ->get();

        return Inertia::render('SingleBookingData', [
            'booking' => $booking,
            'rooms' => $rooms
        ]);
    } 
    
    public function booking_data_rate(Request $request)
    {
        $rooms = NULL;

        if (isset($request->checkin) && isset($request->checkout)){
            $rooms = DB::table('rooms_30_day')
                ->where('booking_id', $request->booking_id)
                ->where('checkin', '>=', $request->checkin)
                ->where('checkout', '<=', $request->checkout)
                ->get();
        }
        else {
            $rooms = DB::table('rooms_30_day')
                ->where('booking_id', $request->booking_id)
                ->get();
        }

        

        $maxAvailableRooms = [];

        // Находим максимальное доступное количество комнат для каждого типа комнаты
        foreach ($rooms as $room) {
            $roomType = $room->room_type;

            if (!isset($maxAvailableRooms[$roomType]) || $room->max_available_rooms > $maxAvailableRooms[$roomType]) {
                $maxAvailableRooms[$roomType] = $room->max_available_rooms;
            }
        }

        $occupancyPercentage = [];

        // Получаем процент заполненности для каждого типа комнаты на заданную дату
        foreach ($rooms as $room) {
            $roomType = $room->room_type;
            $percentage = ($maxAvailableRooms[$roomType] > 0) ? (($maxAvailableRooms[$roomType] - $room->max_available_rooms) / $maxAvailableRooms[$roomType]) * 100 : 100;
            $occupancyPercentage[$roomType][] = $percentage;
        }

        $averageOccupancyPercentage = [];

        // Вычисляем средний процент заполненности для каждого типа комнаты
        foreach ($occupancyPercentage as $roomType => $percentages) {
            $averagePercentage = array_sum($percentages) / count($percentages);
            $averageOccupancyPercentage[$roomType] = round($averagePercentage, 2) . "%";
        }

        return $averageOccupancyPercentage;
    }

    public function booking_data_map(Request $request)
    {
        $coordinates = DB::table('booking_data')
            ->select('id', 'location')
            ->get();

        // return $coordinates;
        $coordinatesArray = [];

        // Перебираем полученные координаты и обрабатываем каждую пару значений
        foreach ($coordinates as $coord) {
            $coords = explode(',', $coord->location); // Используем переменную $coords, а не $coord->location
        
            // Проверяем, что у нас есть две координаты, прежде чем добавить их в массив
            if (count($coords) >= 2) {
                $coordinatesArray[] = [
                    'id' => $coord->id,
                    'location' => [$coords[0], $coords[1]]
                ];
            }
        }

        return Inertia::render('BookingDataMap', [
            'locations' => $coordinatesArray
        ]);
    }

    public function booking_data_map_card ($booking_id)
    {
        $booking_data = DB::table('booking_data')
        ->select('id', 'title', 'description', 'star', 'images', 'location', 'type')
        ->where('id', $booking_id)
        ->get();

        $nearby_location = $this->get_nearby_location($booking_data[0]->location);


        $rooms = DB::table('room_cache')
                ->where('booking_id', $booking_id)
                ->get();
        
                
        $booking_data[0]->rooms = $rooms;
        $booking_data[0]->nearby_location = $nearby_location;

        return $booking_data[0];
    }

    public function get_nearby_location($location, $radius = 2)
    {
        $location = explode(',', $location);

        $centerLat = $location[0]; 
        $centerLng = $location[1]; 

        $objects = DB::table('booking_data')
            ->select('id', 'title', 'description', 'star', 'images', 'type', 'location')
            ->whereRaw('(6371 * acos(cos(radians(?)) * cos(radians(SUBSTRING_INDEX(location, ",", 1))) * cos(radians(SUBSTRING_INDEX(location, ",", -1)) - radians(?)) + sin(radians(?)) * sin(radians(SUBSTRING_INDEX(location, ",", 1))))) <= ?', [$centerLat, $centerLng, $centerLat, $radius])
            ->get();

        return $objects;
    }
}
