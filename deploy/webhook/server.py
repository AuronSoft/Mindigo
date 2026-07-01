#!/usr/bin/env python3
import hashlib
import hmac
import json
import os
import subprocess
from datetime import datetime, timezone
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from pathlib import Path


HOST = os.environ.get("WEBHOOK_HOST", "127.0.0.1")
PORT = int(os.environ.get("WEBHOOK_PORT", "9000"))
SECRET = os.environ.get("WEBHOOK_SECRET", "").strip()
DEPLOY_SCRIPT = os.environ.get("DEPLOY_SCRIPT", str(Path.home() / "webhook" / "deploy.sh"))
LOG_FILE = Path(os.environ.get("WEBHOOK_LOG", str(Path.home() / "webhook" / "webhook.log")))


def log(message: str) -> None:
    LOG_FILE.parent.mkdir(parents=True, exist_ok=True)
    line = f"{datetime.now(timezone.utc).isoformat()} {message}"
    print(line, flush=True)
    with LOG_FILE.open("a", encoding="utf-8") as file:
        file.write(line + "\n")


def valid_signature(body: bytes, *headers: str) -> bool:
    headers = [header for header in headers if header]
    if not SECRET or not headers:
        return False
    expected = hmac.new(SECRET.encode("utf-8"), body, hashlib.sha256).hexdigest()
    for header in headers:
        if not header.startswith("sha256="):
            continue
        received = header.split("=", 1)[1]
        if hmac.compare_digest(expected, received):
            return True
    return False


class DeployHandler(BaseHTTPRequestHandler):
    server_version = "MindigoWebhook/1.0"

    def do_POST(self) -> None:
        if self.path != "/deploy":
            self.respond(404, "Not found")
            return

        length = int(self.headers.get("Content-Length", "0"))
        body = self.rfile.read(length)
        mindigo_signature = self.headers.get("X-Mindigo-Signature", "")
        github_signature = self.headers.get("X-Hub-Signature-256", "")

        if not valid_signature(body, mindigo_signature, github_signature):
            log("Invalid signature")
            self.respond(403, "Invalid signature")
            return

        try:
            payload = json.loads(body.decode("utf-8") or "{}")
        except json.JSONDecodeError:
            self.respond(400, "Invalid JSON")
            return

        branch = str(payload.get("branch") or "main")
        commit = str(payload.get("commit") or "")
        repository = str(payload.get("repository") or "")

        log(f"Webhook received repository={repository} branch={branch} commit={commit}")

        env = os.environ.copy()
        env["DEPLOY_BRANCH"] = branch
        env["DEPLOY_COMMIT"] = commit
        env["DEPLOY_REPOSITORY"] = repository

        try:
            log_file = LOG_FILE.open("a", encoding="utf-8")
            subprocess.Popen(
                [DEPLOY_SCRIPT],
                env=env,
                text=True,
                stdout=log_file,
                stderr=subprocess.STDOUT,
                start_new_session=True,
            )
        except Exception as exc:
            log(f"Deploy failed to start: {exc}")
            self.respond(500, f"Deploy failed to start: {exc}")
            return

        self.respond(202, "Deploy accepted\n")

    def log_message(self, format: str, *args) -> None:
        log("%s - %s" % (self.address_string(), format % args))

    def respond(self, status: int, body: str) -> None:
        data = body.encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "text/plain; charset=utf-8")
        self.send_header("Content-Length", str(len(data)))
        self.end_headers()
        self.wfile.write(data)


if __name__ == "__main__":
    if not SECRET:
        raise SystemExit("WEBHOOK_SECRET is required")
    log(f"Webhook server listening on http://{HOST}:{PORT}/deploy")
    ThreadingHTTPServer((HOST, PORT), DeployHandler).serve_forever()
