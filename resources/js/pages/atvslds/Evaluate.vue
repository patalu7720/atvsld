<template>
    <div class="flex flex-col gap-1 relative">
        <Button label="Thêm mới" icon="pi pi-plus-circle" size="small" @click="showAddDialog = true" />
        <div class="flex flex-wrap gap-4 mt-4">
            <div class="flex flex-row items-center gap-2" v-for="p in plantsOfUsers" :key="p.plant">
                <RadioButton v-model="plant" :inputId="p.plant" name="plant" :value="p.plant" severity="contrast" />
                <label :for="p.plant">{{ p.plant_description }}</label>
            </div>
        </div>
        <EmptyStateCard v-if="!loadingContent && filteredAtvslds.length === 0" />
        <Fieldset v-for="atvsld in filteredAtvslds" :key="atvsld.id">
            <template #legend>
                <div class="flex items-center justify-between w-full">
                    <span class="font-semibold">
                        {{ atvsld.areas.name + ' (' + atvsld.areas.area_id + ')' }}
                    </span>
                    <span class="flex gap-2 ml-3">
                        <Button icon="pi pi-pencil" size="small" severity="info" variant="text" raised rounded
                            aria-label="Star" @click="showUpdate(atvsld)" />
                        <Button icon="pi pi-trash" size="small" severity="danger" variant="text" raised rounded
                            aria-label="Star" @click="showDelete(atvsld.id)" />
                    </span>
                </div>
            </template>
            <div class="grid md:grid-cols-3 gap-4 items-start">
                <!-- Thông tin -->
                <div class="md:col-span-2 space-y-2">
                    <p><span class="font-semibold text-gray-700"><i class="pi pi-wrench mr-1 text-red-400"></i> Vấn
                            đề: </span> {{
                                atvsld.problem_detected }}</p>
                    <p>
                        <span class="font-semibold text-gray-700"><i
                                class="pi pi-exclamation-triangle mr-1 text-yellow-400"></i>
                            Nguy cơ:
                        </span>
                        {{ atvsld.risk }}
                    </p>
                    <p>
                        <span class="font-semibold text-gray-700"><i class="pi pi-clock mr-1 text-gray-500"></i>
                            Thời gian phát
                            hiện: </span>
                        {{ dayjs(atvsld.created_at).format('DD-MM-YYYY HH:mm:ss') }}
                    </p>
                    <p>
                        <span class="font-semibold text-gray-700"><i class="pi pi-lightbulb mr-1 text-green-500"></i>
                            Giải pháp:
                        </span>
                        {{ atvsld.solution || '(Chưa có)' }}
                    </p>
                    <p>
                        <span class="font-semibold text-gray-700"><i class="pi pi-calendar mr-1 text-gray-500"></i>
                            Thời gian xử lý:
                        </span>
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
                        :src="img.image_path.startsWith('http') ? img.image_path : BASE_URL + img.image_path" alt="Ảnh"
                        class="w-28 h-20 object-cover rounded border hover:scale-105 transition cursor-pointer"
                        @click="showImage(img)" />
                </div>
            </div>
        </Fieldset>
        <!-- Dialog phóng to ảnh -->
        <Dialog v-model:visible="showImageDialog" :modal="true" header="Xem ảnh" class="w-auto">
            <img v-if="selectedImage" :src="selectedImage" class="max-w-[95vw] max-h-[90vh] object-contain mx-auto"
                style="display: block;" />
        </Dialog>
    </div>
    <Add v-if="showAddDialog" :showAddDialog="showAddDialog" :plantsOfUsers="plantsOfUsers" :areas="areas" :emails="emails"
        @close="closeAddDialog()" @add-success="addSuccess()" />

    <Delete v-if="showDeleteDialog" :showDeleteDialog="showDeleteDialog" :idDelete="idDelete"
        @close="closeDeleteDialog()" @delete-success="deleteSuccess()" />

    <Update v-if="showUpdateDialog" :showUpdateDialog="showUpdateDialog" :itemUpdate="itemUpdate"
        :plantsOfUsers="plantsOfUsers" :areas="areas" @close="closeUpdateDialog()" @update-success="updateSuccess()" />
    <LoadingOverlay :visible="loadingContent" />
