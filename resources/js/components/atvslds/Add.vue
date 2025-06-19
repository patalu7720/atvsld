<template>
    <Dialog v-model:visible="props.showAddDialog" modal header="Thêm mới" :closable="false"
        class="w-[90vw] md:w-[60vw] xl:w-1/3">
        <Form v-slot="$form" :initialValues :resolver @submit="onFormSubmit" class="flex flex-col gap-4 w-full">
            <div class="flex flex-wrap gap-4">
                <div class="flex items-center gap-2" v-for="p in props.plantsOfUsers" :key="p.plant">
                    <RadioButton v-model="initialValues.plant" :inputId="p.plant + 'add'" name="plant" :value="p.plant"
                        severity="contrast" />
                    <label :for="p.plant + 'add'">{{ p.plant_description }}</label>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <label for="area">Khu vực</label>
                <Select v-model="initialValues.area" :options="filteredAreas" name="area" optionLabel="name"
                    optionValue="id" placeholder="Chọn khu vực" class="w-full" />
                <Message v-if="$form.area?.invalid" severity="error" variant="simple">
                    {{ $form.area.error?.message }}
                </Message>
            </div>
            <div class="card flex flex-col gap-2">
                <label for="area">Hình ảnh</label>
                <!-- Khu vực Drag & Drop -->
                <div @dragover.prevent @drop.prevent="handleDrop"
                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-6 text-gray-400 hover:border-blue-400 transition">
                    <p class="mb-2">Kéo & thả hình ảnh vào đây</p>
                    <p class="text-sm mb-4">hoặc</p>
                    <button type="button" @click="triggerFileInput"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Chọn ảnh
                    </button>
                    <input ref="fileInput" type="file" multiple accept="image/*" @change="onFileSelect"
                        class="hidden" />
                </div>

                <!-- Preview hình -->
                <div v-if="initialValues.images.length" class="mt-4 grid grid-cols-3 gap-3">
                    <div v-for="(img, index) in initialValues.images" :key="index"
                        class="relative group overflow-hidden rounded-xl border border-2 border-gray-400 shadow-sm">
                        <img :src="img.url" class="object-cover w-full h-35" />
                        <button type="button"
                            class="absolute top-1 right-1 cursor-pointer bg-black bg-opacity-50 text-white text-xs rounded-sm p-1 hover:bg-gray-800"
                            @click="removeImage(index)">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
            <div class="flex flex-col gap-2">
                <label for="problem_detected">Vấn đề phát hiện</label>
                <InputText type="text" name="problem_detected" v-model="initialValues.problem_detected"
                    placeholder="Vấn đề phát hiện" />
                <Message v-if="$form.problem_detected?.invalid" severity="error" variant="simple">
                    {{ $form.problem_detected.error?.message }}
                </Message>
            </div>
            <div class="flex flex-col gap-2">
                <label for="risk">Nguy cơ</label>
                <InputText type="text" name="risk" v-model="initialValues.risk" placeholder="Nguy cơ" />
                <Message v-if="$form.risk?.invalid" severity="error" variant="simple">
                    {{ $form.risk.error?.message }}
                </Message>
            </div>
            <div class="flex flex-col gap-2">
                <Fieldset legend="Bộ phận bị đánh giá">
                    <Select v-model="initialValues.solution_user" name="solution_user" :options="props.emails"
                        optionLabel="name" optionVale="email" filter placeholder="Chọn bộ phận bị đánh giá"
                        class="w-full" />
                    <Message v-if="$form.solution_user?.invalid" severity="error" variant="simple" class="mt-2">
                        {{ $form.solution_user.error?.message }}
                    </Message>
                </Fieldset>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <Button label="Đóng" class="p-button-secondary" @click="$emit('close')" />
                <Button type="submit" label="Lưu" :loading="loadingSubmit" />
            </div>
        </Form>
    </Dialog>
</template>
<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import RadioButton from 'primevue/radiobutton';
import Select from 'primevue/select';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import { Form } from '@primevue/forms';
import axios from 'axios';
import Message from 'primevue/message';
import Dialog from 'primevue/dialog';
import Fieldset from 'primevue/fieldset';

const token = localStorage.getItem('token');
const user = JSON.parse(localStorage.getItem('user') || '{}');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

const emit = defineEmits('addSuccess');

const fileInput = ref(null)
const loadingSubmit = ref(false);
let areaName = null;

const props = defineProps({
    showAddDialog: {
        type: Boolean,
        required: true
    },
    areas: {
        type: Array,
        required: true
    },
    emails: {
        type: Array,
        required: true
    },
    plantsOfUsers: {
        type: Array,
        required: true
    }
});

const initialValues = ref({
    plant: '',
    area: '',
    images: [],
    problem_detected: '',
    risk: '',
    solution_user: '',
});

onMounted(() => {
    initialValues.value.plant = props.plantsOfUsers[0]?.plant || '';
    initialValues.value.area = props.areas[0].id;
    areaName = props.areas[0].name;
});

const filteredAreas = computed(() => {
    if (!props.areas) return [];
    return props.areas.filter(area => area.plant === initialValues.value.plant);
});

watch(filteredAreas, (newAreas) => {
    if (newAreas.length > 0) {
        initialValues.value.area = newAreas[0].id;
    } else {
        initialValues.value.area = '';
    }
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.area) {
        errors.area = [{ message: 'Khu vực chưa được chọn' }];
    }

    if (!values.problem_detected.trim()) {
        errors.problem_detected = [{ message: 'Chưa nhập vấn đề' }];
    }

    if (!values.risk.trim()) {
        errors.risk = [{ message: 'Chưa nhập nguy cơ' }];
    }

    if (!values.solution_user) {
        errors.solution_user = [{ message: 'Chưa nhập bộ phận bị đáng giá' }];
    }

    return {
        errors
    };
};

const onFormSubmit = async ({ valid }) => {
    if (valid) {
        loadingSubmit.value = true;
        try {
            const formData = new FormData();
            formData.append('user_id', user.id);
            formData.append('area_id', initialValues.value.area);
            formData.append('problem_detected', initialValues.value.problem_detected);
            formData.append('risk', initialValues.value.risk);
            formData.append('solution_user', initialValues.value.solution_user.email);

            if (initialValues.value.images && initialValues.value.images.length) {
                for (let i = 0; i < initialValues.value.images.length; i++) {
                    formData.append('images[]', initialValues.value.images[i].file);
                }
            }

            const res = await axios.post(
                'http://127.0.0.1:8000/api/atvsld/store',
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } }
            );

            initialValues.value = {
                plant: props.plantsOfUsers[0]?.plant || '',
                area: '',
                images: [],
                problem_detected: '',
                risk: '',
                solution_user: '',
            };

            emit('addSuccess');

        } catch (error) {
            if (error.response?.status === 422)
                errors.value = error.response.data.errors;
        } finally {
            loadingSubmit.value = false;
        }
    }
};

// Tải lên hình ảnh
function triggerFileInput() {
    fileInput.value.click()
}

function onFileSelect(event) {
    const files = event.target.files
    previewFiles(files)
}

function handleDrop(event) {
    const files = event.dataTransfer.files
    previewFiles(files)
}

function previewFiles(files) {
    for (const file of files) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader()
            reader.onload = (e) => {
                initialValues.value.images.push({ file, url: e.target.result })
            }
            reader.readAsDataURL(file)
        }
    }
}

function removeImage(index) {
    initialValues.value.images.splice(index, 1)
}
</script>

<style scoped>
img {
    transition: transform 0.2s;
}

img:hover {
    transform: scale(1.05);
}
</style>