<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
    IconFileTypePdf,
    IconRefresh,
    IconReportAnalytics,
} from '@tabler/icons-vue';

import HeaderTitle from '@/components/HeaderTitle.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { formatFullNumber } from '@/lib/utils';
import { download, index, store } from '@/routes/app/insights/reports';
import type { MonthlyReportDetail } from '@/types/analytics';

const props = defineProps<{
    report: MonthlyReportDetail;
    canGenerate: boolean;
}>();

const form = useForm({ month: props.report.month });

const regenerate = (): void => {
    form.post(store.url(), { preserveScroll: true });
};

const formatCell = (cell: string | number | null): string =>
    typeof cell === 'number' ? formatFullNumber(cell) : (cell ?? '—');
</script>

<template>
    <AppLayout full-width>
        <Head :title="report.document.title" />
        <div
            class="mx-auto flex w-full max-w-5xl min-w-0 flex-col gap-6 px-4 pt-6 pb-10 md:px-8"
        >
            <header
                class="flex min-w-0 flex-wrap items-center justify-between gap-4 border-b border-border-strong pb-4"
            >
                <div class="flex min-w-0 flex-col gap-1">
                    <Link
                        :href="index.url()"
                        class="text-sm text-muted-foreground hover:text-foreground"
                        data-testid="monthly-report-back"
                    >
                        ← {{ $t('analytics.reports.back') }}
                    </Link>
                    <HeaderTitle
                        :title="report.document.title"
                        :icon="IconReportAnalytics"
                    />
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    <Button
                        v-if="canGenerate"
                        variant="outline"
                        :loading="form.processing"
                        data-testid="monthly-report-regenerate"
                        @click="regenerate"
                    >
                        <IconRefresh aria-hidden="true" />
                        {{ $t('analytics.reports.regenerate') }}
                    </Button>
                    <Button as-child>
                        <a
                            :href="download.url(report.id)"
                            download
                            data-testid="monthly-report-pdf"
                        >
                            <IconFileTypePdf aria-hidden="true" />
                            {{ $t('analytics.reports.download_pdf') }}
                        </a>
                    </Button>
                </div>
            </header>

            <dl
                class="grid grid-cols-1 gap-x-6 gap-y-1 text-sm sm:grid-cols-2"
                data-testid="monthly-report-meta"
            >
                <div
                    v-for="[label, value] in report.document.meta"
                    :key="label"
                    class="flex gap-2"
                >
                    <dt class="text-muted-foreground">{{ label }}</dt>
                    <dd class="text-foreground">{{ value }}</dd>
                </div>
            </dl>

            <section
                v-if="report.summary"
                class="flex flex-col gap-4 rounded-xl border border-border bg-muted/40 p-5"
                data-testid="monthly-report-summary"
            >
                <p class="font-heading text-lg font-medium text-foreground">
                    {{ report.summary.headline }}
                </p>
                <div
                    v-if="report.summary.highlights.length"
                    class="flex flex-col gap-1"
                >
                    <h2 class="text-sm font-medium text-foreground">
                        {{ $t('analytics.reports.highlights') }}
                    </h2>
                    <ul class="list-disc ps-5 text-sm text-muted-foreground">
                        <li
                            v-for="highlight in report.summary.highlights"
                            :key="highlight"
                        >
                            {{ highlight }}
                        </li>
                    </ul>
                </div>
                <div
                    v-if="report.summary.recommendations.length"
                    class="flex flex-col gap-1"
                >
                    <h2 class="text-sm font-medium text-foreground">
                        {{ $t('analytics.reports.recommendations') }}
                    </h2>
                    <ul class="list-disc ps-5 text-sm text-muted-foreground">
                        <li
                            v-for="recommendation in report.summary
                                .recommendations"
                            :key="recommendation"
                        >
                            {{ recommendation }}
                        </li>
                    </ul>
                </div>
            </section>
            <p
                v-else
                class="rounded-xl border border-dashed border-border p-4 text-sm text-muted-foreground"
                data-testid="monthly-report-no-summary"
            >
                {{ $t('analytics.reports.no_summary') }}
            </p>

            <section
                v-for="section in report.document.sections"
                :key="section.title"
                class="flex min-w-0 flex-col gap-2"
            >
                <h2 class="font-heading text-base font-medium text-foreground">
                    {{ section.title }}
                </h2>
                <p
                    v-if="section.rows.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    {{ $t('analytics.reports.no_rows') }}
                </p>
                <div
                    v-else
                    class="overflow-x-auto rounded-xl border border-border"
                >
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead
                                    v-for="header in section.headers"
                                    :key="header"
                                    class="whitespace-nowrap"
                                >
                                    {{ header }}
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="(row, rowIndex) in section.rows"
                                :key="rowIndex"
                            >
                                <TableCell
                                    v-for="(cell, cellIndex) in row"
                                    :key="cellIndex"
                                    :class="
                                        typeof cell === 'number'
                                            ? 'text-end whitespace-nowrap tabular-nums'
                                            : 'max-w-md'
                                    "
                                >
                                    {{ formatCell(cell) }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
