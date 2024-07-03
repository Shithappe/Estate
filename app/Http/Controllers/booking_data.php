<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class booking_data extends Controller
{
    public function index(Request $request)
    {
        $data = DB::table('booking_data')
            ->select('booking_data.id', 
                    'booking_data.images',
                    'booking_data.title', 
                    'booking_data.city', 
                    'booking_data.type', 
                    'booking_data.min_price',
                    'booking_data.max_price',
                    'booking_data.star', 
                    'booking_data.score',
                    'booking_data.forecast_price',
                    DB::raw('COUNT(rooms.id) as types_rooms'),
                    DB::raw('SUM(rooms.max_available) as count_rooms'),
                    DB::raw('AVG(rooms.occupancy) as occupancy_rate')
                    )
            ->leftJoin('rooms', 'booking_data.id', '=', 'rooms.booking_id')
            ->orderByRaw('
                CASE 
                    WHEN booking_data.priority > 0 THEN booking_data.priority
                    ELSE 0
                END DESC
            ')
            ->orderBy('booking_data.star', 'desc')
            ->orderBy('booking_data.review_count', 'desc')
            ->orderBy('booking_data.score', 'desc')
            ->groupBy('booking_data.id')
            ->paginate(12);


            $minutes = 1440;

            $countries = Cache::remember('countries', $minutes, function () {
                return DB::table('booking_data')
                    ->select('country', 'city')
                    ->distinct()
                    // ->whereNotNull('country') // Исключаем пустые страны
                    ->get()
                    ->groupBy('country')
                    ->map(function ($item) {
                        return $item->pluck('city')->toArray();
                    });
            });
            
            $cities = Cache::remember('cities', $minutes, function () {
                return DB::table('booking_data')
                    ->select('city')
                    ->distinct()
                    ->pluck('city')
                    ->toArray();
            });
            
            $types = Cache::remember('types', $minutes, function () {
                return DB::table('booking_data')
                    ->select('type')
                    ->distinct()
                    ->pluck('type')
                    ->toArray();
            });
            
            $facilities = Cache::remember('facilities', $minutes, function () {
                return DB::table('facilities')->get();
            });


        return Inertia::render('BookingData', [
            'data' => $data,
            'countries' => $countries,
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
    
    // public function booking_data_rate(Request $request)
    // {
    //     $rooms = NULL;

    //     // if (isset($request->checkin) && isset($request->checkout)){
    //         $rooms = DB::table('rooms_2_day')
    //             // ->select('room_type', 'active', 'price', 'occupancy')
    //             ->where('booking_id', $request->booking_id)
    //             ->where('checkin', '>=', $request->checkin)
    //             ->where('checkin', '<=', $request->checkout)
    //             ->whereDate('checkin', '=', DB::raw('DATE(created_at)'))
    //             ->get();
    //         // }
    //         // else {
    //         //     $rooms = DB::table('rooms')
    //         //     ->select('room_type', 'active', 'price', 'occupancy')
    //         //     ->where('booking_id', $request->booking_id)
    //         //     ->get();

                
    //         // }
    //         return [$request->booking_id, $rooms];


    //     // $maxAvailableRooms = DB::table('rooms')
    //     //     ->select('room_type', DB::raw('MAX(max_available) AS max_available'), 'active', DB::raw('MAX(price) AS price'))
    //     //     ->where('booking_id', $request->booking_id)
    //     //     ->groupBy('room_type', 'active')
    //     //     ->get();
    //     $maxAvailableRooms = DB::table('rooms_30_day')
    //         ->select('room_type', DB::raw('MAX(max_available_rooms) AS max_available'), DB::raw('MAX(price) AS price'))
    //         ->where('booking_id', $request->booking_id)
    //         ->groupBy('room_type')
    //         ->get();

    //     $groupedRooms = $rooms->groupBy('room_type');

    //     $resultArray = [];

    //     foreach ($groupedRooms as $roomType => $group) {
    //         // Находим соответствующую запись в $maxAvailableRooms по room_type
    //         $maxAvailableRoom = $maxAvailableRooms
    //         ->first(function ($item) use ($roomType) {
    //             return $item->room_type === $roomType; // && $item->price !== null;
    //         });


    //     // Если запись найдена и цена не равна NULL, продолжаем вычисления
    //     if ($maxAvailableRoom) { // && $maxAvailableRoom->price !== null
    //         // Сумма свободных комнат по типу
    //         $sum = $group->sum('available_rooms');
    //         return $sum;

    //         // Количество записей по типу
    //         $count = $group->count();

    //         // Расчет занятости
    //         $occupancy = $sum / $count;  // среднее
    //         $occupancy = $maxAvailableRoom->max_available - $occupancy; // отнимает от максимального
    //         if ($occupancy > 0) $occupancy = round(($occupancy / $maxAvailableRoom->max_available) * 100, 2); // переводим в %
    //         if ($occupancy < 0) $occupancy = -1;
    //     } else {
    //         // Обработка ситуации, если не найдено соответствие
    //         $occupancy = -1;
    //     }

    //     // if ($occupancy > 0 && $occupancy < 100) {
    //         // Добавляем результаты в массив
    //         $resultArray[] = [
    //             'room_type' => $roomType,
    //             // 'active' => $maxAvailableRoom ? $maxAvailableRoom->active : null,
    //             'price' => $maxAvailableRoom ? intval($maxAvailableRoom->price) : null,
    //             'occupancy' => $occupancy
    //         ];
    //     // }
    //     }

    //     return $resultArray;
    // }


    public function booking_data_rate(Request $request)
    {
        $rooms = NULL;
        if (isset($request->checkin) && isset($request->checkout)){
            $rooms = DB::table('rooms_2_day')
                ->where('booking_id', $request->booking_id)
                ->where('checkin', '>=', $request->checkin)
                ->where('checkin', '<=', $request->checkout)
                ->whereDate('checkin', '=', DB::raw('DATE(created_at)'))
                ->get();
        }
        else {
            $rooms = DB::table('rooms_2_day')
            ->where('booking_id', $request->booking_id)
                ->whereDate('checkin', '=', DB::raw('DATE(created_at)'))
            ->get();
        }


        $maxAvailableRooms = DB::table('rooms')
            ->select('room_type', DB::raw('MAX(max_available) AS max_available'), 'active', DB::raw('MAX(price) AS price'))
            ->where('booking_id', $request->booking_id)
            ->groupBy('room_type', 'active')
            ->get();

        if ($maxAvailableRooms->isEmpty()) {
            $maxAvailableRooms = DB::table('rooms_2_day')
                ->select('room_type', DB::raw('MAX(available_rooms) AS max_available'), DB::raw('MAX(price) AS price'))
            ->where('booking_id', $request->booking_id)
                ->groupBy('room_type')
            ->get();
        }

        $groupedRooms = $rooms->groupBy('room_type');
  
        $resultArray = [];
        foreach ($groupedRooms as $roomType => $group) {
            // Находим соответствующую запись в $maxAvailableRooms по room_type
            $maxAvailableRoom = $maxAvailableRooms
            ->first(function ($item) use ($roomType) {
                return $item->room_type === $roomType;  // && $item->price !== null
            });
  
        // Если запись найдена и цена не равна NULL, продолжаем вычисления
        if ($maxAvailableRoom) { // && $maxAvailableRoom->price !== null
            // Сумма свободных комнат по типу
            $sum = $group->sum('available_rooms');
            // Количество записей по типу
            $count = $group->count();
            // Расчет занятости
            $occupancy = $sum / $count;  // среднее
            $occupancy = $maxAvailableRoom->max_available - $occupancy; // отнимает от максимального
            if ($occupancy > 0) $occupancy = round(($occupancy / $maxAvailableRoom->max_available) * 100, 2); // переводим в %
            if ($occupancy < 0) $occupancy = -1;
        } else {
            // Обработка ситуации, если не найдено соответствие
            $occupancy = -1;
        }

            // Добавляем результаты в массив
            $resultArray[] = [
                'room_type' => $roomType,
                'active' => $maxAvailableRoom ? $maxAvailableRoom->active : null,
                'price' => $maxAvailableRoom ? intval($maxAvailableRoom->price) : null,
                'occupancy' => $occupancy
            ];
        }

        return $resultArray;
    }


    public function booking_data_map(Request $request)
    {
        $data = $request->json()->all();

        $filterCity = !empty($data['city']) ? $data['city'] : [];
        $filterType = !empty($data['type']) ? $data['type'] : [];
        $filterFacilities = !empty($data['facilities']) ? $data['facilities'] : [];
        $filterPrice = !empty($data['price']) ? $data['price'] : [];

        $query = DB::table('booking_data');

        if (!empty($filterCity)) $query->whereIn('city', $filterCity);
        if (!empty($filterType)) $query->whereIn('type', $filterType);
        if (!empty($filterFacilities)) {
            foreach ($filterFacilities as $facility) {
                $query->whereExists(function ($subquery) use ($facility) {
                    $subquery->select(DB::raw(1))
                        ->from('booking_facilities')
                        ->whereRaw('booking_facilities.booking_id = booking_data.id')
                        ->where('facilities_id', $facility);
                });
            }
        }
        if (!empty($filterPrice)) {
            if (isset($filterPrice['min'])) $query->where('price', '>=', $filterPrice['min']);
            if (isset($filterPrice['max'])) $query->where('price', '<=', $filterPrice['max']);
        }


        $filteredData = $query->select('id', 'title', 'occupancy', 'price', 'location')->get();

        $coordinatesArray = [];

        foreach ($filteredData as $coord) {
            $coords = explode(',', $coord->location);

            if (count($coords) >= 2) {
                $coordinatesArray[] = [
                    'id' => $coord->id,
                    'title' => $coord->title,
                    'occupancy' => $coord->occupancy,
                    'price' => $coord->price,
                    'location' => [$coords[0], $coords[1]]
                ];
            }
        }

        if ($data) {
            return $coordinatesArray;
        }

        $minutes = 1440;

        $countries = Cache::remember('countries', $minutes, function () {
            return DB::table('booking_data')
                ->select('country', 'city')
                ->distinct()
                ->get()
                ->groupBy('country')
                ->map(function ($item) {
                    return $item->pluck('city')->toArray();
                });
        });
        
        $cities = Cache::remember('cities', $minutes, function () {
            return DB::table('booking_data')
                ->select('city')
                ->distinct()
                ->pluck('city')
                ->toArray();
        });
        
        $types = Cache::remember('types', $minutes, function () {
            return DB::table('booking_data')
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->toArray();
        });
        
        $facilities = Cache::remember('facilities', $minutes, function () {
            return DB::table('facilities')->get();
        });

        return Inertia::render('BookingDataMap', [
            'locations' => $coordinatesArray,
            'countries' => $countries,
            'cities' => $cities,
            'types' => $types,
            'facilities' => $facilities
        ]);
    }


    public function booking_data_map_card ($booking_id)
    {
        $booking_data = DB::table('booking_data')
        ->select('id', 'title', 'description', 'star', 'images', 'location', 'type')
        ->where('id', $booking_id)
        ->get();

        $rooms = DB::table('rooms')
                ->where('booking_id', $booking_id)
                ->get();


        // Получение id из booking_facilities для заданного booking_id
        $facilityIds = DB::table('booking_facilities')->where('booking_id', $booking_id)->pluck('facilities_id');
 
        // Получение названий удобств из facilities на основе полученных id
        $facilities = DB::table('facilities')->whereIn('id', $facilityIds)->pluck('title');
        

        $booking_data[0]->rooms = $rooms;
        $booking_data[0]->facilities = $facilities;


        return $booking_data[0];
    }

    public function booking_data_filters(Request $request)
    {
        $data = $request->json()->all();
    
        $filterTitle = $data['title'];
        $filterCountry = $data['country'];
        $filterCity = $data['city'];
        $filterType = $data['type'];
        $filterFacilities = $data['facilities'];
        $filterPrice = $data['price'];
        $filterSort = $data['sort'];
    
        $query = DB::table('booking_data')
            ->select('booking_data.id',
                    'booking_data.images',
                    'booking_data.title',
                    'booking_data.city',
                    'booking_data.type',
                    // 'booking_data.price',
                    'booking_data.star',
                    'booking_data.score',
                    DB::raw('COUNT(rooms.id) as types_rooms'),
                    DB::raw('SUM(rooms.max_available) as count_rooms'),
                    DB::raw('MIN(rooms.price) as min_price'),
                    DB::raw('MAX(rooms.price) as max_price'),
                    DB::raw('AVG(rooms.occupancy) as occupancy_rate')
                    )
            ->leftJoin('rooms', 'booking_data.id', '=', 'rooms.booking_id');
    
        if (empty($filterSort) || $filterSort == null) {
            $query->orderBy('star', 'desc')->orderBy('review_count', 'desc')->orderBy('score', 'desc');
        }
    
        if (!empty($filterTitle)) {
            $query->where('title', 'like', '%' . $filterTitle . '%');
        }
        if (!empty($filterCountry)) {
            $query->where('country', $filterCountry);
        }
        if (!empty($filterCity)) {
            $query->whereIn('city', $filterCity);
        }
        if (!empty($filterType)) {
            $query->whereIn('type', $filterType);
        }
        if (!empty($filterFacilities)) {
            $query->whereHas('facilities', function ($query) use ($filterFacilities) {
                $query->whereIn('facilities_id', $filterFacilities);
            });
        }
        if (!empty($filterPrice)) {
            if (isset($filterPrice['min'])) $query->where('min_price', '>=', $filterPrice['min']);
            if (isset($filterPrice['max'])) $query->where('max_price', '<=', $filterPrice['max']);
        }
        if (!empty($filterSort)) {
            if ($filterSort == 'price') {
                $query->orderBy('price', 'desc');
            }
            elseif ($filterSort == 'rate') {
                $query->orderBy('score', 'desc');
            }
            elseif ($filterSort == 'occupancy') {
                $query->orderByRaw('AVG(rooms.occupancy) DESC');
            }
            elseif ($filterSort == 'room_type') {
                $query->orderByRaw('COUNT(DISTINCT rooms.room_type) DESC');
            }
            elseif ($filterSort == 'room_count') {
                $query->orderByRaw('SUM(rooms.max_available) DESC');
            }
        }
    
        $data = $query->groupBy('booking_data.id')->paginate(12);
    
        foreach ($data as $item) {
            $item->rooms = DB::table('rooms')->where('booking_id', $item->id)->get();
        }
    
        return $data;
    }
    
    public function setting_priority () 
    {
        $priority = DB::table('booking_data')
                    ->where('priority', '>', 0)
                    ->orderBy('priority', 'desc')
                    ->get();

        return Inertia::render('SettingPriorityPage', [
            'priority' => $priority
        ]);
    }

    public function priority_edit (Request $request)
    {
        if ($request->id && $request->show_priority) {
            DB::table('booking_data')->where('id', $request->id)->update(['priority' => $request->priority]);
        }

        if ($request->id && $request->show_forecast_price) {
            DB::table('booking_data')->where('id', $request->id)->update(['forecast_price' => $request->forecast_price]);
        }

        return DB::table('booking_data')
            ->where('priority', '>', 0)
            ->orderBy('priority', 'desc')
            ->get();
    }

    public function get_report(Request $request)
    {
        $data = DB::table('booking_data')
            ->whereIn('booking_data.id', $request->input('id'))
            ->leftJoin('rooms', 'booking_data.id', '=', 'rooms.booking_id')
            ->select([
                'booking_data.*',
                DB::raw('JSON_ARRAYAGG(JSON_OBJECT(
                    "room_type", rooms.room_type,
                    "max_available", rooms.max_available,
                    "occupancy", rooms.occupancy
                )) AS rooms'),
            ])
            ->groupBy('booking_data.id')
            ->get();


        return Inertia::render('GetReport', [
            'data' => $data,
        ]);
    }

    public function get_all(Request $request)
    {
        $booking_id = $request->id;
        // $booking_data = DB::table('booking_data')->where('id', $booking_id)->get();

        // $facilityIds = DB::table('booking_facilities')->where('booking_id', $booking_id)->pluck('facilities_id');
        // $facilities = DB::table('facilities')->whereIn('id', $facilityIds)->pluck('title');

        $rooms = DB::table('rooms')->where('booking_id', $booking_id)->get();
        $rooms_2_day = DB::table('rooms_2_day')->where('booking_id', $booking_id)->get();

        return [
            // "booking_data" => $booking_data
            // "facilities" => $facilities,
            "rooms" => $rooms,
            "rooms_2_day" => $rooms_2_day
          ];
    }
}
