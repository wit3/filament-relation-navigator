import { h } from 'vue'
import DefaultTheme from 'vitepress/theme'
import LatestRelease from './LatestRelease.vue'
import TranslationNotice from './TranslationNotice.vue'
import './style.css'

export default {
    extends: DefaultTheme,
    Layout: () => h(DefaultTheme.Layout, null, {
        'home-hero-info-before': () => h(LatestRelease),
        'doc-before': () => h(TranslationNotice),
    }),
}
