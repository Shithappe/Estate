<?php

namespace App\Http\Controllers\Admin;

use App\Models\Book;
use App\Http\Requests\BookRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

/**
 * Class BookCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BookCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Book::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/book');
        CRUD::setEntityNameStrings('Book', 'Books');

        $this->crud->addField([
            'name' => 'title',
            'label' => 'Title',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'builder_name',
            'label' => 'Builder Name',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'complex_name',
            'label' => 'Complex Name',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'square',
            'label' => 'Square',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'price_per_meter',
            'label' => 'Price per Meter',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'room_count',
            'label' => 'Room Count',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'floor',
            'label' => 'Floor',
            'type' => 'number',
        ]);

        $this->crud->addField([  // Поле для загрузки главного изображения
            'name' => 'main_image',
            'label' => 'Main Image',
            'type' => 'upload',
            'upload' => true,
            'disk' => 'public',
        ]);

        $this->crud->addField([  // Поле для загрузки нескольких изображений
            'name' => 'images',
            'label' => 'Other images',
            'type' => 'upload_multiple',
            'upload' => true,
            'disk' => 'public',
        ]);

        $this->crud->addField([
            'name' => 'city',
            'label' => 'City',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'district',
            'label' => 'District',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'street',
            'label' => 'Street',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'coordinate',
            'label' => 'Coordinate',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'rate',
            'label' => 'Rate',
            'type' => 'number',
        ]);

        $this->crud->addField([
            'name' => 'property_type',
            'label' => 'Property Type',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'bedrooms_count',
            'label' => 'Bedrooms Count',
            'type' => 'text',
        ]);

        $this->crud->addField([
            'name' => 'source_url',
            'label' => 'Source URL',
            'type' => 'text',
        ]);
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(BookRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    public function store()
    {
        $this->crud->addField([
            'name' => 'main_image',
            'type' => 'image',
            'upload' => true,
            'disk' => 'public',
        ]);
    
        // Добавьте поле для загрузки нескольких изображений
        $this->crud->addField([  // Поле для загрузки нескольких изображений
            'name' => 'images',
            'label' => 'Other images',
            'type' => 'multiple_images', // Используйте тип multiple_images для загрузки нескольких изображений
            'upload' => true,
            'disk' => 'public',
        ]);
    
        // Исключите поля _token, _method и _ajax из запроса
        $bookData = $this->crud->getRequest()->except(['_token', '_method', '_ajax']);
    
        // Проверьте, было ли загружено главное изображение
        if ($this->crud->getRequest()->hasFile('main_image')) {
            // Сохраните главное изображение в папке "main_images" на диске "public"
            $mainImage = $this->crud->getRequest()->file('main_image')->store('main_images', 'public');
    
            // Добавьте путь к главному изображению в данные объекта
            $bookData['main_image'] = $mainImage;
        }
    
        // Проверьте, были ли загружены дополнительные изображения
        if ($this->crud->getRequest()->hasFile('images')) {
            $imagePaths = [];
    
            foreach ($this->crud->getRequest()->file('images') as $image) {
                // Сохраните каждое дополнительное изображение в папке "images" на диске "public"
                $path = $image->store('images', 'public');
                $imagePaths[] = $path;
            }
    
            // Преобразуйте массив путей к изображениям в JSON-строку
            $bookData['images'] = json_encode($imagePaths);
        }
    
        // Создайте новый объект Book и сохраните его в базе данных
        $book = new Book($bookData);
        $book->save();
    
        return redirect($this->crud->route);
    }
}
