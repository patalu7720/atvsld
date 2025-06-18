<template>
    <template class="flex flex-col gap-4">
        <div class="flex flex-col md:flex-row gap-4 justify-between">
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2">
                    <RadioButton v-model="plant" inputId="ingredient1" name="plant" value="CC" severity="contrast" />
                    <label for="ingredient1">Củ Chi</label>
                </div>
                <div class="flex items-center gap-2">
                    <RadioButton v-model="plant" inputId="ingredient2" name="plant" value="TB" severity="contrast" />
                    <label for="ingredient2">Trảng Bàng</label>
                </div>
                <div class="flex items-center gap-2">
                    <RadioButton v-model="plant" inputId="ingredient3" name="plant" value="UC" severity="contrast" />
                    <label for="ingredient3">Unitex</label>
                </div>
            </div>
            <div class="flex flex-col md:flex-row gap-4 md:items-center">
                <DatePicker v-model="selectedDate" :showIcon="true" dateFormat="dd/mm/yy"
                    placeholder="Chọn 1 ngày trong tuần" @date-select="handleWeekSelect" :manualInput="false"
                    size="small" />
                <div v-if="selectedWeekRange">
                    <span class="font-semibold">Tuần được chọn : </span>
                    <span class="text-gray-800 italic">
                        {{ selectedWeekRange.start.format('DD/MM/YYYY') }} - {{
                            selectedWeekRange.end.format('DD/MM/YYYY')
                        }}
                    </span>
                </div>
            </div>
        </div>
        <EmptyStateCard v-if="!loadingContent && filteredAtvslds.length === 0" />
        <Fieldset v-for="group in groupedAtvslds" :key="group.area.area_id"
            :legend="group.area.name + ' (' + group.area.area_id + ')'">
            <div class="grid gap-6">
                <div v-for="(atvsld, idx) in group.list" :key="atvsld.id"
                    class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 relative hover:shadow-md transition">
                    <!-- Số thứ tự -->
                    <div v-if="group.list.length > 1"
                        class="absolute top-0 left-0 w-6 h-6 text-sm flex items-center justify-center bg-blue-500 text-white font-bold rounded-br-lg">
                        {{ idx + 1 }}
                    </div>

                    <div class="grid md:grid-cols-3 gap-4 items-start">
                        <!-- Thông tin -->
                        <div class="md:col-span-2 space-y-2">
                            <p><span class="font-semibold text-gray-700"><i class="pi pi-wrench mr-1 text-red-400"></i>
                                    Vấn đề: </span> {{
                                        atvsld.problem_detected }}</p>
                            <p>
                                <span class="font-semibold text-gray-700"><i
                                        class="pi pi-exclamation-triangle mr-1 text-yellow-400"></i> Nguy cơ:
                                </span>
                                {{ atvsld.risk }}
                            </p>
                            <p>
                                <span class="font-semibold text-gray-700"><i class="pi pi-clock mr-1 text-gray-500"></i>
                                    Thời gian phát hiện: </span>
                                {{ dayjs(atvsld.created_at).format('DD-MM-YYYY HH:mm:ss') }}
                            </p>
                            <p>
                                <span class="font-semibold text-gray-700"><i
                                        class="pi pi-lightbulb mr-1 text-green-500"></i> Giải pháp: </span>
                                {{ atvsld.solution || '(Chưa có)' }}
                            </p>
                            <p>
                                <span class="font-semibold text-gray-700"><i
                                        class="pi pi-calendar mr-1 text-gray-500"></i> Thời gian xử lý: </span>
                                {{
                                    (atvsld.start_date && atvsld.end_date)
                                        ? 'Từ ' + dayjs(atvsld.start_date).format('DD-MM-YYYY') + ' đến ' +
                                        dayjs(atvsld.end_date).format('DD-MM-YYYY')
                                        : '(Chưa cập nhật)'
                                }}
                            </p>
                        </div>

                        <!-- Hình ảnh -->
                        <div class="flex gap-2 flex-wrap justify-center">
                            <img v-for="img in atvsld.images" :key="img.id"
                                :src="img.image_path.startsWith('http') ? img.image_path : BASE_URL + img.image_path"
                                alt="Ảnh"
                                class="w-28 h-20 object-cover rounded border hover:scale-105 transition cursor-pointer"
                                @click="showImage(img)" />
                        </div>
                    </div>
                </div>
            </div>
        </Fieldset>
    </template>
    <!-- Dialog phóng to ảnh -->
    <Dialog v-model:visible="showImageDialog" :modal="true" header="Xem ảnh" class="w-auto">
        <img v-if="selectedImage" :src="selectedImage" class="max-w-[95vw] max-h-[90vh] object-contain mx-auto"
            style="display: block;" />
    </Dialog>
    <LoadingOverlay :visible="loadingContent" />
</template>
<script setup>

import dayjs from 'dayjs'
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import Dialog from "primevue/dialog";
import Fieldset from 'primevue/fieldset';
import RadioButton from 'primevue/radiobutton';
import LoadingOverlay from '../../components/LoadingOverlay.vue'
import DatePicker from 'primevue/datepicker'
import isoWeek from 'dayjs/plugin/isoWeek'
dayjs.extend(isoWeek)

import EmptyStateCard from '../../components/EmptyStateCard.vue'

const emit = defineEmits('breadcrumb');

const atvslds = ref([]);
const plant = ref('CC');
const loadingContent = ref(false);
const selectedDate = ref(null);
const selectedWeekRange = ref(null);

function handleWeekSelect(date) {
    const startOfWeek = dayjs(date).startOf('isoWeek')
    const endOfWeek = dayjs(date).endOf('isoWeek')

    selectedWeekRange.value = {
        start: startOfWeek,
        end: endOfWeek,
    }
    fetchAtvslds();
}

const BASE_URL = 'http://127.0.0.1:8000/storage/';

const token = localStorage.getItem('token');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

onMounted(() => {
    selectedDate.value = new Date(); // Chọn ngày hôm nay mặc định
    handleWeekSelect(selectedDate.value); // Cập nhật luôn tuần được chọn
    emit('breadcrumb', 'Báo cáo');
    
});

const filteredAtvslds = computed(() => {
    return atvslds.value.filter(atvsld => atvsld.areas.plant === plant.value);
});

const groupedAtvslds = computed(() => {
    const groups = {};
    for (const item of filteredAtvslds.value) {
        const key = item.areas.area_id;
        if (!groups[key]) {
            groups[key] = {
                area: item.areas,
                list: []
            };
        }
        groups[key].list.push(item);
    }
    return Object.values(groups);
});

const showImageDialog = ref(false);
const selectedImage = ref(null);

function showImage(img) {
    selectedImage.value = img.image_path.startsWith('http')
        ? img.image_path
        : BASE_URL + img.image_path;
    showImageDialog.value = true;
}

const fetchAtvslds = async () => {
    try {
        loadingContent.value = true;
        // Lấy ngày bắt đầu và kết thúc tuần đã chọn
        const start = selectedWeekRange.value?.start.format('YYYY-MM-DD');
        const end = selectedWeekRange.value?.end.format('YYYY-MM-DD');

        const res = await axios.get("http://127.0.0.1:8000/api/atvsld/reports", {
            params: { start, end }
        });
        atvslds.value = res.data;
    } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
    } finally {
        loadingContent.value = false;
    }
}
</script>
