<script setup lang="ts">
import { Link, useHttp } from '@inertiajs/vue3';
import { IconTrendingUp } from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { ref } from 'vue';
import { toast } from 'vue-sonner';

import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useWorkspaceAbilities } from '@/composables/useWorkspaceAbilities';
import { extractErrorMessage } from '@/lib/httpError';
import { store as proposeFromTrends } from '@/routes/app/create/ideas/proposals';
import { settings as workspaceSettings } from '@/routes/app/workspace';

const props = defineProps<{
    hasBrandProfile: boolean;
}>();

const { canManageWorkspace } = useWorkspaceAbilities();

const http = useHttp<Record<string, never>, { message: string }>({});
const missingProfileOpen = ref(false);

const propose = async (): Promise<void> => {
    if (!props.hasBrandProfile) {
        missingProfileOpen.value = true;

        return;
    }

    try {
        const response = await http.post(proposeFromTrends.url());
        toast.success(
            response?.message ?? trans('create.ideas.proposals.queued'),
        );
    } catch (exception) {
        toast.error(
            extractErrorMessage(exception) ??
                trans('create.ideas.proposals.errors.failed'),
        );
    }
};

const closeMissingProfile = (): void => {
    missingProfileOpen.value = false;
};
</script>

<template>
    <Popover v-model:open="missingProfileOpen">
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                class="max-sm:w-8 max-sm:px-0"
                data-testid="ideas-propose-trends"
                :aria-label="$t('create.ideas.proposals.button')"
                :loading="http.processing"
                @click.prevent="propose"
            >
                <IconTrendingUp class="size-4" />
                <span class="max-sm:sr-only">
                    {{ $t('create.ideas.proposals.button') }}
                </span>
            </Button>
        </PopoverTrigger>
        <PopoverContent
            align="end"
            class="flex w-72 flex-col gap-3"
            data-testid="ideas-propose-missing-profile"
        >
            <p class="text-sm">
                {{
                    canManageWorkspace
                        ? $t('create.ideas.proposals.missing_profile')
                        : $t('create.ideas.proposals.missing_profile_member')
                }}
            </p>
            <Button
                v-if="canManageWorkspace"
                as-child
                size="sm"
                class="self-start"
            >
                <Link :href="workspaceSettings()" @click="closeMissingProfile">
                    {{ $t('create.ideas.proposals.open_settings') }}
                </Link>
            </Button>
        </PopoverContent>
    </Popover>
</template>
