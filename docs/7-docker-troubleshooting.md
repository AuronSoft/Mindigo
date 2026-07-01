# Docker troubleshooting

## Docker build fails at COPY resources

Symptom:

```text
COPY resources ./resources
failed to calculate checksum: "/resources": not found
```

The project does not have a root `resources` directory. Vite inputs are loaded from `packages/.../src/resources`, so the Dockerfile should copy `packages` instead:

```dockerfile
COPY vite.config.js ./
COPY packages ./packages
RUN npm run build
```

After updating the Dockerfile on the server, rebuild:

```bash
DOCKER_BUILDKIT=1 docker compose build app
```

## Composer timeout while building Docker image

Symptom:

```text
RUN composer install ...
curl error 28 while downloading https://codeload.github.com/...
Operation timed out after 300004 milliseconds
```

This is a network/download problem, not a Laravel code problem. Composer is downloading packages from GitHub too slowly inside the Docker build.

The Dockerfile is optimized for this case:

- Composer dependencies are installed before copying the whole app source.
- BuildKit cache stores Composer downloads between builds.
- Composer timeout is increased.
- Composer parallel downloads are limited to reduce unstable network pressure.

Build with:

```bash
DOCKER_BUILDKIT=1 docker compose build app
```

Then start the stack:

```bash
docker compose up -d
docker compose ps
docker compose logs --tail=100 app
```

If it still times out, run the build command again. After Composer downloads some packages successfully, Docker/BuildKit can reuse cached files on the next attempt.

## Faster production deployment

For production, prefer CI/CD with GitHub Container Registry:

```text
GitHub Actions builds the image
GitHub Actions pushes it to GHCR
Ubuntu Server pulls the ready image
```

This is faster than building on the Ubuntu Server because the server only downloads the final image instead of running `composer install` and `npm run build`.

## Git clone fails with RPC failed or early EOF

Symptom:

```text
error: RPC failed; curl 18 transfer closed with outstanding read data remaining
fetch-pack: unexpected disconnect while reading sideband packet
fatal: early EOF
fatal: fetch-pack: invalid index-pack output
```

This is usually a slow or unstable network connection to GitHub. Try a blobless shallow clone first:

```bash
git clone --depth 1 --filter=blob:none https://github.com/scoppy9201/Mindigo.git mindigo
```

If the folder already exists from a failed clone, remove or rename it before cloning again:

```bash
mv mindigo mindigo_failed_$(date +%Y%m%d_%H%M%S)
git clone --depth 1 --filter=blob:none https://github.com/scoppy9201/Mindigo.git mindigo
```

If it still fails, force Git to use HTTP/1.1 and increase the low-speed timeout:

```bash
git config --global http.version HTTP/1.1
git config --global http.lowSpeedLimit 0
git config --global http.lowSpeedTime 999999
git clone --depth 1 --filter=blob:none https://github.com/scoppy9201/Mindigo.git mindigo
```