</template>
<script setup lang="ts">

import dayjs from 'dayjs'
import { ref, onMounted, computed } from "vue";
import axios from "axios";
import Dialog from "primevue/dialog";
import Fieldset from 'primevue/fieldset';
import Button from 'primevue/button';
import RadioButton from 'primevue/radiobutton';
import { useToast } from 'primevue/usetoast';

import Add from '../../components/atvslds/Add.vue';
import Delete from '../../components/atvslds/Delete.vue';
import Update from '../../components/atvslds/Update.vue';
import LoadingOverlay from '../../components/LoadingOverlay.vue'
import EmptyStateCard from '../../components/EmptyStateCard.vue'

const token = localStorage.getItem('token');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
const user = JSON.parse(localStorage.getItem('user') || '{}');

const emit = defineEmits('breadcrumb');
const plantsOfUsers = ref([]);
const plant = ref(null);
const atvslds = ref([]);
const emails = ref([]);
const selectedImage = ref(null);
const BASE_URL = 'http://127.0.0.1:8000/storage/';
const toast = useToast();
const idDelete = ref(null);
const itemUpdate = ref(null);
const loadingContent = ref(false);
const areas = ref([]);

const showImageDialog = ref(false);
const showAddDialog = ref(false);
const showDeleteDialog = ref(false);
const showUpdateDialog = ref(false);

onMounted(() => {
    fetchAtvslds();
    emit('breadcrumb', 'Đánh giá');
});

const filteredAtvslds = computed(() => {
    return atvslds.value.filter(atvsld => atvsld.areas.plant === plant.value);
});

const fetchAtvslds = async () => {
    try {
        loadingContent.value = true;
        const resEvaluate = await axios.get("http://127.0.0.1:8000/api/atvsld/evaluate", {
            params: { user_id: user.id }
        });
        atvslds.value = resEvaluate.data.atvslds;
        plantsOfUsers.value = resEvaluate.data.plantsOfUsers;
        plant.value = plantsOfUsers.value[0]?.plant || null;
        //areaIDNewest.value = atvslds.value[0]?.areas.id || null;
        areas.value = resEvaluate.data.areas;

        const resEmail = await axios.get("http://127.0.0.1:8000/api/atvsld/get-emails");
        emails.value = resEmail.data;

    } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
    } finally {
        loadingContent.value = false;
    }
}

function showImage(img) {
    selectedImage.value = img.image_path.startsWith('http')
        ? img.image_path
        : BASE_URL + img.image_path;
    showImageDialog.value = true;
}

// Tính năng Thêm
function closeAddDialog() {
    showAddDialog.value = false;
}

const addSuccess = () => {
    toast.add({
        severity: "success",
        summary: "Thành công",
        detail: "Thêm thành công",
        life: 3000,
    });
    fetchAtvslds()
    showAddDialog.value = false;
};

// Tính năng Xóa
function showDelete(id) {

    idDelete.value = id;
    showDeleteDialog.value = true;
}

function closeDeleteDialog() {
    showDeleteDialog.value = false;
}

const deleteSuccess = () => {
    toast.add({
        severity: "success",
        summary: "Thành công",
        detail: "Xóa thành công",
        life: 3000,
    });
    atvslds.value = atvslds.value.filter(item => item.id !== idDelete.value);
    showDeleteDialog.value = false;
}

// Tính năng Cập nhật
function showUpdate(item) {

    itemUpdate.value = item;
    showUpdateDialog.value = true;
}

function closeUpdateDialog() {
    showUpdateDialog.value = false;
}

const updateSuccess = () => {
    toast.add({
        severity: "success",
        summary: "Thành công",
        detail: "Cập nhật thành công",
        life: 3000,
    });
    fetchAtvslds()
    showUpdateDialog.value = false;
}

</script>
