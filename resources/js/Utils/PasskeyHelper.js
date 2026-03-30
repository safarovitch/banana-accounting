/**
 * Helper to handle WebAuthn Base64URL encoding/decoding.
 * Since we don't have @github/webauthn-json, we implement the core conversion logic here.
 */

export const bufferToBase64URL = (buffer) => {
    const bytes = new Uint8Array(buffer);
    let str = '';
    for (const charCode of bytes) {
        str += String.fromCharCode(charCode);
    }
    return btoa(str)
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
};

export const base64URLToBuffer = (base64url) => {
    const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
    const str = atob(base64);
    const bytes = new Uint8Array(str.length);
    for (let i = 0; i < str.length; i++) {
        bytes[i] = str.charCodeAt(i);
    }
    return bytes.buffer;
};

/**
 * Transforms the challenge and user ID from the server (JSON/Base64URL) 
 * into the format required by navigator.credentials.create / get.
 */
export const prepareRegistrationOptions = (options) => {
    options.challenge = base64URLToBuffer(options.challenge);
    options.user.id = base64URLToBuffer(options.user.id);
    
    if (options.excludeCredentials) {
        options.excludeCredentials = options.excludeCredentials.map(c => ({
            ...c,
            id: base64URLToBuffer(c.id)
        }));
    }
    
    return options;
};

export const prepareAuthenticationOptions = (options) => {
    options.challenge = base64URLToBuffer(options.challenge);
    
    if (options.allowCredentials) {
        options.allowCredentials = options.allowCredentials.map(c => ({
            ...c,
            id: base64URLToBuffer(c.id)
        }));
    }
    
    return options;
};

/**
 * Transforms the credential result from the browser into a JSON-friendly 
 * format for the server.
 */
export const credentialToJSON = (credential) => {
    const json = {
        id: credential.id,
        rawId: bufferToBase64URL(credential.rawId),
        type: credential.type,
        response: {},
        authenticatorAttachment: credential.authenticatorAttachment,
    };

    if (credential.response.attestationObject) {
        json.response.attestationObject = bufferToBase64URL(credential.response.attestationObject);
    }

    if (credential.response.clientDataJSON) {
        json.response.clientDataJSON = bufferToBase64URL(credential.response.clientDataJSON);
    }

    if (credential.response.authenticatorData) {
        json.response.authenticatorData = bufferToBase64URL(credential.response.authenticatorData);
    }

    if (credential.response.signature) {
        json.response.signature = bufferToBase64URL(credential.response.signature);
    }

    if (credential.response.userHandle) {
        json.response.userHandle = bufferToBase64URL(credential.response.userHandle);
    }

    return json;
};
