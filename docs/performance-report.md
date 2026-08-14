# Passkey Performance Report

## Scope

This report is a local engineering baseline, not a production load test.

## Observations

- Registration and login feature tests complete quickly in local development.
- Challenge generation is lightweight and currently in-process.
- Authentication verification cost is dominated by signature verification and database lookups.
- Audit creation adds write overhead on every important authentication action.

## Likely Bottlenecks

- Database writes for audit events during heavy login volume
- Cache-backed rate limiter storage under burst traffic
- Signature verification throughput under high concurrency

## Next Performance Steps

- Measure challenge generation and verification timings under repeated requests
- Track query counts for dashboard and authentication flows
- Move limiter storage to Redis in production
- Add production metrics for success rate, failure rate, and suspicious-event frequency

## Notes

The current implementation is suitable for iterative internal rollout, but it still needs real production telemetry before broad exposure.
