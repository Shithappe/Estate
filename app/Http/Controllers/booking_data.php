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
                    // DB::raw('AVG(rooms.occupancy) as occupancy')
                    'booking_data.occupancy as occupancy',
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
    
    public function booking_data_rate(Request $request)
    {
        $rooms = NULL;
        if (isset($request->checkin) && isset($request->checkout)){
            $rooms = DB::table('rooms_2_day')
                ->where('booking_id', $request->booking_id)
                ->where('checkin', '>=', $request->checkin)
                ->where('checkin', '<', $request->checkout)
                ->whereRaw('DATE(checkin) = DATE(created_at)')
                ->get();
                // return $rooms;
        }
        else {
            $rooms = DB::table('rooms_2_day')
            ->where('booking_id', $request->booking_id)
                ->whereDate('checkin', '=', DB::raw('DATE(created_at)'))
            ->get();
        }

        $maxAvailableRooms = DB::table('rooms_id')
            ->select('room_id', 'room_type', DB::raw('MAX(max_available) AS max_available'), 'active', DB::raw('MAX(price) AS price'))
            ->where('booking_id', $request->booking_id)
            ->groupBy('room_id', 'active')
            ->get();

        // if ($maxAvailableRooms->isEmpty()) {
        //     $maxAvailableRooms = DB::table('rooms_2_day')
        //         ->select('room_type', DB::raw('MAX(available_rooms) AS max_available'), DB::raw('MAX(price) AS price'))
        //     ->where('booking_id', $request->booking_id)
        //         ->groupBy('room_type')
        //     ->get();
        // }

        $allHaveRoomId = $rooms->every(function ($room) {
            return isset($room->room_id) && !empty($room->room_id);
        });

        
        $groupByField = $allHaveRoomId ? 'room_id' : 'room_type';
        $groupedRooms = $rooms->groupBy($groupByField);
        // return $groupedRooms;
        
        $price_avg = 0;
        $resultArray = [];
        foreach ($groupedRooms as $groupKey => $group) {
            // Находим соответствующую запись в $maxAvailableRooms
            $maxAvailableRoom = $maxAvailableRooms->first(function ($item) use ($groupKey, $groupByField) {
                return $item->$groupByField == $groupKey;
            });
    
            // return [$maxAvailableRoom];
            // Если запись найдена и цена не равна NULL, продолжаем вычисления
            if ($maxAvailableRoom) { // && $maxAvailableRoom->price !== null
                // Сумма свободных комнат по типу
                $sum = $group->sum('available_rooms');
                $price_avg = $maxAvailableRoom->price;
                if ($group->whereNotNull('price')->count() > 0) {
                    // dd($maxAvailableRoom);
                    $price_avg = round($group->sum('price') / $group->whereNotNull('price')->count(), 0);
                }
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

            
            // проверка на наличие room_type && occupancy перед добавлением к результату 
            if ( 
                ((!is_null($groupKey) && $groupKey !== '') || 
                (!is_null($maxAvailableRoom) && !is_null($maxAvailableRoom->room_type) && $maxAvailableRoom->room_type !== '')) &&
                $occupancy > 0
            ) {
                $room_type = !$allHaveRoomId ? $groupKey : ($maxAvailableRoom ? $maxAvailableRoom->room_type : null);
                
                if (!is_null($room_type) && $room_type !== '') {
                    $resultArray[] = [
                        'room_id' => $allHaveRoomId ? $groupKey : null,
                        'room_type' => $room_type,
                        'active' => $maxAvailableRoom ? $maxAvailableRoom->active : null,
                        'price' => $price_avg ?? null,
                        'occupancy' => $occupancy
                    ];
                }
            }

        }

        // сортировка результата по цене
        usort($resultArray, function($a, $b) {
            // Сначала проверяем на наличие occupancy равного -1
            if ($a['occupancy'] == -1 && $b['occupancy'] != -1) {
                return 1;
            }
            if ($a['occupancy'] != -1 && $b['occupancy'] == -1) {
                return -1;
            }
        
            // Если оба элемента имеют одинаковое значение occupancy (-1 или не -1), сортируем по price
            return $a['price'] <=> $b['price'];
        });

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
                    DB::raw('AVG(rooms.occupancy) as occupancy')
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

        $rooms = DB::table('rooms_id')->where('booking_id', $booking_id)->get();
        $rooms_2_day = DB::table('rooms_2_day')->where('booking_id', $booking_id)->get();

        return [
            // "booking_data" => $booking_data
            // "facilities" => $facilities,
            "rooms" => $rooms,
            "rooms_2_day" => $rooms_2_day
          ];
    }

    public function for_extension(Request $request)
    {
        $url = $request->url;

        $booking = DB::table('booking_data')->where('link', $url)->first();

        if ($booking) {
            $booking_id = $booking->id;

            $rooms = DB::table('rooms')->where('booking_id', $booking_id)->get();

            return response()->json($rooms);
        } else {
            return response()->json(['error' => 'Booking not found'], 404);
        }
    }

    public function form_submissions(Request $request)
    {
        DB::table('form_submissions')->insert([
            'booking_id' => $request->input('booking_id', null),
            'target' => $request->input('target'),
            'name' => $request->input('name'),
            'phone_number' => $request->input('phone_number'),
            'messenger' => $request->input('messenger'),
            'email' => $request->input('email')
        ]);

        return response()->json(['message' => 'Form submission successful'], 200);
    }

    public function create_list(Request $request)
    {
        // Валидация данных запроса
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'booking_id' => 'nullable|exists:booking_data,id'
        ]);

        // Создание нового списка пользователя
        $listId = DB::table('user_lists')->insertGetId([
            'user_id' => $validatedData['user_id'],
            'name' => $validatedData['name']
        ]);

        if (!empty($validatedData['booking_id'])) {
            DB::table('list_hotels')->insert([
                'list_id' => $listId,
                'booking_id' => $validatedData['booking_id']
            ]);
        }

        // Возврат успешного ответа
        return response()->json([
            'message' => 'List created successfully',
            'list_id' => $listId,
        ], 201);
    }

    public function add_to_list(Request $request)
    {
        // Валидация данных запроса
        $validatedData = $request->validate([
            'list_id' => 'required|exists:user_lists,id',
            'booking_id' => 'required|exists:booking_data,id',
        ]);

        // Проверка, не существует ли уже запись с этим booking_id в этом списке
        $exists = DB::table('list_hotels')
            ->where('list_id', $validatedData['list_id'])
            ->where('booking_id', $validatedData['booking_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'The hotel is already in the list',
            ], 400);
        }

        // Добавление отеля в список
        DB::table('list_hotels')->insert([
            'list_id' => $validatedData['list_id'],
            'booking_id' => $validatedData['booking_id']
        ]);

        // Возврат успешного ответа
        return response()->json([
            'message' => 'Hotel added to list successfully',
        ], 201);
    }

    public function get_list(Request $request)
    {
        // Валидация данных запроса
        $validatedData = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'list_id' => 'nullable|exists:user_lists,id',
        ]);

        if (!empty($validatedData['list_id'])) {
            // Получение определенного списка
            $list = DB::table('user_lists')
                ->where('id', $validatedData['list_id'])
                ->first();

            if ($list) {
                $hotels = DB::table('list_hotels')
                    ->join('booking_data', 'list_hotels.booking_id', '=', 'booking_data.id')
                    ->where('list_hotels.list_id', $validatedData['list_id'])
                    ->select('booking_data.*')
                    ->get();

                return response()->json([
                    'list' => $list,
                    'hotels' => $hotels,
                ], 200);
            } else {
                return response()->json(['message' => 'List not found'], 404);
            }
        } elseif (!empty($validatedData['user_id'])) {
            // Получение всех списков пользователя
            $lists = DB::table('user_lists')
                ->where('user_id', $validatedData['user_id'])
                ->get();

            $result = [];
            foreach ($lists as $list) {
                $hotels = DB::table('list_hotels')
                    ->join('booking_data', 'list_hotels.booking_id', '=', 'booking_data.id')
                    ->where('list_hotels.list_id', $list->id)
                    ->select('booking_data.*')
                    ->get();

                $result[] = [
                    'list' => $list,
                    'hotels' => $hotels,
                ];
            }

            return response()->json($result, 200);
        } else {
            return response()->json(['message' => 'Invalid request'], 400);
        }
    }

    public function list(Request $request)
    {
        // Получение ID авторизованного пользователя
        $userId = $request->user()->id;

        // Получение всех списков пользователя
        $lists = DB::table('user_lists')
            ->where('user_id', $userId)
            ->get();

        foreach ($lists as $list) {
            $hotels = DB::table('list_hotels')
                ->join('booking_data', 'list_hotels.booking_id', '=', 'booking_data.id')
                ->where('list_hotels.list_id', $list->id)
                ->select('booking_data.*')
                ->get();

            $list->hotels = $hotels;
        }

        // Возврат шаблона Inertia с данными списков
        return Inertia::render('ListBookingData', [
            'lists' => $lists
        ]);
    }

    public function list_show(Request $request, $list_id)
    {
        // Получение ID авторизованного пользователя
        $userId = $request->user()->id;

        // Получение списка пользователя
        $list = DB::table('user_lists')
            ->where('id', $list_id)
            ->where('user_id', $userId)
            ->first();

        if (!$list) {
            return response()->json(['message' => 'List not found or you do not have permission to view this list'], 404);
        }

        // Получение отелей в списке
        $hotels = DB::table('list_hotels')
            ->join('booking_data', 'list_hotels.booking_id', '=', 'booking_data.id')
            ->where('list_hotels.list_id', $list_id)
            ->select('booking_data.*')
            ->get();

        $list->hotels = $hotels;

        // Возврат шаблона Inertia с данными списка
        return Inertia::render('ListShowBookingData', [
            'list' => $list
        ]);
    }
}
