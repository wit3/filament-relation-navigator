import { defineConfig } from 'vitepress'
import llmstxt from 'vitepress-plugin-llms'

const repository = 'https://github.com/wit3/filament-relation-navigator'

export default defineConfig({
    title: 'Relation Navigator',
    titleTemplate: ':title · Relation Navigator for Filament',
    description: 'A real page for every Filament relation manager and a searchable menu in the breadcrumb. Filament v4 & v5.',
    lang: 'en-US',
    base: '/filament-relation-navigator/',
    cleanUrls: true,
    lastUpdated: true,
    head: [
        ['meta', { property: 'og:image', content: 'https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/social-preview.jpg' }],
        ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ],
    // llms.txt and llms-full.txt for AI agents, plus a .md version of each page.
    vite: {
        plugins: [llmstxt({
            title: 'Relation Navigator for Filament',
            description: 'A real page for every Filament relation manager (/customers/1/orders) and a searchable menu in the breadcrumb. Filament v4 and v5. Package: wit3/filament-relation-navigator.',
        })],
    },
    markdown: {
        theme: { light: 'github-light', dark: 'github-dark' },
    },
    themeConfig: {
        nav: [
            { text: 'Guide', link: '/guide/installation' },
            { text: 'Configuration', link: '/reference/configuration' },
            { text: 'Changelog', link: '/reference/changelog' },
            { text: 'Filament plugin', link: 'https://filamentphp.com/plugins/wit3-relation-navigator' },
        ],
        sidebar: [
            {
                text: 'Getting started',
                items: [
                    { text: 'Introduction', link: '/guide/introduction' },
                    { text: 'Installation', link: '/guide/installation' },
                    { text: 'Quick start', link: '/guide/quick-start' },
                ],
            },
            {
                text: 'Relation pages',
                items: [
                    { text: 'Relation pages', link: '/guide/relation-pages' },
                    { text: 'Header, actions and widgets', link: '/guide/header-actions-and-widgets' },
                    { text: 'Customising the pages', link: '/guide/customising-the-relation-pages' },
                ],
            },
            {
                text: 'Navigation',
                items: [
                    { text: 'The breadcrumb menu', link: '/guide/the-breadcrumb-menu' },
                    { text: 'Menu presets and options', link: '/guide/menu-presets' },
                    { text: 'Open as page', link: '/guide/open-as-page' },
                    { text: 'Previous and next record', link: '/guide/previous-and-next-record' },
                    { text: 'Links to relation manager tabs', link: '/guide/links-to-relation-manager-tabs' },
                    { text: 'Native record sub-navigation', link: '/guide/native-record-sub-navigation' },
                ],
            },
            {
                text: 'Reference',
                items: [
                    { text: 'Configuration', link: '/reference/configuration' },
                    { text: 'How it works', link: '/reference/how-it-works' },
                    { text: 'AI agents', link: '/reference/ai-agents' },
                    { text: 'Upgrade guide', link: '/reference/upgrade' },
                    { text: 'Changelog', link: '/reference/changelog' },
                    { text: 'Contributing', link: '/reference/contributing' },
                ],
            },
        ],
        search: { provider: 'local' },
        outline: { level: [2, 3] },
        socialLinks: [{ icon: 'github', link: repository }],
        editLink: {
            pattern: `${repository}/edit/main/README.md`,
            text: 'Edit this page on GitHub (README.md)',
        },
        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © Salvatore Scalzi',
        },
    },
})
