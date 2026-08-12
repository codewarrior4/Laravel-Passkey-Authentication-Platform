document.querySelectorAll('[data-passkey-register]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const errorBox = form.querySelector('[data-passkey-error]');
        const submitButton = form.querySelector('[data-passkey-submit]');
        const finishUrl = form.dataset.finishUrl;

        hideError(errorBox);
        toggleButton(submitButton, true, 'Preparing passkey request...');

        try {
            if (!window.isSecureContext) {
                throw new Error('Passkey registration requires HTTPS. Open this app with https://passkeys.test.');
            }

            if (isIpAddress(window.location.hostname)) {
                throw new Error('Passkey registration cannot run from 127.0.0.1 because browsers require a real relying-party domain. Open this app with https://passkeys.test.');
            }

            if (!window.PublicKeyCredential || !navigator.credentials?.create) {
                throw new Error('This browser does not support passkey registration.');
            }

            const startResponse = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
            });

            const startPayload = await startResponse.json();

            if (!startResponse.ok) {
                throw new Error(startPayload.message ?? 'Unable to start passkey registration.');
            }

            const publicKey = normalizeCreationOptions(startPayload.public_key);
            const credential = await navigator.credentials.create({ publicKey });

            if (!credential) {
                throw new Error('No credential was created by the authenticator.');
            }

            const attestationResponse = credential.response;
            const finishResponse = await fetch(finishUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    authenticator_data: bufferToBase64Url(attestationResponse.getAuthenticatorData()),
                    client_data_json: bufferToBase64Url(attestationResponse.clientDataJSON),
                    credential_id: bufferToBase64Url(credential.rawId),
                    origin: window.location.origin,
                    passkey_id: startPayload.passkey_id,
                    public_key: bufferToBase64Url(attestationResponse.getPublicKey()),
                    public_key_algorithm: attestationResponse.getPublicKeyAlgorithm(),
                    transports: typeof attestationResponse.getTransports === 'function' ? attestationResponse.getTransports() : [],
                }),
            });

            const finishPayload = await finishResponse.json();

            if (!finishResponse.ok) {
                throw new Error(finishPayload.message ?? 'Unable to complete passkey registration.');
            }

            window.location.assign(finishPayload.redirect_to);
        } catch (error) {
            showError(errorBox, error instanceof Error ? error.message : 'Passkey registration failed.');
            toggleButton(submitButton, false, 'Register passkey in browser');
        }
    });
});

document.querySelectorAll('[data-passkey-login]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const errorBox = form.querySelector('[data-passkey-error]');
        const submitButton = form.querySelector('[data-passkey-submit]');
        const finishUrl = form.dataset.finishUrl;

        hideError(errorBox);
        toggleButton(submitButton, true, 'Preparing sign-in...');

        try {
            if (!window.isSecureContext) {
                throw new Error('Passkey authentication requires HTTPS. Open this app with https://passkeys.test.');
            }

            if (isIpAddress(window.location.hostname)) {
                throw new Error('Passkey authentication cannot run from 127.0.0.1 because browsers require a real relying-party domain. Open this app with https://passkeys.test.');
            }

            if (!window.PublicKeyCredential || !navigator.credentials?.get) {
                throw new Error('This browser does not support passkey authentication.');
            }

            const startResponse = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
            });

            const startPayload = await startResponse.json();

            if (!startResponse.ok) {
                throw new Error(startPayload.message ?? 'Unable to start passkey authentication.');
            }

            const publicKey = normalizeRequestOptions(startPayload.public_key);
            const assertion = await navigator.credentials.get({ publicKey });

            if (!assertion) {
                throw new Error('No assertion was returned by the authenticator.');
            }

            const assertionResponse = assertion.response;
            const finishResponse = await fetch(finishUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                body: JSON.stringify({
                    authenticator_data: bufferToBase64Url(assertionResponse.authenticatorData),
                    client_data_json: bufferToBase64Url(assertionResponse.clientDataJSON),
                    credential_id: bufferToBase64Url(assertion.rawId),
                    origin: window.location.origin,
                    signature: bufferToBase64Url(assertionResponse.signature),
                }),
            });

            const finishPayload = await finishResponse.json();

            if (!finishResponse.ok) {
                throw new Error(finishPayload.message ?? 'Unable to complete passkey authentication.');
            }

            window.location.assign(finishPayload.redirect_to);
        } catch (error) {
            showError(errorBox, error instanceof Error ? error.message : 'Passkey authentication failed.');
            toggleButton(submitButton, false, 'Sign in with passkey');
        }
    });
});

function bufferToBase64Url(buffer) {
    const bytes = new Uint8Array(buffer);
    let binary = '';

    bytes.forEach((byte) => {
        binary += String.fromCharCode(byte);
    });

    return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/g, '');
}

function createUint8Array(value) {
    const normalized = value.replace(/-/g, '+').replace(/_/g, '/');
    const padded = normalized + '='.repeat((4 - (normalized.length % 4 || 4)) % 4);
    const binary = atob(padded);

    return Uint8Array.from(binary, (character) => character.charCodeAt(0));
}

function csrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');

    return token?.getAttribute('content') ?? '';
}

function hideError(errorBox) {
    if (!errorBox) {
        return;
    }

    errorBox.classList.add('hidden');
    errorBox.textContent = '';
}

function isIpAddress(hostname) {
    return /^(\d{1,3}\.){3}\d{1,3}$/.test(hostname) || hostname.includes(':');
}

function normalizeCreationOptions(options) {
    return {
        ...options,
        challenge: createUint8Array(options.challenge),
        excludeCredentials: (options.excludeCredentials ?? []).map((credential) => ({
            ...credential,
            id: createUint8Array(credential.id),
        })),
        user: {
            ...options.user,
            id: createUint8Array(options.user.id),
        },
    };
}

function normalizeRequestOptions(options) {
    return {
        ...options,
        allowCredentials: (options.allowCredentials ?? []).map((credential) => ({
            ...credential,
            id: createUint8Array(credential.id),
        })),
        challenge: createUint8Array(options.challenge),
    };
}

function showError(errorBox, message) {
    if (!errorBox) {
        return;
    }

    errorBox.classList.remove('hidden');
    errorBox.textContent = message;
}

function toggleButton(button, disabled, label) {
    if (!button) {
        return;
    }

    button.disabled = disabled;
    button.textContent = label;
}
