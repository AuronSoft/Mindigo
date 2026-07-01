# CI/CD GitHub Actions cho Mindigo

Project dùng 2 workflow:

- `.github/workflows/ci.yml`: chạy khi push/pull request vào `dev` hoặc `main`.
- `.github/workflows/deploy.yml`: deploy sau khi CI của `main` thành công, hoặc chạy thủ công trong tab Actions.

## 1. CI làm gì?

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
push Docker image lên GHCR
```

CI push image theo tag branch:

```text
ghcr.io/<owner>/<repo>:main
ghcr.io/<owner>/<repo>:dev
```

## 2. CD làm gì?

Khi CI của `main` xanh:

```text
GitHub Actions SSH vào Ubuntu
cd /home/hung/mindigo/Mindigo
git pull --ff-only origin main
docker login ghcr.io
docker compose pull app
backup database nếu db đang chạy
docker compose up -d --no-build --remove-orphans
php artisan migrate --force
php artisan optimize:clear
curl test http://localhost:8080
```

Deploy hiện dùng image tag theo branch để test ổn định:

```text
ghcr.io/<owner>/<repo>:main
```

Sau khi chạy ổn, có thể nâng tiếp sang deploy theo commit SHA.

## 3. docker-compose cần hỗ trợ GHCR image

Service `app` cần có dạng:

```yaml
app:
  image: ${APP_IMAGE:-mindigo-app}
  build:
    context: .
    dockerfile: Dockerfile
```

Ý nghĩa:

- Local không truyền `APP_IMAGE` thì vẫn build image `mindigo-app`.
- CI/CD truyền `APP_IMAGE=ghcr.io/<owner>/<repo>:main` thì server pull image từ GHCR.

## 4. GitHub Secrets cần tạo

Vào GitHub repo:

```text
Settings -> Secrets and variables -> Actions -> New repository secret
```

Tạo:

| Secret | Giá trị ví dụ |
| --- | --- |
| `SERVER_HOST` | IP Ubuntu Server |
| `SERVER_USER` | `hung` |
| `SERVER_PORT` | `22` |
| `SERVER_PATH` | `/home/hung/mindigo/Mindigo` |
| `SERVER_SSH_KEY` | private key SSH |
| `GHCR_USERNAME` | `scoppy9201` |
| `GHCR_TOKEN` | token có quyền `read:packages` |

`SERVER_PATH` phải đúng với server hiện tại:

```text
/home/hung/mindigo/Mindigo
```

## 5. Test thủ công trên server trước

Trên Ubuntu:

```bash
cd /home/hung/mindigo/Mindigo
git pull origin main
DOCKER_BUILDKIT=1 docker compose build app
docker compose up -d
docker compose ps
docker compose logs --tail=100 app
```

Nếu bước này ổn, CD sẽ dễ xanh hơn.

## 6. Test Deploy workflow

Vào GitHub:

```text
Actions -> Deploy -> Run workflow -> branch main
```

Sau khi chạy xong, kiểm tra trên server:

```bash
cd /home/hung/mindigo/Mindigo
docker compose ps
docker inspect mindigo_app --format '{{.Config.Image}}'
cat .deploy-release
curl -I http://localhost:8080
```

Kết quả image nên là:

```text
ghcr.io/scoppy9201/mindigo:main
```

## 7. Rollback và backup

Workflow lưu image deploy gần nhất trong:

```bash
/home/hung/mindigo/Mindigo/.deploy-release
```

Backup database nằm trong:

```bash
/home/hung/mindigo/Mindigo/backups
```

Nếu deploy lỗi sau khi đổi image, workflow sẽ thử rollback app về image trước đó. Database không tự restore để tránh ghi đè dữ liệu ngoài ý muốn.

