import "./bootstrap";
import { createApp } from "vue";
import router from "./router";
import PrimeVue from "primevue/config";
import 'primeicons/primeicons.css'
import App from "./App.vue";
import Ripple from "primevue/ripple";
import Aura from "@primeuix/themes/aura";
import ToastService from "primevue/toastservice";
import { definePreset } from "@primeuix/themes";

const app = createApp(App);

const CustomPreset = definePreset(Aura, {
    semantic: {
        primary: {
            50: "#f5f5f5", // Nhạt nhất
            100: "#e0e0e0",
            200: "#c7c7c7",
            300: "#6b6b6b",
            400: "#6b6b6b",
            500: "#6b6b6b", // Màu đen xám chính
            600: "#5c5c5c",
            700: "#4d4d4d",
            800: "#3e3e3e",
            900: "#2f2f2f",
            950: "#1a1a1a", // Đậm nhất (gần đen tuyền)
        },
        colorScheme: {
            light: {
                primary: {
                    color: "{primary.950}", // Sử dụng màu đen xám chính
                    contrastColor: "#ffffff",
                    hoverColor: "{primary.900}",
                    activeColor: "{primary.900}",
                },
                highlight: {
                    background: "{primary.100}",
                    focusBackground: "{primary.300}",
                    color: "{primary.700}",
                    focusColor: "{primary.800}",
                },
            },
            dark: {
                primary: {
                    color: "{primary.400}",
                    contrastColor: "{surface.900}",
                    hoverColor: "{primary.300}",
                    activeColor: "{primary.200}",
                },
                highlight: {
                    background:
                        "color-mix(in srgb, {primary.400}, transparent 84%)",
                    focusBackground:
                        "color-mix(in srgb, {primary.400}, transparent 76%)",
                    color: "rgba(255,255,255,.87)",
                    focusColor: "rgba(255,255,255,.87)",
                },
            },
        },
    },
});

app.use(router);
app.use(PrimeVue, {
    theme: {
        preset: CustomPreset,
        options: {
            darkModeSelector: "system", // Hoặc '.app-dark' nếu bạn muốn tùy chỉnh dark mode
        },
    },
});

app.use(ToastService);
app.directive("ripple", Ripple);
app.mount("#app");
