import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "packages/Mindigo/Core/src/resources/css/home.css",
                "packages/Mindigo/Auth/src/resources/css/app.css",
                "packages/Mindigo/Auth/src/resources/js/app.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});