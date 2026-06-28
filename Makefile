#  Mindigo LMS — Makefile (lối tắt cho các lệnh Docker hay dùng)
#  Dùng trên server Linux có 'make'.  Ví dụ:  make up   |   make logs

.PHONY: up build down stop start restart ps logs shell db seed migrate fresh key assets help

help:            ## Liệt kê các lệnh
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN{FS=":.*?## "}{printf "  \033[36m%-10s\033[0m %s\n", $$1, $$2}'

up:              ## Build image + chạy nền (lần đầu)
	docker compose up -d --build

build:           ## Chỉ build lại image
	docker compose build

down:            ## Dừng & xoá container (giữ volume dữ liệu)
	docker compose down

stop:            ## Dừng container
	docker compose stop

start:           ## Chạy lại container đã dừng
	docker compose start

restart:         ## Khởi động lại
	docker compose restart

ps:              ## Xem container đang chạy
	docker compose ps

logs:            ## Xem log container web (Ctrl+C để thoát)
	docker compose logs -f app

shell:           ## Vào shell container web
	docker compose exec app bash

db:              ## Vào MySQL trong container db
	docker compose exec db mysql -u mindigo -psecret mindigo

migrate:         ## Chạy migration
	docker compose exec app php artisan migrate --force

seed:            ## Tạo dữ liệu mẫu (admin/teacher/student + lớp)
	docker compose exec app php artisan db:seed

fresh:           ## Reset DB + seed lại (CẨN THẬN: mất dữ liệu hiện có)
	docker compose exec app php artisan migrate:fresh --seed --force

key:             ## Sinh APP_KEY
	docker compose exec app php artisan key:generate

assets:          ## Build lại CSS/JS (Vite) bên trong môi trường node
	docker compose exec app php artisan optimize:clear
