<script setup lang="ts">
import { computed } from 'vue'
import { useData, withBase } from 'vitepress'

// On translated pages: English is the reference, one click away.
const { theme, page, localeIndex } = useData()

const notice = computed(() => theme.value.translationNotice as { text: string, link: string } | undefined)

const english = computed(() => withBase('/' + page.value.relativePath
    .replace(new RegExp(`^${localeIndex.value}/`), '')
    .replace(/(^|\/)index\.md$/, '$1')
    .replace(/\.md$/, '')))
</script>

<template>
    <div v-if="notice" class="translation-notice">
        {{ notice.text }}
        <a :href="english" lang="en" dir="ltr">{{ notice.link }}</a>
    </div>
</template>

<style scoped>
.translation-notice {
    margin-bottom: 24px;
    padding: 8px 14px;
    border: 1px solid var(--vp-c-divider);
    border-radius: 8px;
    font-size: 13px;
    line-height: 20px;
    color: var(--vp-c-text-2);
}

.translation-notice a {
    color: var(--vp-c-brand-1);
    font-weight: 500;
}
</style>
