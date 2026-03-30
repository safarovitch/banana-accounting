<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { 
    prepareRegistrationOptions, 
    credentialToJSON, 
    base64URLToBuffer 
} from '@/Utils/PasskeyHelper';
import { FingerPrintIcon, TrashIcon, DevicePhoneMobileIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    passkeys: Array,
});

const isSupported = ref(false);
const registrationError = ref('');
const deviceName = ref('');

onMounted(() => {
    isSupported.value = window.PublicKeyCredential && 
        PublicKeyCredential.isUserVerifyingPlatformAuthenticatorAvailable &&
        PublicKeyCredential.isConditionalMediationAvailable;
});

const addPasskey = async () => {
    registrationError.value = '';
    
    try {
        // 1. Fetch registration options from server
        const response = await axios.post(route('passkeys.register-options'));
        const options = prepareRegistrationOptions(response.data);
        
        // 2. Trigger browser's biometric prompt
        const credential = await navigator.credentials.create({
            publicKey: options
        });
        
        // 3. Send the public key to the server
        const name = deviceName.value || 'Мое устройство';
        
        useForm({
            name: name,
            publicKeyCredential: credentialToJSON(credential)
        }).post(route('passkeys.register'), {
            preserveScroll: true,
            onSuccess: () => {
                deviceName.value = '';
            },
            onError: (errors) => {
                registrationError.value = 'Ошибка при сохранении ключа на сервере.';
            }
        });
        
    } catch (err) {
        console.error(err);
        registrationError.value = 'Не удалось создать Passkey. Убедитесь, что ваше устройство поддерживает биометрию.';
    }
};

const deletePasskey = (id) => {
    if (confirm('Вы уверены, что хотите удалить этот Passkey?')) {
        useForm({}).delete(route('passkeys.destroy', id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-medium text-gray-900">Биометрическая аутентификация (Passkeys)</h2>
            <p class="mt-1 text-sm text-gray-600">
                Используйте Face ID, Touch ID или отпечаток пальца для быстрого и безопасного входа без пароля.
            </p>
        </header>

        <div v-if="!isSupported" class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-800 text-sm">
            Ваш браузер или устройство не поддерживают Passkeys.
        </div>

        <div v-else class="space-y-6">
            <!-- Registered Passkeys List -->
            <div v-if="passkeys.length > 0" class="bg-gray-50 border border-gray-100 rounded-2xl overflow-hidden">
                <ul class="divide-y divide-gray-200">
                    <li v-for="key in passkeys" :key="key.id" class="px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                <DevicePhoneMobileIcon class="h-5 w-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ key.name }}</p>
                                <p class="text-xs text-gray-500">Добавлен {{ key.created_at }}</p>
                            </div>
                        </div>
                        <button 
                            @click="deletePasskey(key.id)"
                            class="text-gray-400 hover:text-red-500 transition-colors p-2"
                            title="Удалить"
                        >
                            <TrashIcon class="h-5 w-5" />
                        </button>
                    </li>
                </ul>
            </div>

            <!-- Add New Passkey Form -->
            <div class="max-w-xl p-6 border border-gray-200 rounded-2xl bg-white shadow-sm">
                <h3 class="text-md font-bold text-gray-900 flex items-center mb-4">
                    <FingerPrintIcon class="h-5 w-5 mr-2 text-blue-600" />
                    Добавить новое устройство
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <InputLabel for="device_name" value="Название устройства (например, 'Мой iPhone')" />
                        <TextInput
                            id="device_name"
                            v-model="deviceName"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Название устройства..."
                        />
                    </div>

                    <PrimaryButton @click="addPasskey" class="w-full justify-center py-3">
                        Зарегистрировать биометрию
                    </PrimaryButton>
                    
                    <p v-if="registrationError" class="text-sm text-red-600 text-center">{{ registrationError }}</p>
                </div>
            </div>
        </div>
    </section>
</template>
