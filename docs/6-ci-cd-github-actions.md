# CI/CD webhook + ngrok cho Mindigo

Tài liệu này dùng cho môi trường:

- Ubuntu Server chạy trong máy ảo VMware/VirtualBox.
- Không có public IP.
- GitHub Actions không SSH được vào máy ảo.
- Dùng ngrok để GitHub gọi webhook vào Ubuntu.

## 1. Mô hình triển khai

```text
GitHub
  |
  v
GitHub Actions
  |
  |- composer install
  |- npm ci
  |- npm run build
  |- composer test
  |- docker compose build app
  |
  v
Notify Deploy Webhook
  |
  v
ngrok URL /deploy
  |
  v
webhook server trên Ubuntu
  |
  v
deploy.sh
  |
  |- git pull
  |- docker compose build app
  |- docker compose up -d
  |- php artisan migrate --force
  |- php artisan optimize:clear
  |
  v
mindigo.ommicom.com hoặc http://<IP_UBUNTU>:8080
```

Ưu điểm:

- Không cần public IP.
- Không cần mở port SSH 22 ra Internet.
- Hợp với Ubuntu chạy trong máy ảo.
- Dễ demo CI/CD khi thi.

## 2. GitHub Actions Deploy

File chính:

```text
.github/workflows/deploy.yml
```

Khi push vào `main`, workflow sẽ:

```text
Checkout source
Setup PHP 8.3
Setup Node 20
composer install
php artisan key:generate
npm ci
npm run build
composer test
docker compose build app
POST WEBHOOK_URL với chữ ký HMAC
```

Bước test Laravel đang có:

```yaml
continue-on-error: true
```

Nghĩa là test fail vẫn hiện warning, nhưng không chặn deploy trong giai đoạn bạn đang test CI/CD.

## 3. GitHub Secrets cần tạo

Vào GitHub repo:

```text
Settings -> Secrets and variables -> Actions -> New repository secret
```

Tạo:

| Secret | Giá trị ví dụ |
| --- | --- |
| `WEBHOOK_URL` | `https://inflationary-unpsychologically-marlena.ngrok-free.dev/deploy` |
| `WEBHOOK_SECRET` | `mindigo-secret` |

`WEBHOOK_SECRET` phải giống hệt biến `WEBHOOK_SECRET` khi chạy webhook server trên Ubuntu.

## 4. Chuẩn bị project trên Ubuntu

Project hiện nằm ở:

```bash
/home/hung/mindigo/Mindigo
```

Kiểm tra:

```bash
cd /home/hung/mindigo/Mindigo
git status
docker --version
docker compose version
```

Test deploy thủ công trước:

```bash
cd /home/hung/mindigo/Mindigo
git pull origin main
DOCKER_BUILDKIT=1 docker compose build app
docker compose up -d
docker compose ps
curl -I http://localhost:8080
```

## 5. Tạo thư mục webhook trên Ubuntu

Trên Ubuntu:

```bash
mkdir -p ~/webhook
cd ~/webhook
```

Copy 2 file trong repo lên Ubuntu:

```text
deploy/webhook/server.py
deploy/webhook/deploy.sh
```

Nếu bạn đang ở thư mục project trên Ubuntu:

```bash
cp /home/hung/mindigo/Mindigo/deploy/webhook/server.py ~/webhook/server.py
cp /home/hung/mindigo/Mindigo/deploy/webhook/deploy.sh ~/webhook/deploy.sh
chmod +x ~/webhook/server.py ~/webhook/deploy.sh
```

## 6. Chạy webhook server

Chạy thử:

```bash
cd ~/webhook
WEBHOOK_SECRET=mindigo-secret python3 server.py
```

Webhook server sẽ nghe ở:

```text
http://127.0.0.1:9000/deploy
```

Mở tab SSH khác, test local:

```bash
curl -X POST http://127.0.0.1:9000/deploy
```

Kết quả đúng:

```text
Invalid signature
```

Nó đúng vì bạn gọi tay chưa gửi chữ ký HMAC.

## 7. Cài và chạy ngrok

### Cách nhanh bằng snap

```bash
sudo snap install ngrok
```

Thêm token ngrok:

```bash
ngrok config add-authtoken <NGROK_AUTHTOKEN_CUA_BAN>
```

Chạy tunnel:

```bash
ngrok http 9000
```

Ngrok sẽ hiện URL dạng:

```text
https://xxxx.ngrok-free.app
```

GitHub Secret cần điền:

```text
WEBHOOK_URL=https://xxxx.ngrok-free.app/deploy
WEBHOOK_SECRET=mindigo-secret
```

### Nếu bạn có static domain ngrok

Ví dụ:

```text
https://inflationary-unpsychologically-marlena.ngrok-free.dev
```

Chạy:

```bash
ngrok http --domain=inflationary-unpsychologically-marlena.ngrok-free.dev 9000
```

GitHub Secret:

```text
WEBHOOK_URL=https://inflationary-unpsychologically-marlena.ngrok-free.dev/deploy
WEBHOOK_SECRET=mindigo-secret
```

## 8. Test ngrok tới webhook

Khi webhook server và ngrok đều đang chạy:

