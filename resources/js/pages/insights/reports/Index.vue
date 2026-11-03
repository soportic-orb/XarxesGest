<script setup lang="ts">
import { Head, InfiniteScroll, Link, useForm } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconReportAnalytics,
    IconSparkles,
} from '@tabler/icons-vue';

import EmptyState from '@/components/EmptyState.vue';
import HeaderTitle from '@/components/HeaderTitle.vue';
import { Button } from '@/components/ui/button';
import date from '@/date';
import AppLayout from '@/layouts/AppLayout.vue';
import { insights as insightsRoute } from '@/routes/app';
import { show, store } from '@/routes/app/insights/reports';
import type { MonthlyReportListItem } from '@/types/analytics';

const props = defineProps<{
    reports: { data: MonthlyReportListItem[] };
    hasData: boolean;
    canGenerate: boolean;
    lastMonth: string;
}>();

const form = useForm({ month: props.lastMonth });

const generateLastMonth = (): void => {
    form.post(store.url(), { preserveScroll: true });
};
</script>

<template>
    <AppLayout full-width>
        <Head :title="$t('analytics.reports.title')" />
        <div
            class="mx-auto flex w-full max-w-4xl min-w-0 flex-col gap-6 px-4 pt-6 pb-10 md:px-8"
        >
            <header
                class="flex min-w-0 items-center justify-between gap-4 border-b border-border-strong pb-4"
            >
                <div class="flex min-w-0 flex-col gap-1">
                    <Link
                        :href="insightsRoute.url()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                        data-testid="monthly-reports-back"
                    >
                        ← {{ $t('analytics.title') }}
                    </Link>
                    <HeaderTitle
                        :title="$t('analytics.reports.title')"
                        :icon="IconReportAnalytics"
                    />
                </div>
                <Button
                    v-if="canGenerate"
                    :loading="form.processing"
                    data-testid="monthly-reports-generate"
                    @click="generateLastMonth"
                >
                    <IconSparkles aria-hidden="true" />
                    {{ $t('analytics.reports.generate') }}
                </Button>
            </header>

            <EmptyState
                v-if="!hasData"
                :icon="IconReportAnalytics"
                :title="$t('analytics.reports.empty_title')"
                :description="
                    canGenerate
                        ? $t('analytics.reports.empty_description')
                        : $t('analytics.reports.empty_description_member')
                "
                data-testid="monthly-reports-empty"
            />

            <template v-else>
                <p class="text-sm text-muted-foreground">
                    {{ $t('analytics.reports.description') }}
                </p>
                <InfiniteScroll
                    data="reports"
                    items-element="#monthly-reports-list"
                    preserve-url
                >
                    <ul id="monthly-reports-list" class="flex flex-col gap-2">
                        <li v-for="report in reports.data" :key="report.id">
                            <Link
                                :href="show.url(report.id)"
                                class="flex items-center gap-4 rounded-xl border border-border bg-card p-4 transition-colors hover:bg-accent"
                                :data-testid="`monthly-report-${report.month}`"
                            >
                                <div class="flex min-w-0 flex-1 flex-col gap-1">
                                    <p
                                        class="font-heading text-base font-medium text-foreground capitalize"
                                    >
                                        {{ date.formatMonthName(report.month) }}
                                    </p>
                                    <p
                                        class="line-clamp-2 text-sm text-muted-foreground"
                                    >
                                        {{
                                            report.headline ??
                                            $t('analytics.reports.no_headline')
                                        }}
                                    </p>
                                </div>
                                <div
                                    class="flex shrink-0 flex-col items-end text-sm"
                                >
                                    <span class="font-medium text-foreground">{{
                                        report.posts ?? 0
                                    }}</span>
                                    <span class="text-muted-foreground">{{
                                        $t('analytics.reports.posts')
                                    }}</span>
                                </div>
                                <IconChevronRight
                                    class="size-4 shrink-0 text-muted-foreground rtl:rotate-180"
                                    aria-hidden="true"
                                />
                            </Link>
                        </li>
                    </ul>
                </InfiniteScroll>
            </template>
        </div>
    </AppLayout>
</template>
