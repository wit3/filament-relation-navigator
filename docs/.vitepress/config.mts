import { defineConfig, type DefaultTheme } from 'vitepress'
import llmstxt from 'vitepress-plugin-llms'

const repository = 'https://github.com/wit3/filament-relation-navigator'
const plugin = 'https://filamentphp.com/plugins/wit3-relation-navigator'

function sidebar(prefix: string, t: Record<string, string>): DefaultTheme.SidebarItem[] {
    const link = (path: string) => `${prefix}${path}`

    return [
        {
            text: t.gettingStarted,
            items: [
                { text: t.introduction, link: link('/guide/introduction') },
                { text: t.installation, link: link('/guide/installation') },
                { text: t.quickStart, link: link('/guide/quick-start') },
            ],
        },
        {
            text: t.relationPages,
            items: [
                { text: t.relationPages, link: link('/guide/relation-pages') },
                { text: t.header, link: link('/guide/header-actions-and-widgets') },
                { text: t.customising, link: link('/guide/customising-the-relation-pages') },
            ],
        },
        {
            text: t.navigation,
            items: [
                { text: t.menu, link: link('/guide/the-breadcrumb-menu') },
                { text: t.presets, link: link('/guide/menu-presets') },
                { text: t.openAsPage, link: link('/guide/open-as-page') },
                { text: t.previousNext, link: link('/guide/previous-and-next-record') },
                { text: t.tabs, link: link('/guide/links-to-relation-manager-tabs') },
                { text: t.globalSearch, link: link('/guide/global-search') },
                { text: t.subNavigation, link: link('/guide/native-record-sub-navigation') },
            ],
        },
        {
            text: t.reference,
            items: [
                { text: t.configuration, link: link('/reference/configuration') },
                { text: t.howItWorks, link: link('/reference/how-it-works') },
                { text: t.ai, link: link('/reference/ai-agents') },
                { text: t.upgrade, link: link('/reference/upgrade') },
                { text: t.changelog, link: link('/reference/changelog') },
                { text: t.contributing, link: link('/reference/contributing') },
            ],
        },
    ]
}

const en = {
    gettingStarted: 'Getting started', introduction: 'Introduction', installation: 'Installation', quickStart: 'Quick start',
    relationPages: 'Relation pages', header: 'Header, actions and widgets', customising: 'Customising the pages',
    navigation: 'Navigation', menu: 'The breadcrumb menu', presets: 'Menu presets and options', openAsPage: 'Open as page',
    previousNext: 'Previous and next record', tabs: 'Links to relation manager tabs', globalSearch: 'Global search',
    subNavigation: 'Native record sub-navigation', reference: 'Reference', configuration: 'Configuration',
    howItWorks: 'How it works', ai: 'AI agents', upgrade: 'Upgrade guide', changelog: 'Changelog', contributing: 'Contributing',
}

const it = {
    gettingStarted: 'Per iniziare', introduction: 'Introduzione', installation: 'Installazione', quickStart: 'Avvio rapido',
    relationPages: 'Pagine delle relazioni', header: 'Testata, azioni e widget', customising: 'Personalizzare le pagine',
    navigation: 'Navigazione', menu: 'Il menu nella breadcrumb', presets: 'Preset e opzioni del menu', openAsPage: 'Apri come pagina',
    previousNext: 'Record precedente e successivo', tabs: 'Link alle schede', globalSearch: 'Ricerca globale',
    subNavigation: 'Sotto-navigazione nativa', reference: 'Riferimenti', configuration: 'Configurazione',
    howItWorks: 'Come funziona', ai: 'Agenti AI', upgrade: 'Guida all\'aggiornamento', changelog: 'Changelog', contributing: 'Contribuire',
}

export default defineConfig({
    title: 'Relation Navigator',
    titleTemplate: ':title · Relation Navigator for Filament',
    description: 'A real page for every Filament relation manager and a searchable menu in the breadcrumb. Filament v4 & v5.',
    base: '/filament-relation-navigator/',
    cleanUrls: true,
    lastUpdated: true,
    head: [
        ['meta', { property: 'og:image', content: 'https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/social-preview.jpg' }],
        ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
    ],
    // llms.txt and llms-full.txt for AI agents, plus a .md version of each
    // page. English only: the Italian pages would double them.
    vite: {
        plugins: [llmstxt({
            title: 'Relation Navigator for Filament',
            description: 'A real page for every Filament relation manager (/customers/1/orders) and a searchable menu in the breadcrumb. Filament v4 and v5. Package: wit3/filament-relation-navigator.',
            ignoreFiles: ['it/**'],
        })],
    },
    markdown: {
        theme: { light: 'github-light', dark: 'github-dark' },
    },
    locales: {
        root: {
            label: 'English',
            lang: 'en-US',
            themeConfig: {
                nav: [
                    { text: 'Guide', link: '/guide/installation' },
                    { text: 'Configuration', link: '/reference/configuration' },
                    { text: 'Changelog', link: '/reference/changelog' },
                    { text: 'Filament plugin', link: plugin },
                ],
                sidebar: sidebar('', en),
                editLink: {
                    pattern: `${repository}/edit/main/README.md`,
                    text: 'Edit this page on GitHub (README.md)',
                },
            },
        },
        it: {
            label: 'Italiano',
            lang: 'it-IT',
            link: '/it/',
            title: 'Relation Navigator',
            titleTemplate: ':title · Relation Navigator per Filament',
            description: 'Una pagina vera per ogni relation manager di Filament e un menu con ricerca nella breadcrumb. Filament v4 e v5.',
            themeConfig: {
                nav: [
                    { text: 'Guida', link: '/it/guide/installation' },
                    { text: 'Configurazione', link: '/it/reference/configuration' },
                    { text: 'Changelog', link: '/it/reference/changelog' },
                    { text: 'Plugin su Filament', link: plugin },
                ],
                sidebar: sidebar('/it', it),
                editLink: {
                    pattern: `${repository}/edit/main/README.it.md`,
                    text: 'Modifica questa pagina su GitHub (README.it.md)',
                },
                outline: { level: [2, 3], label: 'In questa pagina' },
                docFooter: { prev: 'Pagina precedente', next: 'Pagina successiva' },
                lastUpdated: { text: 'Aggiornato il' },
                darkModeSwitchLabel: 'Aspetto',
                lightModeSwitchTitle: 'Passa al tema chiaro',
                darkModeSwitchTitle: 'Passa al tema scuro',
                sidebarMenuLabel: 'Menu',
                returnToTopLabel: 'Torna su',
                langMenuLabel: 'Cambia lingua',
                footer: {
                    message: 'Rilasciato con licenza MIT.',
                    copyright: 'Copyright © Salvatore Scalzi',
                },
            },
        },
    },
    themeConfig: {
        search: {
            provider: 'local',
            options: {
                locales: {
                    it: {
                        translations: {
                            button: { buttonText: 'Cerca', buttonAriaLabel: 'Cerca' },
                            modal: {
                                displayDetails: 'Mostra i dettagli',
                                resetButtonTitle: 'Cancella la ricerca',
                                backButtonTitle: 'Chiudi la ricerca',
                                noResultsText: 'Nessun risultato per',
                                footer: { selectText: 'per aprire', navigateText: 'per spostarti', closeText: 'per chiudere' },
                            },
                        },
                    },
                },
            },
        },
        outline: { level: [2, 3] },
        socialLinks: [{ icon: 'github', link: repository }],
        footer: {
            message: 'Released under the MIT License.',
            copyright: 'Copyright © Salvatore Scalzi',
        },
    },
})
