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
                "packages/Mindigo/Dashboard/src/resources/css/app.css",
                "packages/Mindigo/Dashboard/src/resources/js/app.js",
                "packages/Mindigo/ExamManagement/src/resources/css/app.css",
                "packages/Mindigo/ExamManagement/src/resources/js/app.js",
                "packages/Mindigo/Profile/src/resources/css/app.css", 
                "packages/Mindigo/Profile/src/resources/js/app.js",
                "packages/Mindigo/QuestionBank/src/resources/css/app.css",
                "packages/Mindigo/QuestionBank/src/resources/js/app.js",
                "packages/Mindigo/RolePermission/src/resources/css/app.css",
                "packages/Mindigo/RolePermission/src/resources/js/app.js",
                "packages/Mindigo/SubjectManagement/src/resources/css/app.css",
                "packages/Mindigo/SubjectManagement/src/resources/js/app.js",
                "packages/Mindigo/SupportManagement/src/resources/css/app.css",
                "packages/Mindigo/SupportManagement/src/resources/js/app.js",
                "packages/Mindigo/UserManagement/src/resources/css/app.css",
                "packages/Mindigo/UserManagement/src/resources/js/app.js",
                "packages/Mindigo/SystemSetting/src/resources/css/app.css",
                "packages/Mindigo/SystemSetting/src/resources/js/app.js",
                "packages/Mindigo/AuditLog/src/resources/css/app.css",
                "packages/Mindigo/AuditLog/src/resources/js/app.js",
                "packages/Mindigo/Report/src/resources/css/app.css",
                "packages/Mindigo/Report/src/resources/js/app.js",
                "packages/Teacher/TeacherDashboard/src/resources/css/app.css",
                "packages/Teacher/TeacherDashboard/src/resources/js/app.js",
                "packages/Teacher/TeacherClassroom/src/resources/css/app.css",
                "packages/Teacher/TeacherClassroom/src/resources/js/app.js",
                "packages/Teacher/TeacherCourse/src/resources/css/app.css",
                "packages/Teacher/TeacherCourse/src/resources/js/app.js",
                "packages/Teacher/TeacherAssignment/src/resources/js/app.js",
                "packages/Students/StudentExam/src/resources/css/app.css",
                "packages/Students/StudentExam/src/resources/js/app.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
