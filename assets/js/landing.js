/* ============================================================
   RentIt — landing.js
   All features implemented with vanilla JS + jQuery
   ============================================================ */

$(function () {

  /* ─────────────────────────────────────────────
     1. STICKY NAVBAR — shadow + shrink on scroll
  ───────────────────────────────────────────── */
  const $nav = $('.navbar');
  $(window).on('scroll.nav', function () {
    if ($(this).scrollTop() > 40) {
      $nav.addClass('nav-scrolled');
    } else {
      $nav.removeClass('nav-scrolled');
    }
  });


  /* ─────────────────────────────────────────────
     2. TYPEWRITER — cycles words in hero h1
  ───────────────────────────────────────────── */
  const words = ['anything.', 'cameras.', 'bikes.', 'tools.', 'tents.', 'kayaks.'];
  let wordIndex = 0;
  let charIndex  = 0;
  let deleting   = false;

  const $typeTarget = $('#typewriter-word');
  if ($typeTarget.length) {
    function type() {
      const current = words[wordIndex];
      if (deleting) {
        charIndex--;
        $typeTarget.text(current.slice(0, charIndex));
        if (charIndex === 0) {
          deleting = false;
          wordIndex = (wordIndex + 1) % words.length;
          setTimeout(type, 400);
          return;
        }
        setTimeout(type, 60);
      } else {
        charIndex++;
        $typeTarget.text(current.slice(0, charIndex));
        if (charIndex === current.length) {
          deleting = true;
          setTimeout(type, 1800);
          return;
        }
        setTimeout(type, 100);
      }
    }
    setTimeout(type, 800);
  }


  /* ─────────────────────────────────────────────
     3. SCROLL REVEAL — Intersection Observer
  ───────────────────────────────────────────── */
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('revealed');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });
    revealEls.forEach(el => observer.observe(el));
  } else {
    // Fallback: just show everything
    $('.reveal').addClass('revealed');
  }


  /* ─────────────────────────────────────────────
     4. CATEGORY CARD RIPPLE on click
  ───────────────────────────────────────────── */
  $(document).on('click', '.cat-card', function (e) {
    const $card = $(this);
    const offset = $card.offset();
    const x = e.pageX - offset.left;
    const y = e.pageY - offset.top;

    const $ripple = $('<span class="ripple"></span>').css({ left: x, top: y });
    $card.append($ripple);
    setTimeout(() => $ripple.remove(), 600);
  });


  /* ─────────────────────────────────────────────
     5. HERO SEARCH BAR — highlights matching category
  ───────────────────────────────────────────── */
  const categoryMap = {
    camera: 'electronics', photo: 'electronics', lens: 'electronics', drone: 'electronics',
    bike: 'mobility', scooter: 'mobility', cycle: 'mobility', ride: 'mobility',
    drill: 'tools', saw: 'tools', hammer: 'tools', wrench: 'tools',
    tent: 'outdoors', kayak: 'outdoors', camp: 'outdoors', hike: 'outdoors',
  };

  $('#hero-search').on('input', function () {
    const val = $(this).val().toLowerCase().trim();
    $('.cat-card').removeClass('cat-highlighted');

    if (!val) return;

    let matched = null;
    Object.entries(categoryMap).forEach(([keyword, cat]) => {
      if (val.includes(keyword)) matched = cat;
    });

    if (matched) {
      $(`.cat-card[data-category="${matched}"]`).addClass('cat-highlighted');
    }
  });

  $('#hero-search-btn').on('click', function () {
    const val = $('#hero-search').val().trim();
    if (!val) return;
    showToast(`🔍 Searching for "${val}"…`, 'info');
    // In a real app: window.location = `explore.html?q=${encodeURIComponent(val)}`;
  });

  // Also trigger on Enter
  $('#hero-search').on('keydown', function (e) {
    if (e.key === 'Enter') $('#hero-search-btn').trigger('click');
  });


  /* Theme handled by theme.js */


  /* ─────────────────────────────────────────────
     7. BACK-TO-TOP BUTTON
  ───────────────────────────────────────────── */
  const $btt = $('#back-to-top');
  $(window).on('scroll.btt', function () {
    if ($(this).scrollTop() > 300) $btt.addClass('btt-visible');
    else $btt.removeClass('btt-visible');
  });
  $btt.on('click', function () {
    $('html, body').animate({ scrollTop: 0 }, 500, 'swing');
  });


  /* ─────────────────────────────────────────────
     8. STATS COUNTER ANIMATION
  ───────────────────────────────────────────── */
  let statsAnimated = false;
  function animateCounters() {
    if (statsAnimated) return;
    statsAnimated = true;
    $('.stat-number').each(function () {
      const $el  = $(this);
      const end  = parseInt($el.attr('data-target'), 10);
      const dur  = 1800;
      const step = Math.ceil(end / (dur / 16));
      let current = 0;
      const timer = setInterval(function () {
        current = Math.min(current + step, end);
        $el.text(current.toLocaleString());
        if (current >= end) clearInterval(timer);
      }, 16);
    });
  }

  if ('IntersectionObserver' in window) {
    const statsObs = new IntersectionObserver((entries) => {
      if (entries[0].isIntersecting) animateCounters();
    }, { threshold: 0.4 });
    const statsEl = document.getElementById('stats-section');
    if (statsEl) statsObs.observe(statsEl);
  }


  /* ─────────────────────────────────────────────
     9. TOAST NOTIFICATION
  ───────────────────────────────────────────── */
  // "List Yours" button — prompt to sign in
  $(document).on('click', 'a[href="login.html"].btn-secondary', function (e) {
    e.preventDefault();
    showToast('👋 Sign in to list your items and start earning!', 'info', 4000);
  });

  function showToast(msg, type = 'info', duration = 3000) {
    const $toast = $(`<div class="toast toast-${type}">${msg}</div>`);
    $('#toast-container').append($toast);
    setTimeout(() => $toast.addClass('toast-visible'), 10);
    setTimeout(() => {
      $toast.removeClass('toast-visible');
      setTimeout(() => $toast.remove(), 400);
    }, duration);
  }

  // Expose showToast globally for other pages
  window.showToast = showToast;


  /* ─────────────────────────────────────────────
     10. PARALLAX — hero image on scroll
  ───────────────────────────────────────────── */
  const $heroImg = $('.hero-visual img');
  if ($heroImg.length && window.innerWidth > 900) {
    $(window).on('scroll.parallax', function () {
      const scrollY = $(this).scrollTop();
      $heroImg.css('transform', `translateY(${scrollY * 0.12}px)`);
    });
  }


  /* ─────────────────────────────────────────────
     11. MOBILE HAMBURGER MENU
  ───────────────────────────────────────────── */
  $('#hamburger').on('click', function () {
    $(this).toggleClass('open');
    $('#mobile-menu').toggleClass('menu-open');
    $('body').toggleClass('no-scroll');
  });

  // Close menu when a link inside it is clicked
  $('#mobile-menu a').on('click', function () {
    $('#hamburger').removeClass('open');
    $('#mobile-menu').removeClass('menu-open');
    $('body').removeClass('no-scroll');
  });

  // Close on outside click
  $(document).on('click', function (e) {
    if (!$(e.target).closest('#hamburger, #mobile-menu').length) {
      $('#hamburger').removeClass('open');
      $('#mobile-menu').removeClass('menu-open');
      $('body').removeClass('no-scroll');
    }
  });


  /* ─────────────────────────────────────────────
     12. CATEGORY ACTIVE STATE — hash routing feel
  ───────────────────────────────────────────── */
  // Highlight cat card whose data-category matches URL hash
  function syncCategoryHash() {
    const hash = window.location.hash.replace('#', '');
    $('.cat-card').removeClass('cat-active');
    if (hash) $(`.cat-card[data-category="${hash}"]`).addClass('cat-active');
  }
  syncCategoryHash();

  $(window).on('hashchange', syncCategoryHash);

  $('.cat-card').on('click', function (e) {
    e.preventDefault();
    const cat = $(this).data('category');
    history.pushState(null, '', `#${cat}`);
    $('.cat-card').removeClass('cat-active');
    $(this).addClass('cat-active');
    showToast(`Browsing ${$(this).find('h3').text()}…`, 'info', 2000);
  });

});