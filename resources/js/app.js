document.querySelectorAll('[data-passkey-register]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const errorBox = form.querySelector('[data-passkey-error]');
        const submitButton = form.querySelector('[data-passkey-submit]');
        const finishUrl = form.dataset.finishUrl;

        hideError(errorBox);
        toggleButton(submitButton, true, 'Preparing passkey request...');

        try {
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
