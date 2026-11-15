import { h, nextTick, onMounted, watch } from 'vue'
import { useRoute } from 'vitepress'
import DefaultTheme from 'vitepress/theme'
import LatestRelease from './LatestRelease.vue'
import TranslationNotice from './TranslationNotice.vue'
import './style.css'

// On phones, tables read better as one card per row (see style.css): those
// of three columns or more, and those of two that do not fit the screen.
// Each cell gets the name of its column.
const phone = '(max-width: 639px)'

function stackTables(): void {
    document.querySelectorAll<HTMLTableElement>('.vp-doc table:not(.stacked)').forEach((table) => {
        const headings = [...table.querySelectorAll('thead th')].map((th) => th.textContent?.trim() ?? '')
        const overflows = window.matchMedia(phone).matches && table.scrollWidth > table.clientWidth + 2

        if ((headings.length < 3 && ! overflows) || table.closest('.home-section')) {
            return
        }

        table.classList.add('stacked')
        table.querySelectorAll('tbody tr').forEach((row) => {
            [...row.children].forEach((cell, index) => cell.setAttribute('data-label', headings[index] ?? ''))
        })
    })
}

export default {
    extends: DefaultTheme,
    setup() {
        const route = useRoute()

        onMounted(() => {
            stackTables()
            window.matchMedia(phone).addEventListener('change', stackTables)
        })
        watch(() => route.path, () => nextTick(stackTables))
    },
    Layout: () => h(DefaultTheme.Layout, null, {
        'home-hero-info-before': () => h(LatestRelease),
        'doc-before': () => h(TranslationNotice),
    }),
}
