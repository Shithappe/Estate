<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class booking_data extends Controller
{
    public function index(Request $request)
    {
        // $priorityData = DB::table('booking_data')
        //             ->where('priority', '>', 0)
        //             ->orderBy('star', 'desc')
        //             ->orderBy('review_count', 'desc')
        //             ->orderBy('score', 'desc');

        // $data = DB::table('booking_data')
        //             // ->where('priority', null)
        //             ->orderBy('star', 'desc')
        //             ->orderBy('review_count', 'desc')
        //             ->orderBy('score', 'desc')
        //             ->paginate(12);

        $data = DB::table('booking_data')
            ->orderByRaw('
                CASE 
                    WHEN priority > 0 THEN priority
                    ELSE 0
                END DESC
            ')
            ->orderBy('star', 'desc')
            ->orderBy('review_count', 'desc')
            ->orderBy('score', 'desc')
            ->paginate(12);

                    
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

        $types = DB::table('booking_data')
        ->select('type')
        ->distinct()
        ->pluck('type')
        ->toArray();

        $facilities = DB::table('facilities')->get();


        return Inertia::render('BookingData', [
            'data' => $data,
            'cities' => $cities,
            'types' => $types,
            'facilities' => $facilities
        ]);
    }


    public function booking_page($booking_id)
    {
        $booking = DB::table('booking_data')->where('id', $booking_id)->get();

        // Получение id из booking_facilities для заданного booking_id
        $facilityIds = DB::table('booking_facilities')->where('booking_id', $booking_id)->pluck('facilities_id');
 
        // Получение названий удобств из facilities на основе полученных id
        $facilities = DB::table('facilities')->whereIn('id', $facilityIds)->pluck('title');
 

        return Inertia::render('SingleBookingData', [
            'booking' => $booking,
            'facilities' => $facilities
        ]);
    } 
    
    public function booking_data_rate(Request $request)
    {

        $rooms = NULL;

        if (isset($request->checkin) && isset($request->checkout)){
            $rooms = DB::table('rooms_2_day')
                ->where('booking_id', $request->booking_id)
                ->where('checkin', '>=', $request->checkin)
                ->where('checkout', '<=', $request->checkout)
                ->whereDate('checkin', '=', DB::raw('DATE(created_at)'))
                ->get();
        }
        else {
            $rooms = DB::table('rooms_2_day')
                ->where('booking_id', $request->booking_id)
                ->whereDate('checkin', '=', DB::raw('DATE(created_at)'))
                ->get();
        }

        $maxAvailableRooms = DB::table('rooms_30_day')
            ->select('room_type', DB::raw('MAX(max_available_rooms) AS max_available'))
            ->where('booking_id', $request->booking_id)
            ->groupBy('room_type')
        ->get();


        // return $rooms;
        $groupedRooms = $rooms->groupBy('room_type');

        $resultArray = [];

        foreach ($groupedRooms as $roomType => $group) {
            // Находим соответствующую запись в $maxAvailableRooms по room_type
            $maxAvailableRoom = $maxAvailableRooms->firstWhere('room_type', $roomType);

            // Если запись найдена, продолжаем вычисления
            if ($maxAvailableRoom) {
                // Сумма свободных комнат по типу
                $sum = $group->sum('available_rooms');

                // Количество записей по типу
                $count = $group->count();

                // if ($roomType == 'Premium Room Garden View') {
                //     return[$maxAvailableRoom->max_available, $sum, $count];
                // }

                // Расчет занятости
                $occupancy = $sum / $count;
                $occupancy = $maxAvailableRoom->max_available - $occupancy;
                $occupancy = round(($occupancy / $maxAvailableRoom->max_available) * 100, 2);
                if ($occupancy < 0) $occupancy = null;
            } else {
                // Обработка ситуации, если не найдено соответствие
                $occupancy = null;
            }

            // Добавляем результаты в массив
            $resultArray[] = [
                'room_type' => $roomType,
                'occupancy' => $occupancy,
            ];
        }

        return $resultArray;
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


        // Получение id из booking_facilities для заданного booking_id
        $facilityIds = DB::table('booking_facilities')->where('booking_id', $booking_id)->pluck('facilities_id');
 
        // Получение названий удобств из facilities на основе полученных id
        $facilities = DB::table('facilities')->whereIn('id', $facilityIds)->pluck('title');
        

        $booking_data[0]->rooms = $rooms;
        $booking_data[0]->nearby_location = $nearby_location;
        $booking_data[0]->facilities = $facilities;


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

    public function booking_data_filters (Request $request)
    {
        $data = $request->json()->all();

        $filterTitle = $data['title'];
        $filterCity = $data['city'];
        $filterType = $data['type'];
        $filterFacilities = $data['facilities'];

        $query = DB::table('booking_data')
                    ->orderBy('star', 'desc')
                    ->orderBy('review_count', 'desc')
                    ->orderBy('score', 'desc');

        if (!empty($filterCity)) {
            $query->whereIn('city', $filterCity);
        }
        if (!empty($filterTitle)) {
            $query->where('title', 'like', '%' . $filterTitle . '%');
        }
        if (!empty($filterType)) {
            $query->whereIn('type', $filterType);
        }
        foreach ($filterFacilities as $facility) {
            $query->whereExists(function ($subquery) use ($facility) {
                $subquery->select(DB::raw(1))
                    ->from('booking_facilities')
                    ->whereRaw('booking_facilities.booking_id = booking_data.id')
                    ->where('facilities_id', $facility);
            });
        }
    
    
        $data = $query->paginate(12); 

        foreach ($data as $item) {
            $rooms = DB::table('room_cache')
                ->where('booking_id', $item->id)
                ->get();
        
            $item->rooms = DB::table('room_cache')->where('booking_id', $item->id)->get();
        }

        return $data;
    }

    public function setting_priority () 
    {
        $priority = DB::table('booking_data')
                    ->where('priority', '>', 0)
                    ->orderBy('priority', 'desc')
                    ->get();
                    // ->orderBy('review_count', 'desc')
                    // ->orderBy('score', 'desc');

        return Inertia::render('SettingPriorityPage', [
            'priority' => $priority
        ]);
    }

    public function priority_edit (Request $request)
    {
        // return $request;
        switch ($request->msg) {
            case 'edit':
                DB::table('booking_data')->where('id', $request->id)->update(['priority' => $request->priority]);
              break;
            case 'delete':
                DB::table('booking_data')->where('id', $request->id)->update(['priority' => null]);
              break;
          }
          return DB::table('booking_data')
          ->where('priority', '>', 0)
          ->orderBy('priority', 'desc')
          ->get();
    }
}
