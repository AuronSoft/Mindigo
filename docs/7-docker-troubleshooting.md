# Docker troubleshooting

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

