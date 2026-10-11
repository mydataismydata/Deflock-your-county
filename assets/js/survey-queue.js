/* ===========================================================================
   Deflock — the survey's send queue
   ---------------------------------------------------------------------------
   Only /survey-admin loads this. While the queue is running, it asks the
   server every ten seconds whether it is time to send the next invite. The
   server decides, and sends at most one a minute however many pages ask, so
   this file only keeps the page's words up to date.

   Browsers slow timers down in a tab nobody is looking at. The queue then
   sends a little less often than once a minute, never more.
   =========================================================================== */

(function () {
  'use strict';

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

    if (sent) { sent.textContent = row.sent.toLowerCase(); }
    if (status && status.tagName === 'SPAN') {
      status.className   = 'status status--' + row.status;
      status.textContent = row.status;
    }
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
        stop('This page lost its sign-in or its connection, so it has stopped sending. Reload it to carry on.');
      })
      .then(function () { busy = false; });
  }

  timer = setInterval(tick, 10000);
  tick();
})();
