<template>
    <div class="w-full md:w-1/2 mx-auto p-6 bg-white shadow-md rounded-lg">
        <Form v-slot="$form" :initialValues :resolver @submit="onFormSubmit" class="flex flex-col gap-4">
            <div>
                <label for="new_password" class="block mb-1 font-medium">Mật khẩu mới</label>
                <Password id="new_password" name="new_password" v-model="initialValues.new_password" toggleMask fluid />
                <Message v-if="$form.new_password?.invalid" severity="error" size="small" variant="simple">{{
                    $form.new_password.error?.message }}</Message>
            </div>

            <div>
                <label for="confirm_password" class="block mb-1 font-medium">Xác nhận mật khẩu</label>
                <Password id="confirm_password" name="confirm_password" v-model="initialValues.confirm_password"
                    toggleMask fluid />
                <Message v-if="$form.confirm_password?.invalid" severity="error" size="small" variant="simple">{{
                    $form.confirm_password.error?.message }}</Message>
            </div>

            <Button type="submit" label="Lưu thay đổi" class="mt-2" />
        </Form>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Message from 'primevue/message';
import { Form } from '@primevue/forms';
import { useToast } from 'primevue/usetoast';

const token = localStorage.getItem('token');
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
const user = JSON.parse(localStorage.getItem('user') || '{}');

const emit = defineEmits('breadcrumb');
const toast = useToast();

const initialValues = ref({
    new_password: '',
    confirm_password: ''
});

onMounted(() => {
    emit('breadcrumb', 'Thay đổi mật khẩu');
});

const resolver = (values) => {
    const errors = {};

    if (!values.values.new_password || values.values.new_password.length < 6) {
        errors.new_password = [{ message: 'Mật khẩu mới phải ít nhất 6 ký tự' }];
    }

    if (values.values.new_password !== values.values.confirm_password) {
        errors.confirm_password = [{ message: 'Xác nhận mật khẩu không khớp' }];
    }

    return {
        errors
    };
};

const onFormSubmit = async ({ valid, values }) => {
    if (valid) {
        try {
            const res = await axios.post('http://127.0.0.1:8000/api/atvsld/change-password', {
                user_id: user.id,
                new_password: initialValues.value.new_password,
            });
            console.log(res.data);
            toast.add({ severity: 'success', summary: 'Thành công', detail: 'Đã thay đổi mật khẩu', life: 3000 });

            initialValues.value.current_password = '';
            initialValues.value.new_password = '';
            initialValues.value.confirm_password = '';
        } catch (err) {
            toast.add({ severity: 'error', summary: 'Lỗi', detail: 'Không thể thay đổi mật khẩu', life: 3000 });
        }
    }
};
</script>
