# Passkey Production Checklist

## Infrastructure

- [ ] HTTPS is enabled for every passkey origin.
- [ ] `APP_URL` matches the real relying party domain.
- [ ] Cache limiter storage is available and healthy.
- [ ] Queue workers are running for background security processing.
- [ ] Monitoring is active for authentication traffic and failures.

## Security

- [ ] Passkey features are disabled by default until rollout approval.
- [ ] Audit logging is enabled in production.
- [ ] Registration and login rate limits have been tested.
- [ ] Device and passkey revocation flows have been tested.
- [ ] Recovery guidance for lost or stale passkeys is documented.
- [ ] Sensitive actions require recent authentication.

## Application

- [ ] Passkey registration succeeds end to end.
- [ ] Passkey-first login succeeds end to end.
- [ ] Revoked devices can no longer authenticate.
- [ ] Stale device credentials return a clear recovery message.
- [ ] Dashboard data is visible only to authenticated users.
- [ ] Feature tests covering security edge cases are passing.

## Rollout

- [ ] `passkeys.enabled` is scoped to the initial rollout audience.
- [ ] Registration rollout is staged separately from login rollout.
- [ ] Device management rollout is enabled only after support review.
- [ ] Risk-event monitoring is watched before increasing exposure.

## Operations

- [ ] Incident owner for authentication issues is identified.
- [ ] Emergency disable path for passkeys is tested.
- [ ] Support has steps for removing broken or stale passkeys.
- [ ] Browser-specific known issues are captured for triage.
