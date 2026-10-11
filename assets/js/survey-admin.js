/* ===========================================================================
   Deflock — the survey admin page
   ---------------------------------------------------------------------------
   Only /survey-admin loads this. Three behaviours:

     1. Import is one button: picking a file sends the form.
     2. The invites table sorts by any column heading, and the counts above it
        filter it to one status. The choice lasts while the tab is open, so it
        survives the reload after an edit.
     3. While the send queue is running, ask the server every ten seconds
        whether it is time to send the next invite. The server decides, and
        sends at most one per interval however many pages ask, so this only
        keeps the page's words up to date.

   Browsers slow timers down in a tab nobody is looking at. The queue then
   sends a little less often than the interval allows, never more.
   =========================================================================== */

(function () {
  'use strict';

  /* --- 1. import ---------------------------------------------------------- */

  var file = document.querySelector('[data-import]');

  if (file) {
    file.form.classList.add('is-auto');
    file.addEventListener('change', function () {
      if (file.files.length > 0) { file.form.submit(); }
    });
  }

  /* --- 2. sorting and filtering the invites -------------------------------- */

  var invites = (function () {
    var table   = document.querySelector('[data-invites]');
    var tallies = document.querySelector('[data-tallies]');

    if (!table || !tallies) { return null; }

    var STATUSES = ['answered', 'waiting', 'queued', 'unsent', 'withdrawn'];
    var KEEP     = 'survey-admin-invites';
    var tbody    = table.tBodies[0];
    var headers  = Array.prototype.slice.call(table.tHead.rows[0].cells);
    var buttons  = Array.prototype.slice.call(tallies.querySelectorAll('[data-filter]'));
    var rows     = Array.prototype.slice.call(tbody.rows);

    // column -1 is the order the page arrived in, newest first.
    var view = { filter: '', column: -1, dir: 1 };

    rows.forEach(function (tr, n) { tr.setAttribute('data-order', String(n)); });

    try {
      var kept = JSON.parse(window.sessionStorage.getItem(KEEP) || 'null');
      if (kept
          && (kept.filter === '' || STATUSES.indexOf(kept.filter) !== -1)
          && (kept.column === -1 || (headers[kept.column] && headers[kept.column].hasAttribute('data-sort')))
          && (kept.dir === 1 || kept.dir === -1)) {
        view = kept;
      }
    } catch (e) { /* storage blocked: start from the page's own order */ }

    function keep() {
      try { window.sessionStorage.setItem(KEEP, JSON.stringify(view)); } catch (e) { /* not kept */ }
    }

    // What a row sorts by in one column. Blanks always go last.
    function key(tr, column) {
      var type = headers[column].getAttribute('data-sort');
      var cell = tr.cells[column];

      if (type === 'status') { return { blank: false, value: STATUSES.indexOf(tr.getAttribute('data-status')) }; }
      if (type === 'flag')   { return { blank: false, value: cell.textContent.trim() === '' ? 1 : 0 }; }

      var text = type === 'date' ? (cell.getAttribute('data-sort') || '') : cell.textContent.trim();

      return { blank: text === '', value: text };
    }

    function compare(a, b) {
      if (view.column === -1) {
        return Number(a.getAttribute('data-order')) - Number(b.getAttribute('data-order'));
      }

      var x = key(a, view.column);
      var y = key(b, view.column);

      if (x.blank !== y.blank) { return x.blank ? 1 : -1; }

      var d = typeof x.value === 'number'
        ? x.value - y.value
        : x.value.localeCompare(y.value, undefined, { numeric: true, sensitivity: 'base' });

      return d * view.dir || Number(a.getAttribute('data-order')) - Number(b.getAttribute('data-order'));
    }

    function draw() {
      rows.slice().sort(compare).forEach(function (tr) {
        tbody.appendChild(tr);
        tr.hidden = view.filter !== '' && tr.getAttribute('data-status') !== view.filter;
      });

      headers.forEach(function (th, n) {
        if (n === view.column) {
          th.setAttribute('aria-sort', view.dir === 1 ? 'ascending' : 'descending');
        } else {
          th.removeAttribute('aria-sort');
        }
      });

      buttons.forEach(function (b) {
        b.setAttribute('aria-pressed', b.getAttribute('data-filter') === view.filter ? 'true' : 'false');
      });
    }

    // The counts come from the rows, so they agree with the table as the queue sends.
    function recount() {
      buttons.forEach(function (b) {
        var status = b.getAttribute('data-filter');
        var n      = status === '' ? rows.length : rows.filter(function (tr) {
          return tr.getAttribute('data-status') === status;
        }).length;

        b.querySelector('[data-count]').textContent = String(n);
      });
    }

    // A heading's first click sorts the way that column is most often wanted:
    // A to Z, newest first, incumbents first, answered first. The second click
    // turns it round. The third goes back to newest invite first.
    headers.forEach(function (th, n) {
      var type = th.getAttribute('data-sort');

      if (!type) { return; }

      var button = document.createElement('button');
      button.type      = 'button';
      button.className = 'invites__sort';
      while (th.firstChild) { button.appendChild(th.firstChild); }
      th.appendChild(button);

      var first = type === 'date' ? -1 : 1;

      button.addEventListener('click', function () {
        if (view.column !== n) {
          view.column = n;
          view.dir    = first;
        } else if (view.dir === first) {
          view.dir = -first;
        } else {
          view.column = -1;
          view.dir    = 1;
        }
        keep();
        draw();
      });
    });

    // A count shows only that status. Selecting it again, or the total, shows every row.
    buttons.forEach(function (b) {
      b.addEventListener('click', function () {
        var status  = b.getAttribute('data-filter');
        view.filter = status === view.filter ? '' : status;
        keep();
        draw();
      });
    });

    draw();

    return {
      refresh: function () { recount(); draw(); }
    };
  }());

  /* --- 3. send queue ------------------------------------------------------ */

  var box = document.querySelector('[data-queue]');

  if (!box || box.getAttribute('data-running') !== '1' || !window.fetch) { return; }

  var csrf    = box.getAttribute('data-csrf');
  var summary = box.querySelector('[data-queue-summary]');
  var last    = box.querySelector('[data-queue-last]');
  var busy    = false;
  var timer;

  function stop(message) {
    clearInterval(timer);
    summary.textContent = message;
  }

  // Bring one table row up to date after the queue sends to it.
  function updateRow(row) {
    var tr = row && document.getElementById('i-' + row.id);

    if (!tr) { return; }

    var sent   = tr.querySelector('[data-cell="sent"]');
    var status = tr.querySelector('[data-cell="status"] .status');

    if (sent) {
      sent.textContent = row.sent.toLowerCase();
      sent.setAttribute('data-sort', row.sent_at);
    }
    if (status && status.tagName === 'SPAN') {
      status.className   = 'status status--' + row.status;
      status.textContent = row.status;
    }

    tr.setAttribute('data-status', row.status);
    if (invites) { invites.refresh(); }
  }

  function tick() {
    if (busy) { return; }
    busy = true;

    var body = new URLSearchParams();
    body.append('action', 'queue-tick');
    body.append('csrf', csrf);

    fetch('/survey-admin', { method: 'POST', body: body, credentials: 'same-origin' })
      .then(function (response) {
        var type = response.headers.get('Content-Type') || '';

        // Anything but JSON is the sign-in page or a stale-page notice.
        if (!response.ok || type.indexOf('application/json') !== 0) { throw new Error('signed out'); }

        return response.json();
      })
      .then(function (queue) {
        summary.textContent = queue.summary;
        last.textContent    = queue.last;
        updateRow(queue.row);

        // Finished, or paused from another tab: load the page fresh so the
        // buttons and counts match. The query string is new each time, so
        // this is a real load and never a re-sent form.
        if (!queue.running) {
          clearInterval(timer);
          window.location.href = '/survey-admin?queue=' + Date.now() + '#queue';
        }
      })
      .catch(function () {
        stop(box.getAttribute('data-cron') === '1'
          ? 'This page lost its sign-in or its connection. The server is still sending. Reload the page to see how far it has got.'
          : 'This page lost its sign-in or its connection, so it has stopped sending. Reload it to carry on.');
      })
      .then(function () { busy = false; });
  }

  timer = setInterval(tick, 10000);
  tick();
})();
