<script setup>

const props = defineProps({
    links: Array,
    updateData: Function
});

const loadPage = async (link) => {
    const scrollToTop = () => {
        const scrollDuration = 800; // Длительность анимации в миллисекундах
        const scrollStep = -window.scrollY / (scrollDuration / 15);
        
        const scrollInterval = setInterval(() => {
            if (window.scrollY !== 0) {
                window.scrollBy(0, scrollStep);
            } else {
                clearInterval(scrollInterval);
            }
        }, 15);
    };
    scrollToTop();

    
    const filters = {
            'title': localStorage.getItem('selectedTitle'),
            'country': JSON.parse(localStorage.getItem('selectedCountry')),
            'city': JSON.parse(localStorage.getItem('selectedCity')),
            'type': JSON.parse(localStorage.getItem('selectedTypes')),
            'facilities': JSON.parse(localStorage.getItem('selectedFacilities')),
            'price': JSON.parse(localStorage.getItem('selectedPrice')),
            'sort': JSON.parse(localStorage.getItem('selectedSort'))
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
