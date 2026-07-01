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
curl error 28 while downloading https://codeload.github.com/...
Operation timed out after 300004 milliseconds
```

This is usually a slow or unstable network connection to GitHub. The Dockerfile uses a Composer cache mount and a longer timeout, so run:

```bash
DOCKER_BUILDKIT=1 docker compose build app
```

If it still times out, run the same build command again. Successful partial downloads can be reused by Docker/BuildKit.

## Git clone fails with RPC failed or early EOF

Symptom:

```text
error: RPC failed; curl 18 transfer closed with outstanding read data remaining
fatal: early EOF
fatal: fetch-pack: invalid index-pack output
```

Try a blobless shallow clone:

```bash
git clone --depth 1 --filter=blob:none https://github.com/scoppy9201/Mindigo.git mindigo
```

If the failed folder already exists:

```bash
mv mindigo mindigo_failed_$(date +%Y%m%d_%H%M%S)
git clone --depth 1 --filter=blob:none https://github.com/scoppy9201/Mindigo.git mindigo
```

