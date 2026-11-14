// Published security advisories of the package, and which versions they
// affect. The docs build reads them to show a banner on affected versions.
import { existsSync, readFileSync, writeFileSync } from 'node:fs'

const repository = 'wit3/filament-relation-navigator'
const committed = new URL('../advisories.json', import.meta.url)
const fetched = new URL('../.vitepress/advisories.generated.json', import.meta.url)

// The advisories fetched for this build, else the committed copy.
export function loadAdvisories() {
    const file = existsSync(fetched) ? fetched : committed

    return JSON.parse(readFileSync(file, 'utf8'))
}

// Refreshes the advisories from GitHub (no token needed for published ones).
export async function fetchAdvisories() {
    const headers = { Accept: 'application/vnd.github+json' }

    if (process.env.GH_TOKEN) {
        headers.Authorization = `Bearer ${process.env.GH_TOKEN}`
    }

    const response = await fetch(`https://api.github.com/repos/${repository}/security-advisories?state=published&per_page=100`, { headers })

    if (!response.ok) {
        throw new Error(`GitHub answered ${response.status}`)
    }

    const advisories = (await response.json()).map((advisory) => ({
        id: advisory.ghsa_id,
        patched: advisory.vulnerabilities?.[0]?.patched_versions ?? null,
        severity: advisory.severity,
        summary: advisory.summary,
        url: advisory.html_url,
        vulnerable: advisory.vulnerabilities?.[0]?.vulnerable_version_range ?? '',
    }))

    writeFileSync(fetched, JSON.stringify(advisories, null, 4) + '\n')

    return advisories
}

function compare(a, b) {
    const left = a.split('.').map(Number)
    const right = b.split('.').map(Number)

    for (let i = 0; i < 3; i++) {
        if ((left[i] ?? 0) !== (right[i] ?? 0)) {
            return (left[i] ?? 0) < (right[i] ?? 0) ? -1 : 1
        }
    }

    return 0
}

// ">= 1.3.0, < 1.6.0" style ranges, as GitHub stores them.
export function affects(version, range) {
    return range.split(',').map((part) => part.trim()).filter(Boolean).every((part) => {
        const [, operator, bound] = part.match(/^(>=|<=|>|<|=)?\s*v?(\d+\.\d+\.\d+)$/) ?? []

        if (!bound) {
            return false
        }

        const result = compare(version, bound)

        return { '>=': result >= 0, '<=': result <= 0, '>': result > 0, '<': result < 0, '=': result === 0 }[operator ?? '=']
    })
}

const order = ['low', 'medium', 'high', 'critical']

// The advisories that affect a version, worst first.
export function advisoriesFor(version, advisories = loadAdvisories()) {
    return advisories
        .filter((advisory) => affects(version, advisory.vulnerable))
        .sort((a, b) => order.indexOf(b.severity) - order.indexOf(a.severity))
}

// Run directly: refresh the advisories (used by the docs workflow).
if (import.meta.url === `file://${process.argv[1]}`) {
    fetchAdvisories()
        .then((advisories) => console.log(`${advisories.length} published advisories`))
        .catch((error) => console.warn(`Advisories not refreshed, using the committed copy: ${error.message}`))
}
