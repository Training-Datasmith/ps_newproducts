#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_IMAGE="php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143"
PHPUNIT_URL="https://phar.phpunit.de/phpunit-4.8.36.phar"
PHPUNIT_ASC_URL="https://phar.phpunit.de/phpunit-4.8.36.phar.asc"
PHPUNIT_SHA256="ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e"
PHPUNIT_FINGERPRINT="D8406D0D82947747293778314AA394086372C20A"
EXPECTED_TESTS=52
EXPECTED_ASSERTIONS=252

PHAR_DIR="$(mktemp -d)"
JUNIT_DIR="$(mktemp -d)"
cleanup() {
  rm -rf "$PHAR_DIR" "$JUNIT_DIR"
}
trap cleanup EXIT

DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

curl -fsSL "$PHPUNIT_URL" -o "$PHAR_DIR/phpunit.phar"
curl -fsSL "$PHPUNIT_ASC_URL" -o "$PHAR_DIR/phpunit.phar.asc"
echo "$PHPUNIT_SHA256  $PHAR_DIR/phpunit.phar" | sha256sum -c -

GNUPGHOME="$(mktemp -d)"
export GNUPGHOME
gpg --batch --keyserver hkps://keys.openpgp.org --recv-keys "$PHPUNIT_FINGERPRINT" >/dev/null
gpg --batch --status-fd 1 --verify "$PHAR_DIR/phpunit.phar.asc" "$PHAR_DIR/phpunit.phar" 2>/dev/null | tee "$PHAR_DIR/gpg-status.txt" | grep -q "VALIDSIG"
FPR="$(awk '/^\[GNUPG:\] VALIDSIG/ {print $3; exit}' "$PHAR_DIR/gpg-status.txt")"
if [ "$FPR" != "$PHPUNIT_FINGERPRINT" ]; then
  echo "PHPUnit PHAR signature fingerprint mismatch: $FPR" >&2
  exit 1
fi
rm -rf "$GNUPGHOME"

run_phpunit() {
  local log="$1"
  local shuffle_env="$2"
  local junit="$JUNIT_DIR/$log.xml"
  "${DOCKER[@]}" run --rm \
    -v "$ROOT:/src:ro" \
    -v "$PHAR_DIR/phpunit.phar:/usr/local/bin/phpunit.phar:ro" \
    -v "$JUNIT_DIR:$JUNIT_DIR" \
    -w /src \
    --env TEST_SHUFFLE_SEED="$shuffle_env" \
    "$PHP_IMAGE" \
    php -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 -r 'if (!(PHP_VERSION === "5.6.40" && PHP_INT_SIZE === 8)) {fwrite(STDERR, "bad php runtime\n"); exit(1);}' \
    && "${DOCKER[@]}" run --rm \
    -v "$ROOT:/src:ro" \
    -v "$PHAR_DIR/phpunit.phar:/usr/local/bin/phpunit.phar:ro" \
    -v "$JUNIT_DIR:$JUNIT_DIR" \
    -w /src \
    --env TEST_SHUFFLE_SEED="$shuffle_env" \
    "$PHP_IMAGE" \
    php -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 /usr/local/bin/phpunit.phar \
      --configuration /src/tests/phpunit.xml \
      --log-junit "$junit" \
      2>&1 | tee "$JUNIT_DIR/$log.txt"
}

assert_log_ok() {
  local log="$1"
  local file="$JUNIT_DIR/$log.txt"
  if grep -E 'PHPUnit Warning:|Notice:|Warning:|Deprecated:|Strict Standards:|Parse error:|Fatal error:' "$file"; then
    echo "Diagnostics found in $log" >&2
    exit 1
  fi
  if grep -E 'Skipped|Incomplete|Risky|FAILURES|ERRORS!' "$file"; then
    echo "Bad PHPUnit summary in $log" >&2
    exit 1
  fi
  local summary
  summary="$(grep -E '^OK \(' "$file" | tail -1)"
  if [ -z "$summary" ]; then
    echo "Missing OK summary in $log" >&2
    exit 1
  fi
  if ! echo "$summary" | grep -q "($EXPECTED_TESTS tests"; then
    echo "Unexpected test count in $summary" >&2
    exit 1
  fi
  if [ "$EXPECTED_ASSERTIONS" -eq 0 ]; then
    EXPECTED_ASSERTIONS="$(echo "$summary" | sed -n 's/^OK ([0-9]* tests, \([0-9]*\) assertions)$/\1/p')"
    export EXPECTED_ASSERTIONS
  else
    if ! echo "$summary" | grep -q ", $EXPECTED_ASSERTIONS assertions)"; then
      echo "Assertion count drift in $log" >&2
      exit 1
    fi
  fi
}

run_phpunit sorted ""
assert_log_ok sorted
run_phpunit shuffled "20261010"
assert_log_ok shuffled

cd "$ROOT"
if [ -n "$(git status --porcelain)" ]; then
  echo "Working tree not clean after tests" >&2
  git status --porcelain
  exit 1
fi

echo "SUITE_OK tests=$EXPECTED_TESTS assertions=$EXPECTED_ASSERTIONS"
