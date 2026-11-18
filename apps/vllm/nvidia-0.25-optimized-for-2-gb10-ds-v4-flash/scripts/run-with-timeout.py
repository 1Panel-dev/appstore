import math
import os
import signal
import subprocess
import sys
import time


def interrupted(signum, frame):
    raise SystemExit(128 + signum)


def main():
    owner = int(sys.argv[1])
    duration = float(sys.argv[2])
    if not math.isfinite(duration) or duration <= 0:
        raise ValueError("Command timeout must be positive and finite")
    os.setpgid(0, 0)
    for signum in (signal.SIGTERM, signal.SIGINT, signal.SIGHUP):
        signal.signal(signum, interrupted)
    if os.getppid() != owner:
        return 125
    deadline = time.monotonic() + duration
    command = None
    try:
        command = subprocess.Popen(sys.argv[3:], start_new_session=True)
        while True:
            if os.getppid() != owner:
                return 125
            remaining = deadline - time.monotonic()
            if remaining <= 0:
                print(f"Command timed out after {duration:g} seconds", file=sys.stderr)
                return 124
            try:
                status = command.wait(timeout=min(0.2, remaining))
                return status if status >= 0 else 128 - status
            except subprocess.TimeoutExpired:
                pass
    finally:
        if command is not None:
            try:
                os.killpg(command.pid, signal.SIGKILL)
            except ProcessLookupError:
                pass
            command.wait()


if __name__ == "__main__":
    sys.exit(main())
