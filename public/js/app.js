/* CariKopi — skrip kecil untuk seluruh situs (tanpa dependensi selain Bootstrap). */
(function () {
  'use strict';

  /* ---------- Pop-up (SweetAlert2) ---------- */
  var ACCENT = '#9c5f18';

  window.notify = function (message, type) {
    var error = type === 'error';

    if (!window.Swal) { window.alert(message); return; }

    Swal.fire({
      icon: error ? 'error' : 'success',
      title: error ? 'Gagal!' : 'Berhasil!',
      text: message,
      confirmButtonText: 'OK',
      confirmButtonColor: ACCENT,
      timer: error ? undefined : 3500,          // sukses menutup sendiri, gagal menunggu OK
      timerProgressBar: !error
    });
  };

  function parseJson(text) {
    try { return text ? JSON.parse(text) : null; } catch (e) { return null; }
  }

  /* Pesan error di bawah tiap kolom yang salah (kolom diberi garis merah). */
  function showFieldErrors(map) {
    Object.keys(map || {}).forEach(function (name) {
      var field = document.querySelector('form [name="' + name + '"]:not([type="hidden"])');
      if (!field) return;
      field.classList.add('is-invalid');
      var msg = document.createElement('div');
      msg.className = 'invalid-feedback d-block';
      msg.textContent = map[name];
      (field.closest('.qty, .rate-input, .search-pill, .password-field') || field).insertAdjacentElement('afterend', msg);
    });
  }

  /* Daftar error validasi dalam satu pop-up. */
  function notifyErrors(list) {
    if (list.length === 1) { window.notify(list[0], 'error'); return; }
    if (!window.Swal) { window.alert(list.join('\n')); return; }

    var ul = document.createElement('ul');
    ul.className = 'swal-list';
    list.forEach(function (text) {
      var li = document.createElement('li');
      li.textContent = text;
      ul.appendChild(li);
    });

    Swal.fire({ icon: 'error', title: 'Gagal!', html: ul, confirmButtonText: 'OK', confirmButtonColor: ACCENT });
  }

  var flash = document.getElementById('flash');
  if (flash) {
    var errors = parseJson(flash.dataset.errors);
    showFieldErrors(parseJson(flash.dataset.fieldErrors));

    if (errors && errors.length) notifyErrors(errors);
    else if (flash.dataset.error) window.notify(flash.dataset.error, 'error');
    else if (flash.dataset.success) window.notify(flash.dataset.success, 'success');
  }

  /* Konfirmasi sebelum aksi berbahaya: <form data-confirm="Hapus ini?"> */
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form.matches('form[data-confirm]') || form.dataset.confirmed) return;

    e.preventDefault();

    if (!window.Swal) {
      if (window.confirm(form.dataset.confirm)) { form.dataset.confirmed = '1'; form.submit(); }
      return;
    }

    Swal.fire({
      icon: 'warning',
      title: 'Yakin?',
      text: form.dataset.confirm,
      showCancelButton: true,
      confirmButtonText: 'Ya, lanjutkan',
      cancelButtonText: 'Batal',
      confirmButtonColor: ACCENT,
      cancelButtonColor: '#7b6a5c',
      reverseButtons: true
    }).then(function (result) {
      if (result.isConfirmed) { form.dataset.confirmed = '1'; form.submit(); }
    });
  });

  /* ---------- Pencarian & filter langsung (tanpa tombol, tanpa reload) ----------
     <form data-live data-live-target="#a, #b"> : hasil dari server di-fetch lalu hanya
     elemen target yang diganti, jadi posisi scroll dan fokus ketikan tidak berubah. */
  document.querySelectorAll('form[data-live]').forEach(function (form) {
    var targets = form.dataset.liveTarget.split(',').map(function (t) { return t.trim(); });
    var resetBtn = form.querySelector('[data-live-reset]');
    var timer = null;
    var controller = null;

    function fields() {
      return Array.prototype.filter.call(form.elements, function (el) {
        return el.name && !el.disabled && el.type !== 'submit' && el.type !== 'button';
      });
    }

    function isDefault(el) {
      return el.value.trim() === (el.dataset.default || '');
    }

    function buildUrl() {
      var params = new URLSearchParams();
      fields().forEach(function (el) {
        if (el.type === 'hidden' || !isDefault(el)) {
          if (el.value.trim() !== '') params.set(el.name, el.value.trim());
        }
      });
      var base = form.action.split('#')[0].split('?')[0];
      return params.toString() ? base + '?' + params.toString() : base;
    }

    function updateReset() {
      if (!resetBtn) return;
      resetBtn.hidden = !fields().some(function (el) { return el.type !== 'hidden' && !isDefault(el); });
    }

    function setLoading(on) {
      targets.forEach(function (t) {
        var el = document.querySelector(t);
        if (el) { el.classList.toggle('is-loading', on); el.setAttribute('aria-busy', on ? 'true' : 'false'); }
      });
    }

    function load(url, scrollToList) {
      if (controller) controller.abort();
      var mine = controller = new AbortController();
      setLoading(true);

      fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, signal: mine.signal })
        .then(function (res) {
          if (!res.ok) throw new Error('http ' + res.status);
          return res.text();
        })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          targets.forEach(function (t) {
            var from = doc.querySelector(t);
            var to = document.querySelector(t);
            if (from && to) to.innerHTML = from.innerHTML;
          });
          history.replaceState(null, '', url);
          updateReset();
          if (scrollToList) document.querySelector(targets[0]).scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(function (err) {
          if (err.name === 'AbortError') return;
          window.location.href = url;            // cadangan: muat halaman biasa
        })
        .then(function () {
          if (controller === mine) setLoading(false);
        });
    }

    form.addEventListener('input', function (e) {
      if (e.target.matches('input[type="search"], input[type="text"]')) {
        clearTimeout(timer);
        updateReset();
        timer = setTimeout(function () { load(buildUrl(), false); }, 350);
      }
    });

    form.addEventListener('change', function (e) {
      if (e.target.matches('select')) { clearTimeout(timer); updateReset(); load(buildUrl(), false); }
    });

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearTimeout(timer);
      load(buildUrl(), false);
    });

    if (resetBtn) {
      resetBtn.addEventListener('click', function () {
        fields().forEach(function (el) { if (el.type !== 'hidden') el.value = el.dataset.default || ''; });
        clearTimeout(timer);
        updateReset();
        load(buildUrl(), false);
      });
    }

    // Halaman berikutnya juga tanpa reload; setelah itu gulir ke awal daftar.
    document.addEventListener('click', function (e) {
      var link = e.target.closest('.pagination a');
      if (!link || !targets.some(function (t) { return link.closest(t); })) return;
      e.preventDefault();
      load(link.href.split('#')[0], true);
    });
  });

  /* ---------- Ikon mata: tampilkan/sembunyikan password ----------
     Otomatis untuk setiap input password, jadi form baru tidak perlu diubah. */
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    var wrap = document.createElement('div');
    wrap.className = 'password-field';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'password-toggle';
    btn.setAttribute('aria-label', 'Tampilkan password');
    btn.setAttribute('aria-pressed', 'false');
    btn.innerHTML = '<i class="fa-regular fa-eye" aria-hidden="true"></i>';
    wrap.appendChild(btn);

    btn.addEventListener('click', function () {
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-pressed', show ? 'true' : 'false');
      btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
      btn.firstChild.className = show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
      input.focus();
    });
  });

  /* ---------- Stepper jumlah (+ / -) ---------- */
  function clamp(input) {
    var min = parseInt(input.min, 10) || 1;
    var max = parseInt(input.max, 10) || 99;
    var value = parseInt(input.value, 10);
    if (isNaN(value)) value = min;
    input.value = Math.min(max, Math.max(min, value));
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-qty-step]');
    if (!btn) return;
    var input = btn.closest('[data-qty]').querySelector('input');
    var before = input.value;
    input.value = (parseInt(input.value, 10) || 1) + Number(btn.dataset.qtyStep);
    clamp(input);
    if (input.value !== before) input.dispatchEvent(new Event('change', { bubbles: true }));
  });

  document.addEventListener('focusout', function (e) {
    if (e.target.matches('[data-qty] input')) clamp(e.target);
  });

  /* ---------- Keranjang: ubah jumlah langsung memperbarui harga ---------- */
  var timers = new WeakMap();

  document.addEventListener('change', function (e) {
    var input = e.target;
    if (!input.matches('input[data-update-url]')) return;
    clamp(input);
    clearTimeout(timers.get(input));
    timers.set(input, setTimeout(function () { saveQuantity(input); }, 300));
  });

  function setText(selector, text) {
    var el = document.querySelector(selector);
    if (el) el.textContent = text;
  }

  function saveQuantity(input) {
    var token = document.querySelector('meta[name="csrf-token"]').content;

    fetch(input.dataset.updateUrl, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ quantity: parseInt(input.value, 10) })
    })
      .then(function (res) {
        if (!res.ok) throw new Error('gagal');
        return res.json();
      })
      .then(function (d) {
        setText('[data-line-subtotal="' + input.dataset.itemId + '"]', d.line_subtotal);
        setText('#sum-subtotal', d.subtotal);
        setText('#sum-discount', d.discount);
        setText('#sum-total', d.total);
        var badge = document.querySelector('.cart-badge');
        if (badge) badge.textContent = d.count;
      })
      .catch(function () {
        window.notify('Gagal memperbarui jumlah. Halaman akan dimuat ulang.', 'error');
        setTimeout(function () { location.reload(); }, 1200);
      });
  }
})();
