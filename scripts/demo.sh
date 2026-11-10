#!/usr/bin/env bash
# Run from the project root after:  php artisan migrate:fresh --seed
# Needs: `php artisan serve` and `php artisan queue:work` running in other terminals.
set -euo pipefail

BASE="${BASE:-http://127.0.0.1:8000/api}"
KEY="abe82246-0316-468a-a053-df27336b92d8"
RUN_PREFIX="demo-$(date +%s)-$$"

step() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }

admin() {
  curl -s -w '\n-> HTTP %{http_code}\n' \
    -H "X-Admin-Key: $KEY" -H 'Accept: application/json' -H 'Content-Type: application/json' "$@"
}

trigger() { admin -X POST "$BASE/admin/scraping-jobs" -d "{\"company_id\":$1,\"spider\":\"$2\"}"; }

# Pull data.id out of a one-line JSON response.
data_id() { sed -n 's/.*"data":{"id":\([0-9]*\).*/\1/p'; }

step "1. Missing admin key -> 401"
curl -s -w '\n-> HTTP %{http_code}\n' -H 'Accept: application/json' "$BASE/admin/companies"

step "2. List companies (seeded)"
admin "$BASE/admin/companies"

step "3. Create a company -> 201"
OUT=$(admin -X POST "$BASE/admin/companies" \
  -d "{\"name\":\"Umbrella Corp\",\"serial_number\":\"S-$RANDOM\",\"indeed_email\":\"hr@umbrella.example\"}")
echo "$OUT"
COMPANY_ID=$(echo "$OUT" | data_id)
echo "Using company_id=$COMPANY_ID for the remaining steps."

step "4. Trigger an Indeed job for company $COMPANY_ID -> 201"
OUT=$(trigger "$COMPANY_ID" indeed-job-details)
echo "$OUT"
JOB1=$(echo "$OUT" | data_id)

step "5. Trigger again immediately -> 409"
trigger "$COMPANY_ID" indeed-job-details

step "6. Is it running? -> is_running true"
admin "$BASE/admin/scraping-jobs/status?company_id=$COMPANY_ID&spider=indeed-job-details"

step "7. Scraper calls back (job $JOB1) -> created 3"
php artisan scraper:simulate "$JOB1" --prefix="$RUN_PREFIX"

step "8. Stored postings and the job"
admin "$BASE/admin/postings?provider=indeed"
admin "$BASE/admin/scraping-jobs/$JOB1"

step "9. Same data again -> unchanged 3"
JOB2=$(trigger "$COMPANY_ID" indeed-job-details | data_id)
php artisan scraper:simulate "$JOB2" --prefix="$RUN_PREFIX"

step "10. One record changed -> updated 1, unchanged 2"
JOB3=$(trigger "$COMPANY_ID" indeed-job-details | data_id)
php artisan scraper:simulate "$JOB3" --prefix="$RUN_PREFIX" --update

step "11. Wrong signature -> 403"
JOB4=$(trigger "$COMPANY_ID" indeed-job-details | data_id)
curl -s -w '\n-> HTTP %{http_code}\n' -X POST "$BASE/scraping-jobs/indeed-job-details/complete" \
  -H 'Accept: application/json' -H 'Content-Type: application/json' -H 'X-HMAC-Signature: wrong' \
  -d "{\"job_id\":$JOB4,\"status\":\"success\",\"records\":[]}"

step "12. Failure, then retry -> 201 (retry_count 1)"
php artisan scraper:simulate "$JOB4" --prefix="$RUN_PREFIX" --fail || true
admin -X POST "$BASE/admin/scraping-jobs/$JOB4/retry"

step "13. Retrying a completed job -> 422"
admin -X POST "$BASE/admin/scraping-jobs/$JOB1/retry"

step "14. Validation errors"
admin -X POST "$BASE/admin/scraping-jobs" -d "{\"company_id\":$COMPANY_ID,\"spider\":\"nope\"}"
admin -X POST "$BASE/admin/scraping-jobs" -d '{"company_id":9999}'
admin "$BASE/admin/scraping-jobs/9999"

printf '\nDone. Check the log (php artisan pail) for "Posting synced" and "Scraping job finished".\n'