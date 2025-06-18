<template>
    <div class="p-4">
        <div class="mb-4">
            <h2 class="text-2xl font-bold mb-2">Lịch công tác theo Team</h2>
            <p class="text-gray-500">Năm: {{ currentYear }}</p>
        </div>

        <div class="overflow-auto border rounded-lg">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 border-r border-gray-200">Team / Thành viên</th>
                        <th v-for="(month, index) in months" :key="index"
                            class="px-4 py-2 border-r border-gray-200 text-center"
                            :style="{ backgroundColor: monthColors[index % monthColors.length] }">
                            {{ month }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="team in teams" :key="team.id" class="border-t border-gray-200">
                        <!-- Team name + members -->
                        <td class="px-4 py-2 align-top whitespace-nowrap">
                            <div class="font-semibold">{{ team.name }}</div>
                            <ul class="text-xs text-gray-600 mt-1 list-disc list-inside">
                                <li v-for="user in team.users" :key="user.id">{{ user.name }}</li>
                            </ul>
                        </td>

                        <!-- Khu vực theo từng tháng -->
                        <td v-for="(month, index) in months" :key="index" class="px-4 py-2 text-center"
                            :style="{ backgroundColor: monthColors[index % monthColors.length] }">
                            <span v-if="team.months && team.months[index + 1]">
                                {{ team.months[index + 1].area.name }}
                            </span>
                            <span v-else class="text-gray-300">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const months = [
    'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4',
    'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8',
    'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
];

const monthColors = [
    '#FFEDD5', '#FEE2E2', '#E0F2FE', '#D1FAE5',
    '#FDE68A', '#E9D5FF', '#BFDBFE', '#FCD34D',
    '#C7D2FE', '#FCA5A5', '#A7F3D0', '#F9A8D4'
];

const teams = ref([]);

onMounted(async () => {
    try {
        const response = await axios.get('http://127.0.0.1:8000/api/atvsld/scheduler');
        teams.value = response.data.teamData;
    } catch (error) {
        console.error('Failed to load data:', error);
    }
});
</script>

<style scoped>
table {
    border-collapse: separate;
    border-spacing: 0;
}

th,
td {
    border-right: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}

th:last-child,
td:last-child {
    border-right: none;
}
</style>
