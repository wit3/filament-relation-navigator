// Builds the documentation of every older minor version from its git tag,
// into .vitepress/dist/vX.Y/, and adds the version and security banner.
// Run after `vitepress build` (the current site): node scripts/build-versions.mjs
import { execSync } from 'node:child_process'
import { cpSync, existsSync, mkdirSync, readdirSync, readFileSync, rmSync, statSync, writeFileSync } from 'node:fs'
import { join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { advisoriesFor } from './advisories.mjs'
import { latestRelease, olderMinorVersions } from './versions.mjs'

const docs = fileURLToPath(new URL('..', import.meta.url))
const work = join(docs, '.versions')
const dist = join(docs, '.vitepress', 'dist')
const site = 'https://wit3.github.io/filament-relation-navigator/'
const basePath = '/filament-relation-navigator/'
const banner = readFileSync(join(docs, 'scripts', 'banner.js'), 'utf8')

const latest = latestRelease()
const versions = olderMinorVersions()

if (!latest.version || !existsSync(dist)) {
    console.error('Build the current site first, from a clone with the tags.')
    process.exit(1)
}

rmSync(work, { recursive: true, force: true })
execSync('git worktree prune', { cwd: join(docs, '..') })
mkdirSync(work, { recursive: true })

for (const { minor, version, tag } of versions) {
    const source = join(work, `v${minor}`)
    const out = join(dist, `v${minor}`)
    const base = `${basePath}v${minor}/`

    // Every file of that version: a worktree, since archives leave out the
    // files that .gitattributes keeps out of the Composer package (docs).
    execSync(`git worktree add --detach --force "${source}" ${tag}`, { cwd: join(docs, '..'), stdio: 'ignore' })

    if (existsSync(join(source, 'docs', '.vitepress'))) {
        // The site of that version, as it was.
        execSync(`npx vitepress build "${join(source, 'docs')}" --base ${base} --outDir "${out}"`, { cwd: docs, stdio: 'inherit' })
    } else {
        // Before the site existed: the README and the changelog of that version.
        const page = join(source, 'site')
        mkdirSync(join(page, '.vitepress'), { recursive: true })
        // Without the live badges (they show today's version and build).
        const readme = readFileSync(join(source, 'README.md'), 'utf8').replace(/<p class="filament-hidden">[\s\S]*?<\/p>\n*/, '')
        writeFileSync(join(page, 'index.md'), '::: v-pre\n\n' + readme + '\n\n:::\n')
        writeFileSync(join(page, 'changelog.md'), '::: v-pre\n\n' + readFileSync(join(source, 'CHANGELOG.md'), 'utf8') + '\n\n:::\n')
        writeFileSync(join(page, '.vitepress', 'config.mjs'), `export default {
    title: 'Relation Navigator ${version}',
    description: 'Documentation of wit3/filament-relation-navigator ${version}.',
    base: '${base}',
    cleanUrls: true,
    ignoreDeadLinks: true,
    themeConfig: {
        nav: [
            { text: 'README', link: '/' },
            { text: 'Changelog', link: '/changelog' },
            { text: 'Latest documentation', link: '${site}', target: '_self' },
        ],
        socialLinks: [{ icon: 'github', link: 'https://github.com/wit3/filament-relation-navigator/tree/${tag}' }],
        outline: { level: [2, 3] },
        search: { provider: 'local' },
    },
}
`)
        execSync(`npx vitepress build "${page}" --outDir "${out}"`, { cwd: docs, stdio: 'inherit' })
    }

    // The banner on every page of that version.
    const data = JSON.stringify({
        version,
        latest: latest.version,
        latestUrl: site,
        isLatest: false,
        advisories: advisoriesFor(version),
    })
    const snippet = `<script>window.__FRN_DOCS__=${data}</script><script>${banner}</script>`

    for (const file of htmlFiles(out)) {
        writeFileSync(file, readFileSync(file, 'utf8').replace('</head>', `${snippet}</head>`))
    }

    execSync(`git worktree remove --force "${source}"`, { cwd: join(docs, '..'), stdio: 'ignore' })
    console.log(`Built v${minor} (${version}) at ${base}`)
}

execSync('git worktree prune', { cwd: join(docs, '..') })

function htmlFiles(directory) {
    return readdirSync(directory).flatMap((name) => {
        const path = join(directory, name)

        return statSync(path).isDirectory() ? htmlFiles(path) : (name.endsWith('.html') ? [path] : [])
    })
}
