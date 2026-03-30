<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import { prepareAuthenticationOptions, credentialToJSON } from '@/Utils/PasskeyHelper';
import { FingerPrintIcon } from '@heroicons/vue/24/outline';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const isPasskeySupported = ref(false);
const passkeyError = ref('');

onMounted(async () => {
    isPasskeySupported.value = window.PublicKeyCredential && 
        window.PublicKeyCredential.isConditionalMediationAvailable &&
        await PublicKeyCredential.isConditionalMediationAvailable();
        
    if (isPasskeySupported.value) {
        // Automatically attempt passkey login using Conditional UI (autofill)
        attemptPasskeyLogin(true);
    }
});

const attemptPasskeyLogin = async (conditional = false) => {
    passkeyError.value = '';
    
    try {
        // 1. Get authentication options from server
        const response = await axios.post(route('passkeys.login-options'));
        const options = prepareAuthenticationOptions(response.data);
        
        // 2. Trigger biometric challenge
        const credential = await navigator.credentials.get({
            publicKey: options,
            mediation: conditional ? 'conditional' : 'optional'
        });
        
        if (!credential) return;

        // 3. Submit to server
        useForm({
            publicKeyCredential: credentialToJSON(credential)
        }).post(route('passkeys.login'), {
            onError: (errors) => {
                passkeyError.value = errors.email || 'Биометрический вход не удался.';
            }
        });

    } catch (err) {
        if (!conditional) {
            console.error(err);
            passkeyError.value = 'Не удалось войти с помощью биометрии.';
        }
    }
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <div v-if="passkeyError" class="mb-4 text-sm font-medium text-red-600">
            {{ passkeyError }}
        </div>

        <div v-if="isPasskeySupported" class="mb-6">
            <button
                type="button"
                @click="attemptPasskeyLogin(false)"
                class="w-full flex items-center justify-center px-4 py-3 border border-blue-600 text-blue-600 rounded-xl font-bold hover:bg-blue-50 transition-all duration-200"
            >
                <FingerPrintIcon class="h-5 w-5 mr-2" />
                Вход по Face ID / Fingerprint
            </button>
            <div class="relative flex py-4 items-center">
                <div class="flex-grow border-t border-gray-200"></div>
                <span class="flex-shrink mx-4 text-gray-400 text-xs uppercase tracking-widest">или пароль</span>
                <div class="flex-grow border-t border-gray-200"></div>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
