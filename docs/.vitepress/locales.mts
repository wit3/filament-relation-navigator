import { existsSync, readFileSync } from 'node:fs'

// The languages translated from English, besides Italian: each has its pages
// in docs/<code>/, its README.<code>.md and its strings in locales/<code>.json.
export interface TranslatedLocale {
    code: string
    label: string
    lang: string
    dir?: 'rtl'
    strings: Record<string, any>
}

const languages: Omit<TranslatedLocale, 'strings'>[] = [
    { code: 'es', label: 'Español', lang: 'es-ES' },
    { code: 'pt', label: 'Português', lang: 'pt-BR' },
    { code: 'fr', label: 'Français', lang: 'fr-FR' },
    { code: 'de', label: 'Deutsch', lang: 'de-DE' },
    { code: 'zh', label: '中文', lang: 'zh-CN' },
    { code: 'ja', label: '日本語', lang: 'ja-JP' },
    { code: 'ru', label: 'Русский', lang: 'ru-RU' },
    { code: 'ar', label: 'العربية', lang: 'ar', dir: 'rtl' },
]

export const translatedLocales: TranslatedLocale[] = languages
    .filter(({ code }) => existsSync(new URL(`./locales/${code}.json`, import.meta.url)))
    .map((language) => ({
        ...language,
        strings: JSON.parse(readFileSync(new URL(`./locales/${language.code}.json`, import.meta.url), 'utf8')),
    }))
