<script setup>
import axios from 'axios';
import { ref, onMounted } from 'vue';
import SimpleAppLayout from '@/Layouts/SimpleAppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import CardItem from '@/Components/CardItem.vue';
import Modal from '@/Components/Modal.vue';
import Lucide from '@/Components/Lucide.vue';


const data = ref({ data: {}, links: [] });

const modalData = ref({});
const modalVisible = ref(false);
const modalEdit = ref(false);

function openEdit() {
    modalEdit.value = true;
}
function openModal(data) {
    modalData.value = data;
    modalVisible.value = true;
}
function closeModal() {
    modalVisible.value = false;
    modalEdit.value = false;
}

const mainImagePreview = ref('');

// Состояние для предпросмотра нескольких изображений
const imagePreviews = ref([]);

// Обработчик изменения главного изображения
const handleMainImageChange = (event) => {
    const file = event.target.files[0];
    mainImagePreview.value = URL.createObjectURL(file);
};

// Обработчик изменения нескольких изображений
const handleImagesChange = (event) => {
    const files = event.target.files;
    imagePreviews.value = [];
    for (let i = 0; i < files.length; i++) {
        imagePreviews.value.push(URL.createObjectURL(files[i]));
    }
};

// Отправка данных формы на сервер
const submitForm = async () => {
    const formData = {
        'title': modalData.value.title,
        'description': modalData.value.description,
        'builder_name': modalData.value.builder_name,
        'complex_name': modalData.value.complex_name,
        'square': modalData.value.square,
        'price_per_meter': modalData.value.price_per_meter,
        'room_count': modalData.value.room_count,
        'floor': modalData.value.floor,
        'price': modalData.value.price,
        'city': modalData.value.city,
        'district': modalData.value.district,
        'street': modalData.value.street,
        'coordinate': modalData.value.coordinate,
        'rate': modalData.value.rate,
        'property_type': modalData.value.property_type,
        'bedrooms_count': modalData.value.bedrooms_count,
        'source_url': modalData.value.source_url,
    }


    formData['main_image'] = document.querySelector('input[name="main_image"]').files[0];
    formData['images'] = [];

    const imagesFiles = document.querySelector('input[name="images"]').files;
    for (let i = 0; i < imagesFiles.length; i++) {
        formData['images'].push(imagesFiles[i]);
    }

    try {
        const response = await axios.post("/api/book" + (modalData.value.id ? '/' + modalData.value.id : ''), formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        console.log(response.data);
        mainImagePreview.value = '';
        imagePreviews.value = [];
        if (!modalData.value.id) getData();
    } catch (error) {
        console.error(error);
    }
};

const deleteBook = async (id) => {
    try {
        await axios.delete('/api/book/' + id);
        getData();
        closeModal();
    } catch (error) {
        console.error(error);
    }
}

const getData = async () => {
    try {
        const response = await axios.get('/api/getEstateAdmin');
        data.value = response.data;
    } catch (error) {
        console.error(error);
    }
}
onMounted(getData);

</script>

<template>
    <SimpleAppLayout title="Admin Panel">

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <Modal :show="modalEdit" maxWidth="2lg" @close="closeModal">
                    <form @submit.prevent="submitForm"
                        class="p-4 bg-slate-100 rounded-lg flex flex-col justify-center gap-12">
                        <div class="flex flex-col">
                            <label for="title">Title</label>
                            <input type="text" id="title" v-model="modalData.title" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="description">Description</label>
                            <textarea id="description" v-model="modalData.description" required></textarea>
                        </div>

                        <div class="flex flex-col">
                            <label for="builder_name">Builder Name</label>
                            <input type="text" id="builder_name" v-model="modalData.builder_name" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="complex_name">Complex Name</label>
                            <input type="text" id="complex_name" v-model="modalData.complex_name" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="square">Square</label>
                            <input type="number" id="square" v-model="modalData.square" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="price_per_meter">Price per Meter</label>
                            <input type="number" id="price_per_meter" v-model="modalData.price_per_meter" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="room_count">Room Count</label>
                            <input type="number" id="room_count" v-model="modalData.room_count" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="floor">Floor</label>
                            <input type="number" id="floor" v-model="modalData.floor" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="price">Price</label>
                            <input type="number" id="price" v-model.number="modalData.price" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="main_image">Main Image</label>
                            <input type="file" name="main_image" @change="handleMainImageChange" required>
                            <img v-if="mainImagePreview" :src="mainImagePreview" alt="Main Image Preview"
                                class="w-32 h-32 mt-2">
                        </div>

                        <div class="flex flex-col">
                            <label for="images">Images</label>
                            <input type="file" name="images" multiple @change="handleImagesChange">
                            <div v-if="imagePreviews.length" class="mt-2 flex gap-x-2">
                                <div v-for="(preview, index) in imagePreviews" :key="index">
                                    <img :src="preview" alt="Image Preview" class="w-32 h-32">
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col">
                            <label for="city">City</label>
                            <input type="text" id="city" v-model.number="modalData.city" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="district">District</label>
                            <input type="text" id="district" v-model.number="modalData.district">
                        </div>

                        <div class="flex flex-col">
                            <label for="street">Street</label>
                            <input type="text" id="street" v-model.number="modalData.street" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="coordinate">Coordinate</label>
                            <input type="text" id="coordinate" v-model.number="modalData.coordinate" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="rate">Rate</label>
                            <input type="number" id="rate" v-model.number="modalData.rate" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="property_type">Property Type</label>
                            <input type="text" id="property_type" v-model.number="modalData.property_type" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="bedrooms_count">Bedrooms Count</label>
                            <input type="number" id="bedrooms_count" v-model.number="modalData.bedrooms_count" required>
                        </div>

                        <div class="flex flex-col">
                            <label for="source_url">Source Url</label>
                            <input type="text" id="source_url" v-model.number="modalData.source_url" required>
                        </div>

                        <button type="submit"
                            class="w-min mx-auto px-4 py-2 my-1 text-md font-medium text-slate-100 bg-slate-900 rounded-lg">Submit</button>
                    </form>
                </Modal>

                <Modal :show="modalVisible" maxWidth="2lg" @close="closeModal">
                    <CardItem :item="modalData" />
                    <div class="mx-auto mt-1 p-4 flex justify-center gap-12">
                        <div @click="() => { closeModal(); openEdit(); }"
                            class="bg-slate-200 border border-gray-300 shadow p-1 rounded-lg cursor-pointer">
                            <Lucide class="w-6 h-6" icon="Pencil" />
                        </div>
                        <div @click="deleteBook(modalData.id)" class="bg-slate-200 border border-gray-300 shadow p-1 rounded-lg cursor-pointer">
                            <Lucide class="w-6 h-6" icon="Trash2" />
                        </div>
                    </div>
                </Modal>

                <button @click="() => { modalData.value = {}; openEdit();}" class="p-2 my-1 float-left text-md font-medium text-slate-100 bg-slate-900 rounded-lg">+
                    New</button>

                <table class="w-full border-collapse border border-gray-300 bg-white">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="border border-gray-300 px-4 py-2">ID</th>
                            <th class="border border-gray-300 px-4 py-2">Title</th>
                            <th class="border border-gray-300 px-4 py-2">Builder</th>
                            <th class="border border-gray-300 px-4 py-2">Complex</th>
                            <th class="border border-gray-300 px-4 py-2">Count Room</th>
                            <th class="border border-gray-300 px-4 py-2">Floor</th>
                            <th class="border border-gray-300 px-4 py-2">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="book in data?.data" :key="book.id" class="hover:bg-slate-200"
                            @click="() => { openModal(book) }">
                            <td class="border border-gray-300 px-4 py-2 text-center">{{ book.id }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ book.title }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ book.builder_name }}</td>
                            <td class="border border-gray-300 px-4 py-2">{{ book.complex_name }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">{{ book.room_count }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">{{ book.floor }}</td>
                            <td class="border border-gray-300 px-4 py-2 text-center">{{ book.price }}$</td>
                        </tr>
                    </tbody>
                </table>


                <Pagination v-show="data.links" class="mt-6" :links="data?.links" />
            </div>
        </div>
    </SimpleAppLayout>
</template>
