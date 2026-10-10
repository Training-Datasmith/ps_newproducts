#!/usr/bin/env bash
set -euo pipefail

if [ "${RUN_PROOF:-0}" != "1" ]; then
  echo "Set RUN_PROOF=1 to run mutation proofs"
  exit 0
fi

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
MODULE="$ROOT/ps_newproducts.php"
BACKUP="$(mktemp)"
cp "$MODULE" "$BACKUP"
trap 'cp "$BACKUP" "$MODULE"; rm -f "$BACKUP"' EXIT

python3 - <<'PY'
from pathlib import Path
path = Path("ps_newproducts.php")
text = path.read_text()
old = """            } elseif (!$this->isAcceptableUnsignedSetting($productNbr)
                || !$this->isAcceptableUnsignedSetting($productNbDays)
                || (int) $productNbr < 1
                || (int) $productNbDays < 1) {"""
new = """            } elseif (0 === (int) $productNbr || 0 === (int) $productNbDays) {"""
if old not in text:
    raise SystemExit('expected validation block not found')
path.write_text(text.replace(old, new, 1))
PY

cd "$ROOT"
if tests/run-suite.sh; then
  echo "Proof failed: suite still passed after reverting validation" >&2
  exit 1
fi

echo "PROOF_OK validation revert caused expected failure"
