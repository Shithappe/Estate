<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class booking_data extends Controller
{
    public function index(Request $request)
    {
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


        $maxAvailableRooms = DB::table('rooms_30_day')
            ->select('room_type', DB::raw('MAX(max_available_rooms) AS max_available'))
            ->where('booking_id', $request->booking_id)
            ->groupBy('room_type')
        ->get();

        if (count($maxAvailableRooms) == 0) {
            $maxAvailableRooms = DB::table('rooms_2_day')
            ->select('room_type', DB::raw('MAX(available_rooms) AS max_available'))
            ->where('booking_id', $request->booking_id)
            ->groupBy('room_type')
            ->get();
        }



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
                'occupancy' => $occupancy,
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


        $filteredData = $query->select('id', 'title', 'location')->get();

        $coordinatesArray = [];

        foreach ($filteredData as $coord) {
            $coords = explode(',', $coord->location);

            if (count($coords) >= 2) {
                $coordinatesArray[] = [
                    'id' => $coord->id,
                    'title' => $coord->title,
                    'location' => [$coords[0], $coords[1]]
                ];
            }
        }

        if ($data) {
            return $coordinatesArray;
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

        return Inertia::render('BookingDataMap', [
            'locations' => $coordinatesArray,
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

        $rooms = DB::table('room_cache')
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

    public function booking_data_filters (Request $request)
    {
        $data = $request->json()->all();

        $filterTitle = $data['title'];
        $filterCity = $data['city'];
        $filterType = $data['type'];
        $filterFacilities = $data['facilities'];
        $filterPrice = $data['price'];

        $query = DB::table('booking_data')
                    ->orderBy('star', 'desc')
                    ->orderBy('review_count', 'desc')
                    ->orderBy('score', 'desc');

        if (!empty($filterTitle)) {
            $query->where('title', 'like', '%' . $filterTitle . '%');
        }
        if (!empty($filterCity)) {
            $query->whereIn('city', $filterCity);
        }
        if (!empty($filterType)) {
            $query->whereIn('type', $filterType);
        }
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
