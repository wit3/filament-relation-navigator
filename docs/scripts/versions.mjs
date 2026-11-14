// The minor versions of the package, from the git tags: each one with its
// latest patch. The newest minor is the current site; the others get a site
// of their own under /vX.Y/.
import { execSync } from 'node:child_process'

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
