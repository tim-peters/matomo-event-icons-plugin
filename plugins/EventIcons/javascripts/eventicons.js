(function () {
    var MAPPING = window.matomoEventIcons || {};
    var BASE_URL = window.matomoEventIconsBaseUrl || 'plugins/EventIcons/icons/material/';

    if (Object.keys(MAPPING).length === 0) {
        return;
    }

    function replaceIconsInNode(root) {
        if (!root || !root.querySelectorAll) return;

        var images = root.querySelectorAll('.action-list-action-icon.event');
        if (!images.length) return;

        for (var i = 0; i < images.length; i++) {
            var img = images[i];
            if (img.getAttribute('data-ei-replaced')) continue;

            var li = img.closest('.action');
            if (!li) li = img.closest('li');
            if (!li) continue;

            var eventSpan = li.querySelector('.truncated-text-line.event');
            if (!eventSpan) continue;

            var text = eventSpan.textContent.trim();
            var parts = text.split(' - ');
            if (parts.length < 2) continue;

            var key = parts[0].trim() + '/' + parts[1].trim();
            var iconFile = MAPPING[key];

            if (iconFile) {
                img.src = BASE_URL + iconFile + '.svg';
                img.setAttribute('data-ei-replaced', '1');
                img.setAttribute('data-ei-key', key);
            }
        }
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
