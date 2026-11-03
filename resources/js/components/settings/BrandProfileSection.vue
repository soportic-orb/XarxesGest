<script setup lang="ts">
import { Form } from '@inertiajs/vue3';

import WorkspaceBrandProfileController from '@/actions/App/Http/Controllers/App/WorkspaceBrandProfileController';
import SettingsField from '@/components/settings/SettingsField.vue';
import SettingsSection from '@/components/settings/SettingsSection.vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';

export type BrandProfileField =
    | 'business'
    | 'audience'
    | 'tone'
    | 'topics'
    | 'references';

export type BrandProfile = Partial<Record<BrandProfileField, string>>;

defineProps<{
    profile: BrandProfile | null;
}>();

const FIELDS: BrandProfileField[] = [
    'business',
    'audience',
    'tone',
    'topics',
    'references',
];
</script>

<template>
    <SettingsSection
        :title="$t('settings.workspace.brand_profile.title')"
        :description="$t('settings.workspace.brand_profile.description')"
    >
        <Form
            v-bind="WorkspaceBrandProfileController.update.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
            data-testid="brand-profile-form"
            :options="{ preserveScroll: true }"
        >
            <SettingsField
                v-for="field in FIELDS"
                :key="field"
                :label="$t(`settings.workspace.brand_profile.${field}`)"
                :for="`brand-profile-${field}`"
                :error="errors[field]"
            >
                <Textarea
                    :id="`brand-profile-${field}`"
                    :name="field"
                    rows="2"
                    :data-testid="`brand-profile-${field}`"
                    :default-value="profile?.[field] ?? ''"
                    :placeholder="
                        $t(
                            `settings.workspace.brand_profile.${field}_placeholder`,
                        )
                    "
                />
            </SettingsField>

            <Button
                :loading="processing"
                class="self-start"
                data-testid="brand-profile-save"
            >
                {{ $t('settings.workspace.brand_profile.save') }}
            </Button>
        </Form>
    </SettingsSection>
</template>
