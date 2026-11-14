<script setup lang="ts">
import { computed } from 'vue'
import { useData, withBase } from 'vitepress'

// The latest release, read from the changelog by the config, with the
// labels of the current language.
const { theme, lang, localeIndex } = useData()

const latest = computed(() => theme.value.latest as { version: string, date: string })
const labels = computed(() => (theme.value.latestReleaseLabels ?? { label: 'Latest release', whatsNew: 'What\'s new' }) as { label: string, whatsNew: string })

const date = computed(() => new Date(`${latest.value.date}T12:00:00Z`).toLocaleDateString(lang.value, {
    day: 'numeric', month: 'long', year: 'numeric',
}))

const link = computed(() => withBase(localeIndex.value === 'root' ? '/reference/changelog' : `/${localeIndex.value}/reference/changelog`))
</script>

<template>
    <a v-if="latest?.version" class="latest-release" :href="link">
        <span class="latest-release-version">v{{ latest.version }}</span>
        <span class="latest-release-text">
            {{ labels.label }} · {{ date }}
        </span>
        <span class="latest-release-more" aria-hidden="true">{{ labels.whatsNew }} →</span>
    </a>
</template>

<style scoped>
.latest-release {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 4px 14px 4px 4px;
    border: 1px solid var(--vp-c-divider);
    border-radius: 999px;
    font-size: 14px;
    line-height: 24px;
    color: var(--vp-c-text-2);
    text-decoration: none;
    transition: border-color .2s;
}

.latest-release:hover {
    border-color: var(--vp-c-brand-1);
}

.latest-release-version {
    padding: 0 10px;
    border-radius: 999px;
    background: var(--vp-c-brand-soft);
    color: var(--vp-c-brand-1);
    font-weight: 600;
}

.latest-release-more {
    color: var(--vp-c-brand-1);
    font-weight: 500;
}
</style>
