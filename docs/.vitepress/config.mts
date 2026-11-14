import { readFileSync } from 'node:fs'
import { defineConfig, type DefaultTheme } from 'vitepress'
import llmstxt from 'vitepress-plugin-llms'
import { advisoriesFor } from '../scripts/advisories.mjs'
import { latestRelease, olderMinorVersions } from '../scripts/versions.mjs'
import { translatedLocales, type TranslatedLocale } from './locales.mts'

const repository = 'https://github.com/wit3/filament-relation-navigator'
const plugin = 'https://filamentphp.com/plugins/wit3-relation-navigator'
const packagist = 'https://packagist.org/packages/wit3/filament-relation-navigator'
const site = 'https://wit3.github.io/filament-relation-navigator/'

// The latest release, from the changelog: its version, date and summary.
const latest = latestRelease()

// Older minor versions, each with a site of its own under /vX.Y/ (built by
// scripts/build-versions.mjs from the git tags).
const olderVersions = olderMinorVersions()

// The banner (security issues of this version): shared with older versions.
const bannerData = { version: latest.version, latest: latest.version, latestUrl: site, isLatest: true, advisories: advisoriesFor(latest.version) }
const bannerScript = readFileSync(new URL('../scripts/banner.js', import.meta.url), 'utf8')

function versionMenu(prefix: string, t: Record<string, string>): DefaultTheme.NavItemWithChildren {
    return {
        text: `v${latest.version}`,
        items: [
            {
                items: [
                    { text: t.changelog, link: `${prefix}/reference/changelog` },
                    { text: t.upgrade, link: `${prefix}/reference/upgrade` },
                    { text: t.releases, link: `${repository}/releases` },
                    { text: 'Packagist', link: packagist },
                ],
            },
            ...(olderVersions.length ? [{
                text: t.olderVersions,
                items: olderVersions.map(({ minor, version }) => ({ text: `v${minor} (${version})`, link: `${site}v${minor}/`, target: '_self' })),
            }] : []),
        ],
    }
}

