<template>
    <Dialog v-model:visible="props.showDeleteDialog" :style="{ width: '450px' }" header="Xác nhận" :modal="true" :closable="false">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span>Bạn có muốn xóa không?</span>
        </div>
        <template #footer>
            <Button label="Hủy" size="small" icon="pi pi-times" text @click="$emit('close')" />
            <Button label="Đồng ý" size="small" icon="pi pi-check" :loading="loading" @click="deleteItem" />
        </template>
    </Dialog>
</template>
<script setup>
import { ref } from 'vue';
import Dialog from "primevue/dialog";
import Button from 'primevue/button';

const loading = ref(false);

const token = localStorage.getItem('token');
const user = JSON.parse(localStorage.getItem('user') || '{}');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`

const emit = defineEmits('deleteSuccess');

const props = defineProps({
    showDeleteDialog: {
        type: Boolean,
        required: true
    },
    idDelete: {
        type: Number,
        required: true
    },
});

const deleteItem = async () => {
    loading.value = true;
    try {
        await axios.delete(`http://127.0.0.1:8000/api/atvsld/${props.idDelete}`);
        emit('deleteSuccess');
    } catch (error) {
        
    } finally {
        loading.value = false;
    }
};

</script>