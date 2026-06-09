(function () {
    var MAPPING = window.matomoEventIcons || {};
    var BASE_URL = window.matomoEventIconsBaseUrl || 'plugins/EventIcons/icons/material/';

    function getKeyFromCategoryActionText(text) {
        var parts = text.split(' - ');
        if (parts.length < 2) return null;
        var category = parts[0].trim();
        var action = parts[1].trim();
        if (!category || !action) return null;
        return category + '/' + action;
    }

    function replaceIcon(img, key) {
        var iconFile = MAPPING[key];
        if (iconFile) {
            img.src = BASE_URL + iconFile + '.svg';
            img.setAttribute('data-ei-replaced', '1');
            img.setAttribute('data-ei-key', key);
        }
    }

    function replaceVisitorLogIcons(root) {
        var images = root.querySelectorAll('.action-list-action-icon.event');
        for (var i = 0; i < images.length; i++) {
            var img = images[i];
            if (img.getAttribute('data-ei-replaced')) continue;

            var li = img.closest('.action');
            if (!li) li = img.closest('li');
            if (!li) continue;

            var eventSpan = li.querySelector('.truncated-text-line.event');
            if (!eventSpan) continue;

            var key = getKeyFromCategoryActionText(eventSpan.textContent.trim());
            if (key) replaceIcon(img, key);
        }
    }

    function replaceLiveWidgetIcons(root) {
        var liveWidget = document.getElementById('visitsLive');
        if (!liveWidget) return;

        var container = (root === document || root === liveWidget || liveWidget.contains(root))
            ? liveWidget : null;
        if (!container) return;

        var images = container.querySelectorAll('img.iconPadding[src*="event."]');
        for (var i = 0; i < images.length; i++) {
            var img = images[i];
            if (img.getAttribute('data-ei-replaced')) continue;

            var title = img.getAttribute('title') || '';
            var match = title.match(/^[^ ]+ (.+?) - (.+?)(?: -|$)/);
            if (!match) continue;

            var key = match[1].trim() + '/' + match[2].trim();
            replaceIcon(img, key);
        }
    }

    function replaceIconsInNode(root) {
        if (!root || !root.querySelectorAll) return;
        replaceVisitorLogIcons(root);
        replaceLiveWidgetIcons(root);
    }

    function onReady() {
        replaceIconsInNode(document);

        var target = document.getElementById('visitsLive');
        if (!target) {
            target = document.body;
        }

        var observer = new MutationObserver(function (mutations) {
            for (var m = 0; m < mutations.length; m++) {
                var added = mutations[m].addedNodes;
                for (var n = 0; n < added.length; n++) {
                    if (added[n].nodeType === 1) {
                        replaceIconsInNode(added[n]);
                    }
                }
            }
        });

        observer.observe(target, {
            childList: true,
            subtree: true,
            attributes: false
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onReady);
    } else {
        onReady();
    }
})();
