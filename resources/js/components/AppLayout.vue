<template>
    <div class="flex flex-row min-h-screen">
        <!-- Overlay for mobile -->
        <div v-if="toggle && windowWidth < 768" class="fixed inset-0 z-30" style="background: rgba(0,0,0,0.2);"
            @click="toggle = false"></div>
        <!-- Sidebar -->
        <div v-show="toggle" :class="[
            'p-2 z-40 transition-all duration-300 min-h-screen border-r border-gray-200',
            windowWidth < 768
                ? 'fixed top-0 left-0 min-w-70 bg-white shadow-lg'
                : 'min-w-70 static'
        ]">
            <div class="flex flex-col gap-2">
                <div v-for="(item, idx) in items" :key="idx">
                    <div class="flex items-center px-4 py-2 font-semibold cursor-pointer hover:bg-gray-200 hover:text-gray-800 hover:rounded"
                        @click="toggleSub(idx)">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                        <span v-if="item.items" class="pi pi-angle-down ml-auto" />
                    </div>
                    <div v-if="item.items && openSub === idx" class="ml-6">
                        <router-link v-for="(sub, subIdx) in item.items" :key="subIdx" :to="sub.route"
                            class="flex items-center px-3 py-1 rounded hover:bg-gray-200" :class="{
                                'bg-gray-200 text-gray-900 font-semibold': route.path === sub.route
                            }" @click="onMenuClick">
                            <span :class="sub.icon" />
                            <span class="ml-2">{{ sub.label }}</span>
                        </router-link>
                    </div>
                </div>
                <a href="#" @click.prevent="logout"
                    class="flex items-center px-4 py-2 font-semibold cursor-pointer text-red-600 hover:bg-red-50 hover:text-red-500 hover:rounded">
                    <span class="pi pi-sign-out" />
                    <span class="ml-2">Đăng xuất</span>
                </a>
            </div>
        </div>
        <!-- Main content -->
        <div class="flex flex-col px-6 w-full pb-5">
            <Toast />
            <div class="h-16 py-1">
                <Button icon="pi pi-align-justify" variant="outlined" severity="secondary" @click="toggle = !toggle"
                    rounded />
                <label class="font-semibold text-lg pl-3 text-gray-700">{{ breadcrumb }}</label>
            </div>
            <router-view @breadcrumb="breadcrumb = $event" />
            <ScrollTop />
        </div>
    </div>
    <div v-if="loadingContent"
        class="fixed top-4 right-6 z-50 flex items-center justify-center bg-white bg-opacity-70 border border-gray-200 rounded-full shadow-lg p-2 transition-all"
        style="box-shadow: 0 4px 24px rgba(0,0,0,0.10);">
        <ProgressSpinner style="width: 36px; height: 36px" strokeWidth="6" fill="transparent" animationDuration=".7s"
            aria-label="Đang tải nội dung" />
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useRouter } from 'vue-router';
import Button from 'primevue/button';
import { Toast } from "primevue";
import { useRoute } from 'vue-router';
import ProgressSpinner from 'primevue/progressspinner'
import ScrollTop from 'primevue/scrolltop';

const user = JSON.parse(localStorage.getItem('user') || '{}');

const route = useRoute();
const router = useRouter();
const toggle = ref(window.innerWidth >= 768);
const windowWidth = ref(window.innerWidth);
const openSub = ref(null);
const breadcrumb = ref(null);
const loadingContent = ref(false)

function handleResize() {
    windowWidth.value = window.innerWidth;
    if (window.innerWidth >= 768) {
        toggle.value = true;
    } else {
        toggle.value = false;
    }
}

function toggleSub(idx) {
    openSub.value = openSub.value === idx ? null : idx;
}

function onMenuClick() {
    if (windowWidth.value < 768) toggle.value = false;
}

onMounted(() => {
    window.addEventListener('resize', handleResize);
    handleResize();
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});

const logout = () => {
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
}

const items = ref([
    {
        label: 'ATVSLD',
        icon: 'pi pi-heart',
        items: [
            {
                label: 'Đánh giá',
                icon: 'pi pi-pencil',
                route: '/atvsld/evaluate'
            },
            {
                label: 'Khắc phục',
                icon: 'pi pi-check-circle',
                route: '/atvsld/solution'
            },
            {
                label: 'Báo cáo',
                icon: 'pi pi-chart-bar',
                route: '/atvsld/reports'
            }
        ]
    },
    {
        label: user.name,
        icon: 'pi pi-user',
        items: [
            {
                label: 'Đổi mật khẩu',
                icon: 'pi pi-key',
                route: '/atvsld/change-password'
            },
        ]
    },
]);
</script>
