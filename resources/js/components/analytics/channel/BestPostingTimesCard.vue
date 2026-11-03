<script setup lang="ts">
import { computed } from 'vue';

import AnalyticsSection from '@/components/analytics/workspace/AnalyticsSection.vue';
import { Skeleton } from '@/components/ui/skeleton';
import date from '@/date';
import dayjs from '@/dayjs';
import { activeLocale } from '@/language';
import { orderedWeekdays } from '@/preferences';
import type { BestPostingTimes } from '@/types/analytics';

const props = defineProps<{
    /** Undefined while the deferred prop loads. */
    bestTimes?: BestPostingTimes;
}>();

const HOURS = Array.from({ length: 24 }, (_, hour) => hour);
const TOP_WINDOWS = 3;

const days = computed(() => orderedWeekdays());

const weekdayName = (day: number, format: 'dd' | 'dddd'): string =>
    dayjs().locale(activeLocale.value.toLowerCase()).day(day).format(format);

const hourLabel = (hour: number): string =>
    date.formatClockTime(`${String(hour).padStart(2, '0')}:00`);

const topWindows = computed(() =>
    (props.bestTimes?.windows ?? []).slice(0, TOP_WINDOWS),
);

const cellStyle = (score: number | null): Record<string, string> =>
    score === null ? {} : { opacity: String(0.15 + score * 0.85) };
</script>

<template>
    <AnalyticsSection
        :title="$t('analytics.channel.best_times.title')"
        :info="$t('analytics.channel.best_times.info')"
        info-testid="best-times-info"
        :subtitle="
            bestTimes?.reliable
                ? $t('analytics.channel.best_times.based_on', {
                      count: String(bestTimes.sample_size),
                  })
                : undefined
        "
    >
        <div class="px-4 pb-4 sm:px-5" data-testid="best-times">
            <Skeleton v-if="!bestTimes" class="h-40 w-full" />

            <p
                v-else-if="!bestTimes.reliable"
                class="text-sm text-muted-foreground"
                data-testid="best-times-not-enough"
            >
                {{
                    $t('analytics.channel.best_times.not_enough', {
                        count: String(bestTimes.sample_size),
                        minimum: String(bestTimes.minimum_sample),
                    })
                }}
            </p>

            <template v-else>
                <ol
                    v-if="topWindows.length"
                    class="mb-4 flex flex-wrap gap-2"
                    data-testid="best-times-top"
                >
                    <li
                        v-for="window in topWindows"
                        :key="`${window.day}-${window.hour}`"
                        class="rounded-full bg-accent px-3 py-1 text-sm font-medium"
                    >
                        {{ weekdayName(window.day, 'dddd') }} ·
                        {{ hourLabel(window.hour) }}
                    </li>
                </ol>

                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[36rem] border-separate border-spacing-0.5 text-xs"
                        data-testid="best-times-grid"
                    >
                        <thead>
                            <tr>
                                <th class="w-10" />
                                <th
                                    v-for="hour in HOURS"
                                    :key="hour"
                                    class="font-normal text-muted-foreground"
                                    scope="col"
                                >
                                    <span v-if="hour % 3 === 0">{{
                                        hour
                                    }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="day in days" :key="day">
                                <th
                                    class="pe-1 text-start font-normal text-muted-foreground"
                                    scope="row"
                                >
                                    {{ weekdayName(day, 'dd') }}
                                </th>
                                <td
                                    v-for="hour in HOURS"
                                    :key="hour"
                                    class="h-6 rounded-sm bg-muted p-0"
                                >
                                    <div
                                        v-if="
                                            bestTimes.grid[day]?.[hour]
                                                ?.score !== null &&
                                            bestTimes.grid[day]?.[hour]
                                                ?.score !== undefined
                                        "
                                        class="size-full rounded-sm bg-primary"
                                        :style="
                                            cellStyle(
                                                bestTimes.grid[day][hour].score,
                                            )
                                        "
                                        :title="
                                            $t(
                                                'analytics.channel.best_times.cell',
                                                {
                                                    day: weekdayName(
                                                        day,
                                                        'dddd',
                                                    ),
                                                    time: hourLabel(hour),
                                                    count: String(
                                                        bestTimes.grid[day][
                                                            hour
                                                        ].count,
                                                    ),
                                                },
                                            )
                                        "
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-muted-foreground">
                    {{
                        $t('analytics.channel.best_times.timezone', {
                            timezone: bestTimes.timezone,
                        })
                    }}
                </p>
            </template>
        </div>
    </AnalyticsSection>
</template>
