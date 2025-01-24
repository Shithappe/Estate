<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

class booking_data extends Controller
{
    public function getLists($user_id) //auth()->id()
    {
        $lists = DB::table('user_lists')
            ->where('user_id', $user_id)
            ->get()
            ->groupBy('type'); 

        $listsFormatted = [
            'unit' => $lists->get('unit', collect()),     // Если типа 'unit' нет, вернёт пустую коллекцию
            'complex' => $lists->get('complex', collect()) // Если типа 'complex' нет, вернёт пустую коллекцию
        ];

        return $listsFormatted;
    }

    public function index(Request $request)
    {
        $data = DB::table('booking_data')
            ->select(
                'booking_data.id', 
                'booking_data.slug',
                'booking_data.static_images',
                'booking_data.images',
                'booking_data.title', 
                'booking_data.city', 
                'booking_data.type', 
                'booking_data.min_price',
                'booking_data.max_price',
                'booking_data.star', 
                'booking_data.score',
                DB::raw('COUNT(rooms_id.room_id) as types_rooms'),
                DB::raw('SUM(rooms_id.max_available) as count_rooms'),
                'booking_data.occupancy as occupancy',
                DB::raw('ROUND(booking_data.min_price * (booking_data.occupancy / 100) * 30) AS rental_income_min'),
                DB::raw('ROUND(booking_data.max_price * (booking_data.occupancy / 100) * 30) AS rental_income_max'),
                DB::raw('
                    ROUND(
                        IF(
                            booking_data.forecast_price IS NULL OR booking_data.forecast_price = "",
                            (booking_data.occupancy / 100) * 365 * ((booking_data.min_price + booking_data.max_price) / 2) * 10 * 0.5,
                            booking_data.forecast_price
                        )
                    ) as forecast_price
                '),
            )
            ->leftJoin('rooms_id', 'booking_data.id', '=', 'rooms_id.booking_id')
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
            'facilities' => $facilities,
            'lists' => $this->getLists(auth()->id()),
        ]);
    }

    public function getComplexPage($slug)
    {
        $booking = DB::table('booking_data')->where('slug', $slug)->first();
        if (!$booking) return redirect('/');

        // Получение id из booking_facilities для заданного booking_id
        $facilityIds = DB::table('booking_facilities')->where('booking_id', $booking->id)->pluck('facilities_id');
 
        // Получение названий удобств из facilities на основе полученных id
        $facilities = DB::table('facilities')->whereIn('id', $facilityIds)->pluck('title');        

        return Inertia::render('SingleBookingData', [
            'booking' => $booking,
            'lists' => $this->getLists(auth()->id()),
            'facilities' => $facilities,
            'meta' => [
                'og:title' => $booking->title,
                'og:description' => 'Estate market is a platform for real estate analysis and investment. We provide a wide range of tools for real estate analysis, investment, and management.',
                'og:image' => explode(',', $booking->images)[0],
                'og:url' => 'https://estatemarket.io/complex/' . $slug
            ],
        ]);
    } 

    public function booking_page($booking_id)
    {
        $booking = DB::table('booking_data')->where('id', $booking_id)->first();

        // Получение id из booking_facilities для заданного booking_id
        $facilityIds = DB::table('booking_facilities')->where('booking_id', $booking_id)->pluck('facilities_id');
 
        // Получение названий удобств из facilities на основе полученных id
        $facilities = DB::table('facilities')->whereIn('id', $facilityIds)->pluck('title');

        return Inertia::render('SingleBookingData', [
            'booking' => $booking,
            'lists' => $this->getLists(auth()->id()),
            'facilities' => $facilities,
        ]);
    } 

    public function booking_data_rate(Request $request)
    {
        $bookingId = $request->booking_id;
        $bookingIds = $request->booking_ids;
        $roomIds = $request->rooms_ids; // Получаем массив комнат
        $checkinDate = $request->checkin;
        $checkoutDate = $request->checkout;
    
        // Проверяем, пришел ли один объект, массив отелей или массив комнат
        if ($bookingId) {
            // Обрабатываем один объект (один отель)
            return $this->processBookingData($bookingId, $checkinDate, $checkoutDate, true);
        } elseif (is_array($bookingIds) && !empty($bookingIds)) {
            // Обрабатываем массив отелей
            $resultArray = [];
            foreach ($bookingIds as $id) {
                $resultArray[] = $this->processBookingData($id, $checkinDate, $checkoutDate, false);
            }
            return $resultArray;
        } elseif (is_array($roomIds) && !empty($roomIds)) {
            // Обрабатываем массив комнат
            return $this->processRoomData($roomIds, $checkinDate, $checkoutDate);
        } else {
            return response()->json(['message' => 'Invalid booking_id, booking_ids, or rooms_ids'], 400);
        }
    }
    
    // Вспомогательная функция для обработки данных бронирования
    private function processBookingData($bookingId, $checkinDate, $checkoutDate, $sortResults)
    {
        // Получение названия отеля только если это одиночный запрос
        $bookingTitle = !$sortResults ? DB::table('booking_data')
            ->where('id', $bookingId)
            ->value('title') : null;
    
        // Запрос к базе данных для получения всех необходимых данных
        $rooms = DB::table('rooms_2_day as r2d')
            ->join('rooms_id as ri', 'r2d.room_id', '=', 'ri.room_id')
            ->select('r2d.room_id',
                DB::raw('SUM(r2d.available_rooms) AS sum'),
                DB::raw('COUNT(r2d.available_rooms) AS count'),
                DB::raw('MAX(ri.max_available) AS max_available'),
                'ri.room_type',
                DB::raw('ROUND(AVG(COALESCE(r2d.price, ri.price))) AS price'),
                'ri.active',
                DB::raw('
                    ROUND(
                        IF(
                            ri.estimated_price IS NULL OR ri.estimated_price = "",
                            (ri.occupancy / 100) * 365 * ri.price * 10 * 0.5,
                            ri.estimated_price
                        )
                    ) as estimated_price
                '),
                DB::raw('COUNT(*) AS record_count'),
            )
            ->where('r2d.booking_id', $bookingId)
            ->whereBetween('r2d.checkin', [$checkinDate, $checkoutDate])
            // ->when($checkinDate && $checkoutDate, function ($query) use ($checkinDate, $checkoutDate) {
            //     return $query->where('r2d.checkin', '>=', $checkinDate)
            //      ->where('r2d.checkin', '<=', $checkoutDate);
            // })
            ->groupBy('r2d.room_id')
            ->get();

        $roomsOutOfRange = DB::table('rooms_2_day as r2d')
            ->join('rooms_id as ri', 'r2d.room_id', '=', 'ri.room_id')
            ->select('r2d.room_id',
                DB::raw('SUM(r2d.available_rooms) AS sum'),
                DB::raw('COUNT(r2d.available_rooms) AS count'),
                DB::raw('MAX(ri.max_available) AS max_available'),
                'ri.room_type',
                DB::raw('ROUND(AVG(COALESCE(r2d.price, ri.price))) AS price'),
                'ri.active',
                DB::raw('
                    ROUND(
                        IF(
                            ri.estimated_price IS NULL OR ri.estimated_price = "",
                            (ri.occupancy / 100) * 365 * ri.price * 10 * 0.5,
                            ri.estimated_price
                        )
                    ) as estimated_price
                '),
                DB::raw('COUNT(*) AS record_count'),
                DB::raw("'out_of_range' as range_status") // Пометка для вне диапазона
            )
            ->where('r2d.booking_id', $bookingId)
            ->whereNotBetween('r2d.checkin', [$checkinDate, $checkoutDate])
            ->groupBy('r2d.room_id')
            ->get();

        $rooms = $rooms->merge($roomsOutOfRange)->unique('room_id');

        return $this->calculateOccupancy($rooms, $bookingTitle, $sortResults);
    }
    
    // Вспомогательная функция для обработки данных комнат
    private function processRoomData($roomIds, $checkinDate, $checkoutDate)
    {
        // Используем кэширование для оптимизации
        $roomIdsKey = implode('_', $roomIds); // Превращаем массив в строку с разделителем "_"
        $cacheKey = "room_data_{$roomIdsKey}_{$checkinDate}_{$checkoutDate}";
    
        // Используем кэширование для оптимизации
        $rooms = Cache::remember($cacheKey, 60, function () use ($roomIds, $checkinDate, $checkoutDate) {
            return DB::table('rooms_2_day as r2d')
                ->join('rooms_id as ri', 'r2d.room_id', '=', 'ri.room_id')
                ->join('booking_data as bd', 'ri.booking_id', '=', 'bd.id')
                ->select('r2d.room_id',
                    DB::raw('SUM(r2d.available_rooms) AS sum'),
                    DB::raw('COUNT(r2d.available_rooms) AS count'),
                    'ri.max_available',
                    'ri.room_type',
                    'ri.price',
                    'ri.active',
                    DB::raw('
                        ROUND(
                            IF(
                                ri.estimated_price IS NULL OR ri.estimated_price = "",
                                (ri.occupancy / 100) * 365 * ri.price * 10 * 0.5,
                                ri.estimated_price
                            )
                        ) as estimated_price
                    '),
                    'bd.title as booking_title',
                    DB::raw('COUNT(r2d.id) AS record_count'),
                )
                ->whereIn('r2d.room_id', $roomIds)
                ->whereBetween('r2d.created_at', [$checkinDate, $checkoutDate])
                ->groupBy('bd.title', 'r2d.room_id', 'ri.max_available', 'ri.room_type', 'ri.price', 'ri.active')
                ->get();
        });
        return $this->calculateOccupancy($rooms, null, false);
    }
    

    // Функция для расчета занятости и создания итогового массива
    private function calculateOccupancy($rooms, $bookingTitle, $sortResults)
    {
        // return $rooms;
        $roomsArray = [];
        foreach ($rooms as $room) {
            $sum = $room->sum;
            $count = $room->count;
            $maxAvailable = $room->max_available;

            // Расчет занятости
            if (isset($room->range_status) && $room->range_status === 'out_of_range') {
                $occupancy = 100;
            }
            else {
                $occupancy = ((($maxAvailable - ($sum / $count)) / $maxAvailable) * 100);
                if ($occupancy < 0) $occupancy = -1;
            }
            

            // Добавляем результат в массив
            $roomsArray[] = [
                'room_id' => $room->room_id,
                'room_type' => $room->room_type,
                'active' => $room->active,
                'record_count' => $room->record_count,
                'price' => $room->price,
                'occupancy' => round($occupancy),
                'profit' => round($room->record_count * $occupancy / 100 * $room->price),
                'estimated_price' => $room->estimated_price,
                'booking_title' => $room->booking_title ?? $bookingTitle // Используем booking_title из JOIN или переданное значение
            ];
        }

        // Сортируем результат только если это одиночный запрос
        if ($sortResults) {
            usort($roomsArray, function($a, $b) {
                if ($a['occupancy'] == -1 && $b['occupancy'] != -1) {
                    return 1;
                }
                if ($a['occupancy'] != -1 && $b['occupancy'] == -1) {
                    return -1;
                }
                return $a['price'] <=> $b['price'];
            });
        }

        return $roomsArray;
    }
    

    public function booking_data_map(Request $request)
    {
        $data = $request->json()->all();

        $filterCity = $data['city'] ?? null;
        $filterType = $data['type'] ?? null;
        $filterFacilities = $data['facilities'] ?? null;
        $filterPrice = $data['price'] ?? null;
    
        $query = DB::table('booking_data')
            ->select('id', 'title', 'occupancy', 'price', 'min_price', 'max_price', 'location');
    
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
            if (isset($filterPrice['min_min'])) $query->where('min_price', '>=', $filterPrice['min_min']);
            if (isset($filterPrice['min_max'])) $query->where('min_price', '<=', $filterPrice['min_max']);
            if (isset($filterPrice['max_min'])) $query->where('max_price', '>=', $filterPrice['max_min']);
            if (isset($filterPrice['max_max'])) $query->where('max_price', '<=', $filterPrice['max_max']);
        }
    
    
        $filteredData = $query->get();

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
            'facilities' => $facilities,
            'lists' => $this->getLists(auth()->id())
        ]);
    }
    

    public function booking_data_map_card ($booking_id)
    {
        $booking_data = DB::table('booking_data')
        ->select('id', 'title', 'description', 'star', 'images', 'location', 'type', 'occupancy', 'min_price', 'max_price')
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
    
        $filterTitle = $data['title'] ?? null;
        $filterCountry = $data['country'] ?? null;
        $filterCity = $data['city'] ?? null;
        $filterType = $data['type'] ?? null;
        $filterFacilities = $data['facilities'] ?? null;
        $filterPrice = $data['price'] ?? null;
        $filterSort = $data['sort'] ?? null;
    
        $query = DB::table('booking_data')
            ->select(
                'booking_data.id',
                'booking_data.slug',
                'booking_data.images',
                'booking_data.title',
                'booking_data.city',
                'booking_data.type',
                'booking_data.star',
                'booking_data.score',
                'booking_data.min_price',
                'booking_data.max_price',
                DB::raw('COUNT(rooms_id.room_id) as types_rooms'),
                DB::raw('SUM(rooms_id.max_available) as count_rooms'),
                'booking_data.occupancy as occupancy',
                DB::raw('ROUND(booking_data.min_price * (booking_data.occupancy / 100) * 30) AS rental_income_min'),
                DB::raw('ROUND(booking_data.max_price * (booking_data.occupancy / 100) * 30) AS rental_income_max'),
                DB::raw('
                    ROUND(
                        IF(
                            booking_data.forecast_price IS NULL OR booking_data.forecast_price = "",
                            (booking_data.occupancy / 100) * 365 * ((MIN(rooms_id.price) + MAX(rooms_id.price)) / 2) * 10 * 0.5,
                            booking_data.forecast_price
                        )
                    ) as forecast_price
                ')
            )
            ->leftJoin('rooms_id', 'booking_data.id', '=', 'rooms_id.booking_id')
            ->groupBy(
                'booking_data.id',
                'booking_data.images',
                'booking_data.title',
                'booking_data.city',
                'booking_data.type',
                'booking_data.star',
                'booking_data.score',
                'booking_data.min_price',
                'booking_data.max_price',
                'booking_data.occupancy'
            );

        $query->havingRaw('min_price IS NOT NULL')->havingRaw('max_price IS NOT NULL');
    
        // Apply filters
        if (!empty($filterTitle)) {
            $query->where('booking_data.title', 'like', '%' . $filterTitle . '%');
        }
        if (!empty($filterCountry)) {
            $query->where('booking_data.country', $filterCountry);
        }
        if (!empty($filterCity)) {
            $query->whereIn('booking_data.city', $filterCity);
        }
        if (!empty($filterType)) {
            $query->whereIn('booking_data.type', $filterType);
        }
        if (!empty($filterFacilities)) {
            $query->whereHas('facilities', function ($query) use ($filterFacilities) {
                $query->whereIn('facilities_id', $filterFacilities);
            });
        }
    
        // Фильтрация по цене
        if (!empty($filterPrice)) {
            if (isset($filterPrice['min_min'])) {
                $query->having('min_price', '>=', $filterPrice['min_min']);
            }
            if (isset($filterPrice['min_max'])) {
                $query->having('min_price', '<=', $filterPrice['min_max']);
            }
            if (isset($filterPrice['max_min'])) {
                $query->having('max_price', '>=', $filterPrice['max_min']);
            }
            if (isset($filterPrice['max_max'])) {
                $query->having('max_price', '<=', $filterPrice['max_max']);
            }
        }
    
        // Сортировка
        if (!empty($filterSort)) {
            $sortValue = $filterSort['value'] ?? null;
            $orderBy = $filterSort['orderBy'] ?? 'desc';

            
            if ($sortValue == 'price') {
                // dd($filterSort, $sortValue, $orderBy);
                $query->orderBy('min_price', $orderBy);
            } elseif ($sortValue == 'rate') {
                $query->orderBy('score', $orderBy);
            } elseif ($sortValue == 'occupancy') {
                $query->orderBy('booking_data.occupancy', $orderBy);
            } elseif ($sortValue == 'room_type') {
                $query->orderByRaw($orderBy === 'asc' ? 'COUNT(DISTINCT rooms_id.room_type) ASC' : 'COUNT(DISTINCT rooms_id.room_type) DESC');
            } elseif ($sortValue == 'room_count') {
                $query->orderByRaw($orderBy === 'asc' ? 'SUM(rooms_id.max_available) ASC' : 'SUM(rooms_id.max_available) DESC');
            }
        }

        // Добавление дефолтной сортировки
        $query->orderBy('review_count', 'desc')
              ->orderBy('score', 'desc')
              ->orderBy('star', 'desc');
    
        $data = $query->paginate(12);
    
        // Добавляем комнаты для каждого объекта
        // foreach ($data as $item) {
        //     $item->rooms = DB::table('rooms_id')->where('booking_id', $item->id)->get();
        // }
    
        return $data;
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
        $rooms = DB::table('rooms_id')->where('booking_id', $booking_id)->get();
        $rooms_2_day = DB::table('rooms_2_day as r2')
            ->join('rooms_id as ri', 'r2.room_id', '=', 'ri.room_id')
            ->where('r2.booking_id', $booking_id)
            ->select(
                'r2.id',
                'r2.booking_id',
                'r2.room_id',
                'ri.room_type',
                'r2.available_rooms',
                'r2.price',
                'r2.checkin',
                'r2.checkout'
            )
            ->orderBy('r2.checkin', 'desc')
            ->get();

        return [
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
            'type' => 'required|string|in:complex,unit',
            'name' => 'required|string|max:255',
            'item_id' => 'nullable' // item_id может быть как одиночным booking_id, так и массивом booking_id
        ]);
    
        // Создание нового объекта списка
        $list = [
            'user_id' => $validatedData['user_id'],
            'type' => $validatedData['type'],
            'name' => $validatedData['name'],
            'share_token' => Str::random(32),
        ];
    
        // Вставка данных в таблицу и получение ID
        $list['id'] = DB::table('user_lists')->insertGetId($list);
        $list['hotels'] = [];
    
        // Проверка, является ли item_id массивом или одиночным значением
        if (!empty($validatedData['item_id'])) {
            // Если item_id массив, добавляем каждый элемент
            if (is_array($validatedData['item_id'])) {
                $hotels = [];
                foreach ($validatedData['item_id'] as $itemId) {
                    $hotels[] = [
                        'list_id' => $list['id'],
                        'booking_id' => $itemId
                    ];
                }
                DB::table('list_hotels')->insert($hotels); // Вставка массива записей за один запрос
                $list['hotels'] = $hotels;
            } else {
                // Если item_id одиночное значение, добавляем один элемент
                DB::table('list_hotels')->insert([
                    'list_id' => $list['id'],
                    'booking_id' => $validatedData['item_id']
                ]);
                $list['hotels'] = [
                    [
                        'list_id' => $list['id'],
                        'booking_id' => $validatedData['item_id']
                    ]
                ];
            }
        }
    
        // Возврат успешного ответа с объектом списка
        return response()->json([
            'list' => $list
        ], 201);
    }
    
    public function add_to_list(Request $request)
    {
        // Валидация данных запроса
        $validatedData = $request->validate([
            'list_id' => 'required|integer',
            'booking_id' => 'required',  // Может быть одиночным значением или массивом
        ]);

        // Преобразуем booking_id в массив, если это одиночное значение
        $bookingIds = is_array($validatedData['booking_id'])
            ? $validatedData['booking_id']
            : [$validatedData['booking_id']];

        $listId = $validatedData['list_id'];

        // Проверка существующих записей в списке
        $existingBookings = DB::table('list_hotels')
            ->where('list_id', $listId)
            ->whereIn('booking_id', $bookingIds)
            ->pluck('booking_id')
            ->toArray();

        // Отфильтровываем идентификаторы отелей, которые уже существуют в списке
        $newBookingIds = array_diff($bookingIds, $existingBookings);

        // Добавление новых отелей в список
        $insertData = array_map(function ($bookingId) use ($listId) {
            return [
                'list_id' => $listId,
                'booking_id' => $bookingId
            ];
        }, $newBookingIds);

        if (!empty($insertData)) {
            DB::table('list_hotels')->insert($insertData);
        }

        // Возврат успешного ответа
        return response()->json([
            'message' => 'Hotels added to list successfully',
            'added' => $newBookingIds,
            'skipped' => $existingBookings,
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
            $itemsCount = DB::table('list_hotels')
                ->where('list_id', $list->id)
                ->count();

            $list->items_count = $itemsCount;
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
    
        // Если тип списка complex, получаем количество элементов (отелей)
        if ($list->type == 'complex') {
            $items = DB::table('list_hotels')
                ->join('booking_data', 'list_hotels.booking_id', '=', 'booking_data.id')
                ->leftJoin('rooms_id', 'booking_data.id', '=', 'rooms_id.booking_id')
                ->where('list_hotels.list_id', $list_id)
                ->select(
                    'booking_data.id',
                    'booking_data.slug',
                    'booking_data.static_images',
                    'booking_data.images',
                    'booking_data.title',
                    'booking_data.city',
                    'booking_data.type',
                    'booking_data.min_price',
                    'booking_data.max_price',
                    'booking_data.star',
                    'booking_data.score',
                    DB::raw('
                        ROUND(
                            IF(
                                booking_data.forecast_price IS NULL OR booking_data.forecast_price = "",
                                (booking_data.occupancy / 100) * 365 * ((booking_data.min_price + booking_data.max_price) / 2) * 10 * 0.5,
                                booking_data.forecast_price
                            )
                        ) as forecast_price
                    '),
                    DB::raw('COUNT(rooms_id.room_id) as types_rooms'),
                    DB::raw('SUM(rooms_id.max_available) as count_rooms'),
                    'booking_data.occupancy as occupancy'
                )
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
                ->get();
    
            $list->items = $items;
        }
    
        // Если тип списка unit, получаем комнаты, совпадающие по room_id
        if ($list->type == 'unit') {
            $items = DB::table('list_hotels')
                ->join('rooms_id', 'list_hotels.booking_id', '=', 'rooms_id.room_id')
                ->where('list_hotels.list_id', $list_id)
                ->select('rooms_id.*')
                ->get();
    
            $list->items = $items;
        }
    
        // return $list;
        return Inertia::render('ListShowBookingData', [
            'list' => $list,
            'lists' => $this->getLists(auth()->id())
        ]);
    }
    

    public function delete_list(Request $request, $list_id)
    {
        // $userId = $request->user()->id;

        DB::table('user_lists')
            ->where('id', $list_id)
            // ->where('user_id', $userId)
            ->delete();
    }

    public function delete_item_from_list(Request $request, $list_id, $item_id)
    {
        DB::table('list_hotels')
            ->where('list_id', $list_id)
            ->where('booking_id', $item_id)
            ->delete();
    }

    public function update_list(Request $request, $list_id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'nullable|string',
            'privacy_mode' => 'nullable|in:private,link,specific_users',
            'shared_with_users' => 'nullable|array',
            'shared_with_users.*' => 'exists:users,id',
        ]);
        
        // Обновление данных списка
        $updateData = array_filter([
            'name' => $validatedData['name'] ?? null,
            'privacy_mode' => $validatedData['privacy_mode'] ?? null,
        ]);

        DB::table('user_lists')
            ->where('id', $list_id)
            ->where('user_id', $validatedData['user_id'])
            ->update($updateData);
        
        // Обработка добавления пользователей для режима specific_users
        if (isset($validatedData['privacy_mode']) && $validatedData['privacy_mode'] === 'specific_users') {
            DB::table('shared_lists')
                ->where('list_id', $list_id)
                ->delete(); // Удаляем текущих пользователей, чтобы обновить список

            if (!empty($validatedData['shared_with_users'])) {
                foreach ($validatedData['shared_with_users'] as $userId) {
                    DB::table('shared_lists')->insert([
                        'list_id' => $list_id,
                        'shared_with_user_id' => $userId,
                        'created_at' => now(),
                    ]);
                }
            }
        }

        // Генерация ссылки для режима link
        if (isset($validatedData['privacy_mode']) && $validatedData['privacy_mode'] === 'link') {

            $shareToken = DB::table('user_lists')
                ->where('id', $list_id)
                ->where('user_id', $validatedData['user_id'])
                ->value('share_token');

            $shareLink = url('list/share/' . $shareToken);
            return response()->json(['link' => $shareLink], 200);
        }

        return response()->json(['message' => 'List updated successfully']);
    }

    public function accessSharedList($share_token)
    {
        // Получение списка по share_token и проверка на privacy_mode
        $list = DB::table('user_lists')
            ->where('share_token', $share_token)
            ->where('privacy_mode', '=', 'link')
            ->first();
        
        if (!$list) {
            return response()->json(['error' => 'Invalid link'], 404);
        }
    
        // Если тип списка complex, получаем количество элементов (отелей)
        if ($list->type == 'complex') {
            $items = DB::table('list_hotels')
                ->join('booking_data', 'list_hotels.booking_id', '=', 'booking_data.id')
                ->leftJoin('rooms_id', 'booking_data.id', '=', 'rooms_id.booking_id')
                ->where('list_hotels.list_id', $list->id)
                ->select(
                    'booking_data.id',
                    'booking_data.static_images',
                    'booking_data.images',
                    'booking_data.title',
                    'booking_data.city',
                    'booking_data.type',
                    'booking_data.min_price',
                    'booking_data.max_price',
                    'booking_data.star',
                    'booking_data.score',
                    DB::raw('
                        ROUND(
                            IF(
                                booking_data.forecast_price IS NULL OR booking_data.forecast_price = "",
                                (booking_data.occupancy / 100) * 365 * ((booking_data.min_price + booking_data.max_price) / 2) * 10 * 0.5,
                                booking_data.forecast_price
                            )
                        ) as forecast_price
                    '),
                    DB::raw('COUNT(rooms_id.room_id) as types_rooms'),
                    DB::raw('SUM(rooms_id.max_available) as count_rooms'),
                    'booking_data.occupancy as occupancy'
                )
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
                ->get();
        
            $list->items = $items;
        }
    
        // Если тип списка unit, получаем комнаты, совпадающие по room_id
        if ($list->type == 'unit') {
            $items = DB::table('list_hotels')
                ->join('rooms_id', 'list_hotels.booking_id', '=', 'rooms_id.room_id')
                ->where('list_hotels.list_id', $list->id)
                ->select('rooms_id.*')
                ->get();
    
            $list->items = $items;
        }
    
        // Возврат шаблона Inertia с данными списка
        return Inertia::render('ListShowBookingData', [
            'is_share'=> true,
            'list' => $list,
            'lists' => $this->getLists(auth()->id())
        ]);
    }
}
