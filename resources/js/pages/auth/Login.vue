<template>
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <Form v-slot="$form" :initialValues :resolver @submit="onFormSubmit" 
            class="bg-white p-8 rounded shadow-md w-96 flex flex-col gap-4">
            <h2 class="text-3xl font-semibold mb-2 text-center">Đăng nhập</h2>
            <div class="flex flex-col gap-2">
                <label for="username">Tài khoản</label>
                <InputText v-model="initialValues.username" name="username" placeholder="Username" type="text"/>
                <Message v-if="$form.username?.invalid" severity="error" size="small" variant="simple">{{
                    $form.username.error?.message }}</Message>
            </div>
            <div class="flex flex-col gap-2 mb-2">
                <label for="username">Mật khẩu</label>
                <Password v-model="initialValues.password" name="password" placeholder="Mật khẩu" fluid toggleMask/>
                <Message v-if="$form.password?.invalid" severity="error" size="small" variant="simple">{{
                    $form.password.error?.message }}</Message>
            </div>
            <Button type="submit" label="Đăng nhập" :loading="loading" />
            <Message v-if="error" severity="error" class="mt-2">{{ error }}</Message>
        </Form>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { Form } from '@primevue/forms'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Message from 'primevue/message'
import Password from 'primevue/password'
import axios from 'axios'

onMounted(() => {
    if (localStorage.getItem('token')) {
        router.push('/atvsld/reports')
    }
})

const loading = ref(false)
const error = ref('')
const router = useRouter()

const initialValues = ref({
    username: '',
    password: ''
});

const resolver = ({ values }) => {
    const errors = {};

    if (!values.username.trim()) {
        errors.username = [{ message: 'Tài khoản chưa nhập' }];
    }

    if (!values.password.trim()) {
        errors.password = [{ message: 'Mật khẩu chưa nhập' }];
    }

    return {
        errors
    };
};

const onFormSubmit = async ({ valid }) => {
    if (valid) {
        loading.value = true
        error.value = ''
        try {
            const res = await axios.post('http://127.0.0.1:8000/api/login', {
                username: initialValues.value.username,
                password: initialValues.value.password
            })
            // Giả sử API trả về { token, user }
            localStorage.setItem('token', res.data.token)
            localStorage.setItem('user', JSON.stringify(res.data.user))
            router.push('/atvsld/evaluate')
        } catch (e) {
            error.value = e.response?.data?.message || 'Đăng nhập thất bại'
        } finally {
            loading.value = false
        }
    }
}
</script>

