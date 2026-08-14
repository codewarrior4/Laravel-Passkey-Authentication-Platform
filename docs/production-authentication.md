# Production Authentication Rollout

## Current Friday State

The passkey module now supports:

- WebAuthn registration
- Passkey-first authentication
- Protected dashboard access
- Device rename and revoke actions
- Passkey revoke actions
- Authentication audit events
- Rate limits on registration, login, and device-management endpoints
- Suspicious activity event recording

## Rollout Plan

1. Enable passkeys for internal developer accounts.
2. Validate registration, login, revoke, and recovery flows.
3. Expand to internal staff and support users.
4. Review error rate, success rate, and suspicious activity volume.
5. Gradually widen access only after operational review.

## Monitoring

Watch these events closely:

- `passkey.registration.completed`
- `passkey.authentication.completed`
- `passkey.authentication.failed`
- `passkey.revoked`
- `device.revoked`
- `suspicious.activity.detected`

## Rollback

If authentication issues spike:

1. Disable passkey-facing features via feature flags.
2. Keep existing sessions alive where safe.
3. Triage recent authentication failures and suspicious events.
4. Remove broken credentials for affected users if needed.
5. Re-enable only after root cause review.

## Incident Response

If a device is lost or abused:

1. Sign in from another trusted device.
2. Open the protected dashboard.
3. Revoke the affected device and any stale passkeys.
4. Review recent audit events.
5. Register a replacement passkey if necessary.
