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

    var actionTimeCache = {};

    function getCurrentSiteId() {
        var m = location.search.match(/idSite=(\d+)/);
        return m ? parseInt(m[1]) : 1;
    }

    function fetchActionTimes(idVisit, idSite, callback) {
        var url = 'index.php?module=EventIcons&action=getVisitActionTimes&idVisit=' + idVisit + '&idSite=' + idSite;
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function () {
            if (xhr.status === 200) {
                try {
                    var data = JSON.parse(xhr.responseText);
                    if (Array.isArray(data)) {
                        callback(idVisit, data);
                    }
                } catch (e) {}
            }
        };
        xhr.send();
    }

    function updateEventTooltips(container, actionData) {
        var imgs = container.querySelectorAll('img.iconPadding');
        for (var i = 0; i < imgs.length; i++) {
            var img = imgs[i];
            var title = img.getAttribute('title') || '';
            if (!title.match(/^(Ereignis|Event)\s/)) continue;
            if (title.indexOf('\n') >= 0) continue;

            var data = actionData[i];
            if (!data) continue;

            var timeOnPageLabel = window.piwik_translations && window.piwik_translations['General_TimeOnPage']
                ? window.piwik_translations['General_TimeOnPage']
                : 'Time on page';
            var timeInfo = '\n' + data.serverTimePretty;
            if (data.timeSpentPretty) {
                timeInfo += '\n' + timeOnPageLabel + ': ' + data.timeSpentPretty;
            }
            img.setAttribute('title', title + timeInfo);
        }
    }

    function enhanceEventTooltips(root) {
        var liveWidget = document.getElementById('visitsLive');
        if (!liveWidget) return;

        var container = (root === document || liveWidget.contains(root)) ? liveWidget : null;
        if (!container) return;

        var visits = container.querySelectorAll('li.visit:not([data-ei-enriched])');
        var idSite = getCurrentSiteId();

        for (var v = 0; v < visits.length; v++) {
            var visit = visits[v];
            var idVisit = parseInt(visit.id.replace('vid', ''));
            if (!idVisit) continue;

            var actionsContainer = document.getElementById('actions_' + idVisit);
            if (!actionsContainer) continue;

            visit.setAttribute('data-ei-enriched', '1');

            if (actionTimeCache[idVisit]) {
                updateEventTooltips(actionsContainer, actionTimeCache[idVisit]);
                continue;
            }

            fetchActionTimes(idVisit, idSite, function (id, actions) {
                actionTimeCache[id] = actions;
                var container = document.getElementById('actions_' + id);
                if (container) {
                    updateEventTooltips(container, actions);
                }
            });
        }
    }

    function replaceIconsInNode(root) {
        if (!root || !root.querySelectorAll) return;
        replaceVisitorLogIcons(root);
        replaceLiveWidgetIcons(root);
        enhanceEventTooltips(root);
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
