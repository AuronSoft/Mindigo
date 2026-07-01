#!/usr/bin/env bash
set -Eeuo pipefail

APP_DIR="${APP_DIR:-/home/hung/mindigo/Mindigo}"
BRANCH="${DEPLOY_BRANCH:-main}"
LOCK_DIR="${LOCK_DIR:-/tmp/mindigo-deploy.lock}"

if ! mkdir "$LOCK_DIR" 2>/dev/null; then
  echo "Another deploy is already running."
  exit 1
fi
trap 'rm -rf "$LOCK_DIR"' EXIT

echo "==== DEPLOY START $(date -Is) ===="
echo "APP_DIR=$APP_DIR"
echo "BRANCH=$BRANCH"
echo "COMMIT=${DEPLOY_COMMIT:-}"

command -v git
command -v docker
docker compose version

cd "$APP_DIR"

if [ -n "$(git status --short)" ]; then
  echo "Working tree has uncommitted changes. Commit or stash them before deploy."
  git status --short
  exit 1
fi

git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

DOCKER_BUILDKIT=1 docker compose build app
docker compose up -d --remove-orphans
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize:clear
docker image prune -f
docker compose ps
curl -fsS -I http://localhost:8080

echo "==== DEPLOY DONE $(date -Is) ===="
