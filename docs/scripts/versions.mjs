// The minor versions of the package, from the git tags: each one with its
// latest patch. The current site is the latest release in the changelog; the
// other minors get a site of their own under /vX.Y/.
import { execSync } from 'node:child_process'
import { readFileSync } from 'node:fs'

function sortVersions(versions) {
    return versions.sort((a, b) => {
        const left = a.split('.').map(Number)
        const right = b.split('.').map(Number)

        return (right[0] - left[0]) || (right[1] - left[1]) || (right[2] - left[2])
    })
}

export function minorVersions() {
    let tags = []

    try {
        tags = execSync('git tag -l "v*"', { encoding: 'utf8' }).split('\n').map((tag) => tag.trim()).filter((tag) => /^v\d+\.\d+\.\d+$/.test(tag))
    } catch {
        return []
    }

    const latestPatch = new Map()

    for (const version of sortVersions(tags.map((tag) => tag.slice(1)))) {
        const minor = version.split('.').slice(0, 2).join('.')

        if (!latestPatch.has(minor)) {
            latestPatch.set(minor, version)
        }
    }

    return [...latestPatch.entries()].map(([minor, version]) => ({ minor, version, tag: `v${version}` }))
}

// The latest release in the changelog: its version, date and summary. It
// decides the current site, so a build that runs before the release tag
// exists (the push of the release commit) builds the same sites as the tag.
export function latestRelease() {
    const changelog = readFileSync(new URL('../../CHANGELOG.md', import.meta.url), 'utf8')
    const release = changelog.match(/^## (\d+\.\d+\.\d+) - (\d{4}-\d{2}-\d{2})\n+(?!#)([^\n]+)?/m)

    return { version: release?.[1] ?? '', date: release?.[2] ?? '', summary: release?.[3] ?? '' }
}

// The minor versions with a site of their own: every one but the current.
export function olderMinorVersions() {
    const current = latestRelease().version.split('.').slice(0, 2).join('.')

    return minorVersions().filter(({ minor }) => minor !== current)
}
