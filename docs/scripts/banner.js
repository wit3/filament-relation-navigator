// Version and security banner of the documentation. Plain JavaScript, so the
// same file works on the current site and on the sites of older versions.
// Data: window.__FRN_DOCS__ = { version, latest, latestUrl, isLatest, advisories }.
(function () {
    var data = window.__FRN_DOCS__
    if (!data) return

    var italian = location.pathname.indexOf('/it/') !== -1
    var t = italian ? {
        old: 'Stai leggendo la documentazione della versione ' + data.version + '. L’ultima è la ' + data.latest + '.',
        latest: 'Vai alla documentazione più recente',
        security: function (a) { return 'Problema di sicurezza (' + ({ critical: 'critico', high: 'alto', medium: 'medio', low: 'basso' }[a.severity] || a.severity) + ') nella versione ' + data.version + ': ' + a.summary + '.' },
        update: function (a) { return a.patched ? ' Aggiorna alla ' + a.patched + ' o successiva.' : ' Aggiorna appena possibile.' },
        advisory: 'Leggi l’advisory',
        close: 'Chiudi',
    } : {
        old: 'You are reading the documentation of version ' + data.version + '. The latest is ' + data.latest + '.',
        latest: 'Go to the latest documentation',
        security: function (a) { return 'Security issue (' + a.severity + ') in version ' + data.version + ': ' + a.summary + '.' },
        update: function (a) { return a.patched ? ' Update to ' + a.patched + ' or later.' : ' Update as soon as possible.' },
        advisory: 'Read the advisory',
        close: 'Close',
    }

    var advisory = (data.advisories || [])[0]
    if (!advisory && data.isLatest) return

    // Critical and high: always on top, cannot be closed. Medium, low and
    // older versions: on top of the page, scroll away, can be closed.
    var sticky = !!advisory && (advisory.severity === 'critical' || advisory.severity === 'high')
    var level = advisory ? advisory.severity : 'info'
    var storageKey = 'frn-docs-banner:' + data.version + ':' + (advisory ? advisory.id : 'old')

    try {
        if (!sticky && localStorage.getItem(storageKey) === 'closed') return
    } catch (error) {}

    var css = '.frn-banner{position:' + (sticky ? 'fixed' : 'absolute') + ';top:0;left:0;right:0;z-index:100;display:flex;gap:12px;align-items:center;justify-content:center;flex-wrap:wrap;padding:10px 48px;font:500 14px/1.5 Inter,ui-sans-serif,system-ui,sans-serif;text-align:center}'
        + '.frn-banner a{color:inherit;font-weight:600;text-decoration:underline;text-underline-offset:2px}'
        + '.frn-banner button{position:absolute;right:12px;top:50%;transform:translateY(-50%);border:0;background:none;color:inherit;font-size:20px;line-height:1;cursor:pointer;padding:4px 8px}'
        + '.frn-banner-critical,.frn-banner-high{background:#b91c1c;color:#fff}'
        + '.frn-banner-medium{background:#f59e0b;color:#1c1917}'
        + '.frn-banner-low{background:#fde68a;color:#1c1917}'
        + '.frn-banner-info{background:var(--vp-c-bg-soft,#f4f4f5);color:var(--vp-c-text-1,#18181b);border-bottom:1px solid var(--vp-c-divider,#e4e4e7)}'

    function render() {
        if (document.getElementById('frn-banner')) return

        var style = document.createElement('style')
        style.textContent = css
        document.head.appendChild(style)

        var banner = document.createElement('div')
        banner.id = 'frn-banner'
        banner.className = 'frn-banner frn-banner-' + level
        banner.setAttribute('role', advisory ? 'alert' : 'status')

        var text = document.createElement('span')
        text.textContent = advisory ? t.security(advisory) + t.update(advisory) : t.old
        banner.appendChild(text)

        if (advisory) {
            var read = document.createElement('a')
            read.href = advisory.url
            read.textContent = t.advisory
            banner.appendChild(read)
        }

        if (!data.isLatest) {
            var latest = document.createElement('a')
            latest.href = data.latestUrl + (italian ? 'it/' : '')
            latest.textContent = t.latest
            banner.appendChild(latest)
        }

        document.body.insertBefore(banner, document.body.firstChild)

        // VitePress moves its fixed bar below a banner of this height.
        var root = document.documentElement
        var height = function () { return banner.offsetHeight }
        var update = function () {
            var offset = sticky ? height() : Math.max(0, height() - window.scrollY)
            root.style.setProperty('--vp-layout-top-height', offset + 'px')
        }

        if (!sticky) {
            var close = document.createElement('button')
            close.type = 'button'
            close.setAttribute('aria-label', t.close)
            close.textContent = '×'
            close.onclick = function () {
                banner.remove()
                root.style.setProperty('--vp-layout-top-height', '0px')
                window.removeEventListener('scroll', update)
                try { localStorage.setItem(storageKey, 'closed') } catch (error) {}
            }
            banner.appendChild(close)
            window.addEventListener('scroll', update, { passive: true })
        }

        // VitePress moves its bar and its content by --vp-layout-top-height.
        window.addEventListener('resize', update)
        update()
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', render)
    } else {
        render()
    }
})()
