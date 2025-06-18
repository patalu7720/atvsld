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
                            <Button label="Khắc phục" size="small" icon="pi pi-arrow-right" iconPos="right" class="mt-2"
                                severity="success" @click="clickSoLution(atvsld.id)"></Button>
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

    <!-- Dialog khắc phục -->
    <Dialog v-model:visible="showSolutionDialog" header="Khắc phục" :modal="true" :closable="false"
        class="w-[90vw] md:w-[60vw] xl:w-1/3">
        <Form v-slot="$form" :initialValues :resolver @submit="onFormSubmit" class="flex flex-col gap-4 w-full">
            <div class="flex flex-col gap-2">
                <label for="solution">Phương hướng khắc phục</label>
                <InputText type="text" name="solution" v-model="initialValues.solution"
                    placeholder="Vấn đề phát hiện" />
                <Message v-if="$form.solution?.invalid" severity="error" variant="simple">
                    {{ $form.solution.error?.message }}
                </Message>
            </div>
            <div class="flex flex-col gap-2">
                <label for="start">Ngày bắt đầu</label>
                <DatePicker v-model="initialValues.start" name="start" :showIcon="true" dateFormat="dd/mm/yy"
                    placeholder="Chọn ngày bắt đầu" :manualInput="false" />
                <Message v-if="$form.start?.invalid" severity="error" variant="simple">
                    {{ $form.start.error?.message }}
                </Message>
            </div>
            <div class="flex flex-col gap-2">
                <label for="start">Ngày kết thúc</label>
                <DatePicker v-model="initialValues.end" name="end" :showIcon="true" dateFormat="dd/mm/yy"
                    placeholder="Chọn ngày kết thúc" :manualInput="false" />
                <Message v-if="$form.end?.invalid" severity="error" variant="simple">
                    {{ $form.end.error?.message }}
                </Message>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <Button label="Đóng" class="p-button-secondary" @click="showSolutionDialog = false" />
                <Button type="submit" label="Lưu" :loading="loadingSubmit" />
            </div>
        </Form>
    </Dialog>

    <LoadingOverlay :visible="loadingContent" />
</template>
<script setup>

import dayjs from 'dayjs'
import { ref, onMounted, computed } from "vue";
import { Form } from '@primevue/forms';
import axios from "axios";
import Dialog from "primevue/dialog";
import Fieldset from 'primevue/fieldset';
import RadioButton from 'primevue/radiobutton';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';

import LoadingOverlay from '../../components/LoadingOverlay.vue'
import EmptyStateCard from '../../components/EmptyStateCard.vue'

const emit = defineEmits('breadcrumb');

const atvslds = ref([]);
const plant = ref('CC');
const loadingContent = ref(false);
const loadingSubmit = ref(false);
const idUpdate = ref(null);

const showImageDialog = ref(false);
const selectedImage = ref(null);
const showSolutionDialog = ref(false);

function clickSoLution(id) {
    idUpdate.value = id;
    showSolutionDialog.value = true;
}

const initialValues = ref({
    solution: '',
    start: '',
    end: '',
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.solution.trim()) {
        errors.solution = [{ message: 'Chưa nhập phuong hướng khắc phục' }];
    }

    if (!values.start) {
        errors.start = [{ message: 'Chưa chọn ngày bắt đầu' }];
    }

    if (!values.end) {
        errors.end = [{ message: 'Chưa chọn ngày kết thúc' }];
    }

    return {
        errors
    };
};

const BASE_URL = 'http://127.0.0.1:8000/storage/';

const token = localStorage.getItem('token');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
const user = JSON.parse(localStorage.getItem('user') || '{}');

onMounted(() => {
    fetchAtvslds();
    emit('breadcrumb', 'Khắc phục');
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

function showImage(img) {
    selectedImage.value = img.image_path.startsWith('http')
        ? img.image_path
        : BASE_URL + img.image_path;
    showImageDialog.value = true;
}

const fetchAtvslds = async () => {
    try {
        loadingContent.value = true;

        const res = await axios.get("http://127.0.0.1:8000/api/atvsld/get-solution", {
            params: { user_email: user.email }
        });
        atvslds.value = res.data;
    } catch (error) {
        console.error("Lỗi khi lấy dữ liệu:", error);
    } finally {
        loadingContent.value = false;
    }
}

const onFormSubmit = async ({ valid }) => {
    if (valid) {
        loadingSubmit.value = true;
        try {
            await axios.post(
                'http://127.0.0.1:8000/api/atvsld/solution', {
                id: idUpdate.value,
                solution: initialValues.value.solution,
                start: dayjs(initialValues.value.start).format('YYYY-MM-DD'),
                end: dayjs(initialValues.value.end).format('YYYY-MM-DD'),
            }
            );

            initialValues.value = {
                solution: '',
                start: '',
                end: '',
            };
            showSolutionDialog.value = false;

            fetchAtvslds();

            toast.add({
                severity: "success",
                summary: "Thành công",
                detail: "Thành công",
                life: 3000,
            });
        } catch (error) {
            if (error.response?.status === 422)
                errors.value = error.response.data.errors;
        } finally {
            loadingSubmit.value = false;
        }
    }
};

</script>
