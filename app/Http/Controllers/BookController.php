<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $cities = DB::table('books')
        ->select('city')
        ->distinct()
        ->pluck('city')
        ->toArray();
        
        $filterCity = $request->query('city');
        $filterMinPrice = $request->query('minPrice');
        $filterMaxPrice = $request->query('maxPrice');

        $query = Book::query();

        if (!empty($filterCity)) {
            $query->where('city', $filterCity);
        }
        if (!empty($filterMinPrice)) {
            $query->where('price', '>=', $filterMinPrice);
        }
        if (!empty($filterMaxPrice)) {
            $query->where('price', '<=', $filterMaxPrice);
        }

        $books = $query->paginate(10);

        foreach ($books as $book) {
            $book->main_image = asset('storage/' . $book->main_image);
            
            $images = json_decode($book->images, true);
            $newImages = [];
        
            foreach ($images as $image) {
                $newImages[] = asset('storage/' . $image);
            }
        
            $book->images = $newImages;
        }

        return Inertia::render('Main', [
            'cities' => $cities,
            'books' => $books,
        ]);
    }
    
    public function getEstate($id)
    {
        $item = Book::where('id', $id)->first();

        $item->main_image = asset('storage/' . $item->main_image);

        $images = json_decode($item->images, true);
        $newImages = [];
        
        foreach ($images as $image) {
            $newImages[] = asset('storage/' . $image);
        }
    
        $item->images = $newImages;

        return Inertia::render('SingleBook', [
            'item' => $item
        ]);
    }

    public function getEstateAdmin(Request $request)
    {
        $cities = DB::table('books')
        ->select('city')
        ->distinct()
        ->pluck('city')
        ->toArray();
        
        $filterCity = $request->query('city');
        $filterMinPrice = $request->query('minPrice');
        $filterMaxPrice = $request->query('maxPrice');

        $query = Book::query();

        if (!empty($filterCity)) {
            $query->where('city', $filterCity);
        }
        if (!empty($filterMinPrice)) {
            $query->where('price', '>=', $filterMinPrice);
        }
        if (!empty($filterMaxPrice)) {
            $query->where('price', '<=', $filterMaxPrice);
        }

        $books = $query->paginate(20);

        foreach ($books as $book) {
            $book->main_image = asset('storage/' . $book->main_image);
            
            $images = json_decode($book->images, true);
            $newImages = [];
        
            foreach ($images as $image) {
                $newImages[] = asset('storage/' . $image);
            }
        
            $book->images = $newImages;
        }

        return $books;
    }

    public function storeOrUpdate(Request $request, $id = null)
    {

        // return $request;

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Пример правила для изображения
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Пример правила для нескольких изображений
        ];
    
        $validatedData = $request->validate($rules);
    
        try {
            $bookData = $request->except('_token', '_method');
    
            if ($request->hasFile('main_image')) {
                $mainImage = $request->file('main_image')->store('main_images', 'public');
                $bookData['main_image'] = $mainImage;
            }
    
            if ($request->hasFile('images')) {
                $imagePaths = [];
    
                foreach ($request->file('images') as $image) {
                    $path = $image->store('images', 'public');
                    $imagePaths[] = $path;
                }
    
                $bookData['images'] = json_encode($imagePaths);
            }
    
            if ($id) {
                $book = Book::findOrFail($id);
                $book->update($bookData);
            } else {
                $book = Book::create($bookData);
            }
    
            return 'ok';
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Book $book)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Book::find($id)->delete();
    }
}
