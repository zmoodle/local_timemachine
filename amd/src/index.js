/**
 * Behaviour for local_timemachine admin page.
 *
 * Handles expand/collapse and confirmation dialogs.
 *
 * @module     local_timemachine/index
 * @copyright 2025 GiDA
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    /**
     * Toggle child rows for a course.
     *
     * @param {HTMLElement} link
     */
    var toggleRows = function(link) {
        var cid = link.getAttribute('data-courseid');
        var expanded = link.getAttribute('aria-expanded') === 'true';
        var rows = document.querySelectorAll('tr.tm-child');
        rows.forEach(function(tr) {
            if (tr.getAttribute('data-parent') === cid) {
                tr.style.display = expanded ? 'none' : 'table-row';
            }
        });
        link.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        var icon = link.querySelector('img, i');
        if (icon) {
            var urlCollapsed = link.getAttribute('data-icon-collapsed');
            var urlExpanded = link.getAttribute('data-icon-expanded');
            if (icon.tagName.toLowerCase() === 'img') {
                if (urlCollapsed && urlExpanded) {
                    icon.setAttribute('src', expanded ? urlCollapsed : urlExpanded);
                }
            } else if (icon.classList) {
                var classCollapsed = link.getAttribute('data-icon-class-collapsed');
                var classExpanded = link.getAttribute('data-icon-class-expanded');
                if (classCollapsed && classExpanded) {
                    icon.classList.remove(expanded ? classExpanded : classCollapsed);
                    icon.classList.add(expanded ? classCollapsed : classExpanded);
                }
            }
        }
        var titleExpand = link.getAttribute('data-title-expand') || '';
        var titleCollapse = link.getAttribute('data-title-collapse') || '';
        link.setAttribute('title', expanded ? titleExpand : titleCollapse);
    };

    /**
     * Attach delegated click handlers.
     */
    var init = function() {
        document.addEventListener('click', function(e) {
            var toggle = e.target.closest('a.tm-toggle');
            if (toggle) {
                e.preventDefault();
                toggleRows(toggle);
                return;
            }
            var link = e.target.closest('a.tm-confirm');
            if (!link) {
                return;
            }
            var msg = link.getAttribute('data-confirm') || '';
            if (msg && !window.confirm(msg)) {
                e.preventDefault();
            }
        });
    };

    return {init: init};
});