```bash
curl -X POST https://inflationary-unpsychologically-marlena.ngrok-free.dev/deploy
```

Kết quả đúng:

```text
Invalid signature
```

Trong ngrok log có thể thấy:

```text
POST /deploy 403 Forbidden
```

Đây là dấu hiệu tốt:

- ngrok online.
- Internet gọi được vào Ubuntu VM.
- webhook server đang chạy.
- endpoint `/deploy` hoạt động.
- request bị chặn vì thiếu chữ ký bảo mật.

## 9. Test thật bằng GitHub Actions

Trên Ubuntu mở log:

```bash
tail -f ~/webhook/webhook.log
```

Ở Windows/local:

```bash
git add .
git commit -m "test webhook ci cd"
git push origin main
```

Hoặc vào GitHub:

```text
Actions -> Deploy -> Run workflow -> branch main
```

Nếu đúng, log trên Ubuntu sẽ có:

```text
Webhook received repository=scoppy9201/Mindigo branch=main commit=...
==== DEPLOY START ...
==== DEPLOY DONE ...
```

Trong GitHub Actions, bước gọi webhook sẽ nhận:

```text
Deploy accepted
Webhook status: 202
```

Webhook trả lời GitHub ngay sau khi xác thực chữ ký. Phần deploy chạy nền trên Ubuntu và ghi log vào `~/webhook/webhook.log`.

Nếu vẫn thấy:

```text
Invalid signature
```

thì kiểm tra lại:

- `WEBHOOK_SECRET` trên GitHub.
- `WEBHOOK_SECRET` khi chạy `python3 server.py`.
- Hai giá trị phải giống hệt nhau.

## 10. Chạy webhook bằng systemd

Tạo file service:

```bash
sudo nano /etc/systemd/system/mindigo-webhook.service
```

Dán nội dung:

```ini
[Unit]
Description=Mindigo deploy webhook
After=network.target

[Service]
Type=simple
User=hung
WorkingDirectory=/home/hung/webhook
Environment=WEBHOOK_SECRET=mindigo-secret
Environment=WEBHOOK_PORT=9000
Environment=APP_DIR=/home/hung/mindigo/Mindigo
ExecStart=/usr/bin/python3 /home/hung/webhook/server.py
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Bật service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable mindigo-webhook
sudo systemctl start mindigo-webhook
sudo systemctl status mindigo-webhook
```

Xem log:

```bash
journalctl -u mindigo-webhook -f
tail -f ~/webhook/webhook.log
```

## 11. Chạy ngrok lâu dài

Tạo service ngrok:

```bash
sudo nano /etc/systemd/system/mindigo-ngrok.service
```

Nếu dùng static domain, dán:

```ini
[Unit]
Description=Mindigo ngrok tunnel
After=network.target

[Service]
Type=simple
User=hung
ExecStart=/snap/bin/ngrok http --domain=inflationary-unpsychologically-marlena.ngrok-free.dev 9000
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Nếu không dùng static domain, dán:

```ini
[Unit]
Description=Mindigo ngrok tunnel
After=network.target

[Service]
Type=simple
User=hung
ExecStart=/snap/bin/ngrok http 9000
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Bật service:

```bash
sudo systemctl daemon-reload
sudo systemctl enable mindigo-ngrok
sudo systemctl start mindigo-ngrok
sudo systemctl status mindigo-ngrok
```

Xem log:

```bash
journalctl -u mindigo-ngrok -f
```

Lưu ý: nếu không có static domain, ngrok URL sẽ đổi sau mỗi lần chạy lại. Khi đó phải cập nhật lại `WEBHOOK_URL` trong GitHub Secrets.

## 12. Lệnh kiểm tra sau deploy

```bash
cd /home/hung/mindigo/Mindigo
docker compose ps
docker compose logs --tail=100 app
curl -I http://localhost:8080
```

Từ Windows mở:

```text
http://<IP_UBUNTU>:8080
```

## 13. Lỗi thường gặp

### GitHub Actions báo Invalid signature hoặc 403

Nguyên nhân:

- `WEBHOOK_SECRET` trên GitHub khác với `WEBHOOK_SECRET` trên Ubuntu.
- Bạn test bằng `curl` thường, không gửi chữ ký HMAC.

Kiểm tra:

```bash
echo $WEBHOOK_SECRET
tail -f ~/webhook/webhook.log
```

### GitHub Actions không gọi được webhook

Kiểm tra ngrok:

```bash
systemctl status mindigo-ngrok
journalctl -u mindigo-ngrok -f
```

Nếu không dùng static domain, URL ngrok có thể đổi. Cập nhật lại:

```text
GitHub -> Settings -> Secrets and variables -> Actions -> WEBHOOK_URL
```

### Deploy không chạy

Kiểm tra webhook:

```bash
systemctl status mindigo-webhook
journalctl -u mindigo-webhook -f
tail -f ~/webhook/webhook.log
```

### Báo Another deploy is already running

Đang có một deploy khác chạy. Nếu chắc chắn không còn process deploy, xóa lock:

```bash
rm -rf /tmp/mindigo-deploy.lock
```
