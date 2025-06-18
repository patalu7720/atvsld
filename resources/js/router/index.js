import { createRouter, createWebHistory } from "vue-router";
import Layout from "../components/AppLayout.vue";
import Login from "../pages/auth/Login.vue";
import Evaluate from "../pages/atvslds/Evaluate.vue";
import Reports from "../pages/atvslds/Reports.vue";
import Solution from "../pages/atvslds/Solution.vue";
import ChangePassword from "../pages/auth/ChangePassword.vue";

const routes = [
    {
        path: "/login",
        name: "Login",
        component: Login,
    },
    {
        path: "/",
        component: Layout,
        children: [
            {
                path: "/atvsld/evaluate",
                name: "Đánh giá",
                component: Evaluate,
            },
            {
                path: "/atvsld/reports",
                name: "Báo cáo",
                component: Reports,
            },
            {
                path: "/atvsld/solution",
                name: "Khắc phục",
                component: Solution,
            },
            {
                path: "/atvsld/change-password",
                name: "Thay đổi mật khâu",
                component: ChangePassword,
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem("token");
    if (to.path !== "/login" && !token) {
        next("/login");
    } else {
        next();
    }
});

export default router;
