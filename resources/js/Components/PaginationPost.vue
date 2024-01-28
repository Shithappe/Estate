<script setup>

const props = defineProps({
    links: Array,
    updateData: Function
});

const loadPage = async (link) => {
    const filters = {
            'title': null,
            'city': JSON.parse(localStorage.getItem('selectedCity')),
            'type': JSON.parse(localStorage.getItem('selectedTypes')),
            'facilities': JSON.parse(localStorage.getItem('selectedFacilities')),
            'price': JSON.parse(localStorage.getItem('selectedPrice'))
    }

    try {
        const response = await axios.post(link, filters);
        props.updateData(response.data);
    } catch (error) {
        console.error(error);
    }
};
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap justify-center -mb-1">
            <template v-for="(link, p) in links" :key="p">
                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 border rounded"
                    v-html="link.label" />
                <div v-else
                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border rounded hover:cursor-pointer focus:border-indigo-500 focus:text-indigo-500"
                    :class="{ 'bg-blue-700 text-white': link.active }" @click="() => loadPage(link.url)"
                    v-html="link.label" />
            </template>
        </div>
    </div>
</template>
