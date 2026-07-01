# CI/CD GitHub Actions cho Mindigo

CI/CD trong project này dùng 2 workflow:

- `.github/workflows/ci.yml`: chạy khi push/pull request vào `dev` hoặc `main`.
- `.github/workflows/deploy.yml`: deploy tự động sau khi CI của branch `main` thành công, hoặc bấm chạy thủ công.

## 1. Luồng CI

Khi push code:

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
push Docker image lên GHCR khi push code
```

Nếu bước nào lỗi, GitHub sẽ báo fail để sửa trước khi deploy.

## 2. Luồng CD

Khi push vào `main` và CI thành công:

```text
GitHub Actions lấy đúng Docker image theo commit SHA
GitHub Actions SSH vào Ubuntu
cd ~/Mindigo
git pull --ff-only origin main
docker compose pull app
backup database
docker compose up -d --no-build --remove-orphans
php artisan migrate --force trong container app
php artisan optimize:clear trong container app
docker compose ps
curl test http://localhost:8080
```

Workflow deploy sẽ dừng nếu thư mục project trên Ubuntu có code đang sửa chưa commit.
Nếu deploy lỗi sau khi đổi image, workflow sẽ thử rollback container app về image đã deploy trước đó. Database sẽ không tự restore để tránh ghi đè dữ liệu ngoài ý muốn.

## 3. Chuẩn bị Ubuntu Server

Trên Ubuntu, project nên nằm ở:

```bash
~/Mindigo
```

Kiểm tra:

```bash
cd ~/Mindigo
git remote -v
git status
docker --version
docker compose version
docker compose ps
```

User SSH dùng để deploy phải chạy được Docker không cần `sudo`:

```bash
sudo usermod -aG docker $USER
newgrp docker
docker info
```

## 4. Tạo SSH key cho deploy

Trên máy cá nhân hoặc Ubuntu, tạo key riêng cho GitHub Actions:

```bash
ssh-keygen -t ed25519 -C "mindigo-github-actions" -f ~/.ssh/mindigo_github_actions
```

Copy public key vào Ubuntu server:

```bash
cat ~/.ssh/mindigo_github_actions.pub
```

Thêm nội dung public key vào file:

```bash
~/.ssh/authorized_keys
```

Private key dùng cho GitHub Secret:

```bash
cat ~/.ssh/mindigo_github_actions
```

## 5. Tạo GitHub Secrets

Vào GitHub repo:

```text
Settings -> Secrets and variables -> Actions -> New repository secret
```

Tạo các secret:

| Secret | Ví dụ | Ghi chú |
| --- | --- | --- |
| `SERVER_HOST` | `192.168.1.50` | IP Ubuntu Server |
| `SERVER_USER` | `hung` | User SSH |
| `SERVER_PORT` | `22` | Port SSH |
| `SERVER_SSH_KEY` | private key | Nội dung file `mindigo_github_actions` |
| `SERVER_PATH` | `/home/hung/Mindigo` | Có thể bỏ trống nếu dùng `~/Mindigo` |
| `GHCR_USERNAME` | username GitHub | Cần nếu GHCR package để private |
| `GHCR_TOKEN` | GitHub PAT | Cần quyền `read:packages` nếu GHCR package để private |

Ghi chú:

- CI dùng sẵn `GITHUB_TOKEN` để push image lên GitHub Container Registry.
- Nếu package GHCR để public thì server có thể pull không cần `GHCR_USERNAME` và `GHCR_TOKEN`.
- Nếu package GHCR để private, tạo GitHub Personal Access Token có quyền `read:packages`.

## 6. Cách dùng hằng ngày

Làm việc trên branch `dev`:

```bash
git checkout dev
git add .
git commit -m "Update feature"
git push origin dev
```

GitHub sẽ chạy CI.

Khi muốn deploy:

```bash
git checkout main
git merge dev
git push origin main
```

GitHub sẽ chạy CD và deploy lên Ubuntu.

## 7. Chạy deploy thủ công

Vào GitHub repo:

```text
Actions -> Deploy -> Run workflow
```

Chọn branch cần deploy, ví dụ `main` hoặc `dev`.

## 8. Test sau deploy

Trên Ubuntu:

```bash
cd ~/Mindigo
docker compose ps
docker compose logs --tail=100 app
curl -I http://localhost:8080
```

Kiểm tra image đang chạy:

```bash
docker inspect mindigo_app --format '{{.Config.Image}}'
cat ~/Mindigo/.deploy-release
```

Backup database nằm trong:

```bash
~/Mindigo/backups
```

Lần deploy đầu tiên có thể chưa có container `db` đang chạy, khi đó bước backup sẽ được bỏ qua và deploy vẫn tiếp tục.

Từ Windows mở:

```text
http://<IP_UBUNTU>:8080
http://<IP_UBUNTU>:8081
```