function communityMenu(t: Record<string, string>): DefaultTheme.NavItemWithChildren {
    return {
        text: t.community,
        items: [
            { text: t.discussions, link: `${repository}/discussions` },
            { text: t.issues, link: `${repository}/issues` },
            { text: t.security, link: `${repository}/security` },
            { text: t.filament, link: plugin },
        ],
    }
}

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
            text: t.help,
            items: [
                { text: t.recipes, link: link('/guide/recipes') },
                { text: t.troubleshooting, link: link('/guide/troubleshooting') },
            ],
        },
        {
            text: t.reference,
            items: [
                { text: t.configuration, link: link('/reference/configuration') },
                { text: t.api, link: link('/reference/api') },
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
    api: 'API reference', help: 'Help', recipes: 'Recipes', troubleshooting: 'Troubleshooting',
    releases: 'GitHub releases', community: 'Community', discussions: 'Questions and ideas', issues: 'Bug reports',
    security: 'Report a vulnerability', filament: 'Filament plugin page', olderVersions: 'Older versions',
}

const it = {
    gettingStarted: 'Per iniziare', introduction: 'Introduzione', installation: 'Installazione', quickStart: 'Avvio rapido',
    relationPages: 'Pagine delle relazioni', header: 'Testata, azioni e widget', customising: 'Personalizzare le pagine',
    navigation: 'Navigazione', menu: 'Il menu nella breadcrumb', presets: 'Preset e opzioni del menu', openAsPage: 'Apri come pagina',
    previousNext: 'Record precedente e successivo', tabs: 'Link alle schede', globalSearch: 'Ricerca globale',
    subNavigation: 'Sotto-navigazione nativa', reference: 'Riferimenti', configuration: 'Configurazione',
    howItWorks: 'Come funziona', ai: 'Agenti AI', upgrade: 'Guida all\'aggiornamento', changelog: 'Changelog', contributing: 'Contribuire',
    api: 'Riferimento API', help: 'Aiuto', recipes: 'Ricette', troubleshooting: 'Problemi comuni',
    releases: 'Release su GitHub', community: 'Community', discussions: 'Domande e idee', issues: 'Segnalare un bug',
    security: 'Segnalare una vulnerabilità', filament: 'Pagina del plugin su Filament', olderVersions: 'Versioni precedenti',
}

// A locale translated from English (docs/<code>/, README.<code>.md): its
// strings are in .vitepress/locales/<code>.json.
function translatedLocale({ code, label, lang, dir, strings: s }: TranslatedLocale) {
    return {
        label,
        lang,
        dir,
        link: `/${code}/`,
        title: 'Relation Navigator',
        titleTemplate: `:title · ${s.titleTemplate}`,
        description: s.description,
        themeConfig: {
            nav: [
                { text: s.nav.guide, link: `/${code}/guide/quick-start` },
                { text: s.nav.configuration, link: `/${code}/reference/configuration` },
                { text: s.nav.api, link: `/${code}/reference/api` },
                communityMenu(s.t),
                versionMenu(`/${code}`, s.t),
            ],
            sidebar: sidebar(`/${code}`, s.t),
            editLink: { pattern: `${repository}/edit/main/README.${code}.md`, text: s.editLink },
            outline: { level: [2, 3] as [number, number], label: s.outline },
            docFooter: { prev: s.docFooterPrev, next: s.docFooterNext },
            lastUpdated: { text: s.lastUpdated },
            darkModeSwitchLabel: s.darkModeSwitchLabel,
            lightModeSwitchTitle: s.lightModeSwitchTitle,
            darkModeSwitchTitle: s.darkModeSwitchTitle,
            sidebarMenuLabel: s.sidebarMenuLabel,
            returnToTopLabel: s.returnToTopLabel,
            langMenuLabel: s.langMenuLabel,
            footer: {
                message: s.footer.replace('{version}', latest.version).replace('{date}', latest.date),
                copyright: 'Copyright © Salvatore Scalzi',
            },
            // Read by the theme: the notice on every page and the hero's link.
            translationNotice: { text: s.notice, link: s.noticeLink },
            latestReleaseLabels: s.latestRelease,
        },
    }
}

export default defineConfig({
    title: 'Relation Navigator',
    titleTemplate: ':title · Relation Navigator for Filament',
    description: 'A real page for every Filament relation manager and a searchable menu in the breadcrumb. Filament v4 & v5.',
    base: '/filament-relation-navigator/',
    cleanUrls: true,
    lastUpdated: true,
    sitemap: { hostname: site },
    head: [
        ['meta', { property: 'og:image', content: 'https://raw.githubusercontent.com/wit3/filament-relation-navigator/main/art/social-preview.jpg' }],
        ['meta', { name: 'twitter:card', content: 'summary_large_image' }],
        ['script', {}, `window.__FRN_DOCS__=${JSON.stringify(bannerData)}`],
        ['script', {}, bannerScript],
    ],
    // The sources of older versions, checked out to build their sites.
    srcExclude: ['.versions/**', 'scripts/**'],
    // llms.txt and llms-full.txt for AI agents, plus a .md version of each
    // page. English only: the translated pages would double them.
    vite: {
        plugins: [llmstxt({
            title: 'Relation Navigator for Filament',
            description: 'A real page for every Filament relation manager (/customers/1/orders) and a searchable menu in the breadcrumb. Filament v4 and v5. Package: wit3/filament-relation-navigator.',
            ignoreFiles: ['it/**', ...translatedLocales.map(({ code }) => `${code}/**`), '.versions/**'],
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
                    { text: 'Guide', link: '/guide/quick-start' },
                    { text: 'Configuration', link: '/reference/configuration' },
                    { text: 'API', link: '/reference/api' },
                    communityMenu(en),
                    versionMenu('', en),
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
                    { text: 'Guida', link: '/it/guide/quick-start' },
                    { text: 'Configurazione', link: '/it/reference/configuration' },
                    { text: 'API', link: '/it/reference/api' },
                    communityMenu(it),
                    versionMenu('/it', it),
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
                    message: `Versione ${latest.version} del ${latest.date.split('-').reverse().join('/')} · Licenza MIT.`,
                    copyright: 'Copyright © Salvatore Scalzi',
                },
                latestReleaseLabels: { label: 'Ultima versione', whatsNew: 'Novità' },
            },
        },
        ...Object.fromEntries(translatedLocales.map((locale) => [locale.code, translatedLocale(locale)])),
    },
    themeConfig: {
        search: {
            provider: 'local',
            options: {
                locales: {
                    ...Object.fromEntries(translatedLocales.map(({ code, strings: { search: t } }) => [code, {
                        translations: {
                            button: { buttonText: t.buttonText, buttonAriaLabel: t.buttonAriaLabel },
                            modal: {
                                displayDetails: t.displayDetails,
                                resetButtonTitle: t.resetButtonTitle,
                                backButtonTitle: t.backButtonTitle,
                                noResultsText: t.noResultsText,
                                footer: { selectText: t.selectText, navigateText: t.navigateText, closeText: t.closeText },
                            },
                        },
                    }])),
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
        // Read by the theme (the hero's "latest release" link).
        latest,
        outline: { level: [2, 3] },
        socialLinks: [{ icon: 'github', link: repository }],
        footer: {
            message: `Version ${latest.version}, released ${latest.date} · MIT License.`,
            copyright: 'Copyright © Salvatore Scalzi',
        },
    },
})
