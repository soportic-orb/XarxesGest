<script setup lang="ts">
import { useHttp } from '@inertiajs/vue3';
import { IconLoader2, IconSparkles } from '@tabler/icons-vue';
import { trans } from 'laravel-vue-i18n';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { extractErrorMessage } from '@/lib/httpError';
import {
    store as generateImage,
    prompt as writePrompt,
} from '@/routes/app/media/ai';
import type { MediaItem, MediaSource, SourceMetaValue } from '@/types/media';

type Aspect = 'square' | 'portrait' | 'landscape';

type GeneratedMedia = MediaItem & {
    meta?: MediaItem['meta'] & {
        source?: MediaSource;
        source_meta?: Record<string, SourceMetaValue>;
    };
};

const ASPECTS: Aspect[] = ['square', 'portrait', 'landscape'];

const props = defineProps<{
    /** Plain text of the post being written, used to draft the description. */
    postContent: string;
}>();

const open = defineModel<boolean>('open', { required: true });

const emit = defineEmits<{
    (event: 'picked', item: MediaItem): void;
}>();

const aspect = ref<Aspect>('square');
const error = ref('');
const generated = ref<GeneratedMedia | null>(null);

const promptHttp = useHttp<
    { content: string; aspect: Aspect },
    { prompt: string }
>({
    content: '',
    aspect: 'square',
});
const imageHttp = useHttp<{ prompt: string; aspect: Aspect }, GeneratedMedia>({
    prompt: '',
    aspect: 'square',
});

const busy = computed(() => promptHttp.processing || imageHttp.processing);
const canWriteFromPost = computed(
    () => !busy.value && props.postContent.trim().length > 0,
);
const canGenerate = computed(
    () => !busy.value && imageHttp.prompt.trim().length >= 3,
);

watch(open, (isOpen) => {
    promptHttp.cancel();
    imageHttp.cancel();

    if (!isOpen) {
        return;
    }

    error.value = '';
    generated.value = null;
    aspect.value = 'square';
    imageHttp.prompt = '';
});

onBeforeUnmount(() => {
    promptHttp.cancel();
    imageHttp.cancel();
});

const failureMessage = (
    http: { errors: Record<string, string> },
    exception: unknown,
    fallbackKey: string,
): string =>
    Object.values(http.errors)[0] ??
    extractErrorMessage(exception) ??
    trans(fallbackKey);

const selectAspect = (value: Aspect): void => {
    aspect.value = value;
};

const writeFromPost = async (): Promise<void> => {
    if (!canWriteFromPost.value) {
        return;
    }

    error.value = '';
    promptHttp.content = props.postContent;
    promptHttp.aspect = aspect.value;

    try {
        const result = await promptHttp.post(writePrompt.url());
        if (result?.prompt) {
            imageHttp.prompt = result.prompt;
        }
    } catch (exception) {
        error.value = failureMessage(
            promptHttp,
            exception,
            'posts.composer.ai_image.errors.prompt_failed',
        );
    }
};

const generate = async (): Promise<void> => {
    if (!canGenerate.value) {
        return;
    }

    error.value = '';
    imageHttp.aspect = aspect.value;

    try {
        const media = await imageHttp.post(generateImage.url());
        if (media) {
            generated.value = media;
        }
    } catch (exception) {
        error.value = failureMessage(
            imageHttp,
            exception,
            'posts.composer.ai_image.errors.generation_failed',
        );
    }
};

const useImage = (): void => {
    const media = generated.value;
    if (!media) {
        return;
    }

    emit('picked', {
        ...media,
        source: media.meta?.source,
        source_meta: media.meta?.source_meta,
    });
    open.value = false;
};
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent
            class="flex max-h-[90vh] flex-col gap-4 overflow-y-auto sm:max-w-xl"
            data-testid="ai-image-dialog"
        >
            <DialogHeader>
                <DialogTitle>{{
                    $t('posts.composer.ai_image.title')
                }}</DialogTitle>
                <DialogDescription>
                    {{ $t('posts.composer.ai_image.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="flex flex-col gap-2">
                <div class="flex items-center justify-between gap-2">
                    <Label for="ai-image-prompt">
                        {{ $t('posts.composer.ai_image.prompt_label') }}
                    </Label>
                    <Button
                        type="button"
                        variant="ghost"
                        size="sm"
                        data-testid="ai-image-write-from-post"
                        :disabled="!canWriteFromPost"
                        @click="writeFromPost"
                    >
                        <IconLoader2
                            v-if="promptHttp.processing"
                            class="size-4 animate-spin"
                        />
                        <IconSparkles v-else class="size-4" />
                        {{ $t('posts.composer.ai_image.write_from_post') }}
                    </Button>
                </div>
                <Textarea
                    id="ai-image-prompt"
                    v-model="imageHttp.prompt"
                    data-testid="ai-image-prompt"
                    rows="4"
                    :disabled="busy"
                    :placeholder="
                        $t('posts.composer.ai_image.prompt_placeholder')
                    "
                />
            </div>

            <div class="flex flex-col gap-2">
                <span class="text-sm font-medium">
                    {{ $t('posts.composer.ai_image.aspect_label') }}
                </span>
                <div
                    class="flex gap-2"
                    role="radiogroup"
                    :aria-label="$t('posts.composer.ai_image.aspect_label')"
                >
                    <Button
                        v-for="option in ASPECTS"
                        :key="option"
                        type="button"
                        size="sm"
                        role="radio"
                        :aria-checked="aspect === option"
                        :variant="aspect === option ? 'default' : 'outline'"
                        :data-testid="`ai-image-aspect-${option}`"
                        :disabled="busy"
                        @click="selectAspect(option)"
                    >
                        {{ $t(`posts.composer.ai_image.aspects.${option}`) }}
                    </Button>
                </div>
            </div>

            <p
                v-if="imageHttp.processing"
                class="flex items-center gap-2 text-sm text-muted-foreground"
                data-testid="ai-image-generating"
            >
                <IconLoader2 class="size-4 animate-spin" />
                {{ $t('posts.composer.ai_image.generating') }}
            </p>

            <img
                v-if="generated && !imageHttp.processing"
                :src="generated.url"
                :alt="$t('posts.composer.ai_image.preview_alt')"
                class="max-h-80 w-full rounded-lg bg-muted object-contain"
                data-testid="ai-image-preview"
            />

            <p
                v-if="error"
                class="text-sm text-destructive"
                role="alert"
                data-testid="ai-image-error"
            >
                {{ error }}
            </p>

            <DialogFooter>
                <Button
                    v-if="generated"
                    type="button"
                    variant="outline"
                    data-testid="ai-image-regenerate"
                    :loading="imageHttp.processing"
                    :disabled="!canGenerate"
                    @click="generate"
                >
                    {{ $t('posts.composer.ai_image.regenerate') }}
                </Button>
                <Button
                    v-if="generated"
                    type="button"
                    data-testid="ai-image-use"
                    :disabled="busy"
                    @click="useImage"
                >
                    {{ $t('posts.composer.ai_image.use_image') }}
                </Button>
                <Button
                    v-else
                    type="button"
                    data-testid="ai-image-generate"
                    :loading="imageHttp.processing"
                    :disabled="!canGenerate"
                    @click="generate"
                >
                    {{ $t('posts.composer.ai_image.generate') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
