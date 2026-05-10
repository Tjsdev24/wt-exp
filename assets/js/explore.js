/* ============================================================
   RentIt — explore.js
   All features: jQuery + vanilla JS, no database required
   ============================================================ */

$(function () {

  /* ─────────────────────────────────────────────
     PRODUCT DATA — mirrors the HTML cards
     Each card in .grid gets a data-* attribute set;
     we read those here for client-side filtering.
  ───────────────────────────────────────────── */
  // Data is read directly from the DOM (data-* attrs set in HTML)


  /* ─────────────────────────────────────────────
     SHARED STATE
  ───────────────────────────────────────────── */
  const state = {
    query:      '',
    categories: [], // empty = all
    maxPrice:   10000,
    sort:       'default',
    wishlist:   JSON.parse(localStorage.getItem('rentit-wishlist') || '[]'),
  };


  /* ─────────────────────────────────────────────
     7. SKELETON LOADING — shimmer on page load
  ───────────────────────────────────────────── */
  const $grid   = $('.grid');
  const $cards  = $grid.find('.soft-product-card');
  $cards.addClass('card-hidden');

  // Inject skeletons
  for (let i = 0; i < $cards.length; i++) {
    $grid.prepend(`
      <div class="skeleton-card">
        <div class="skel skel-img"></div>
        <div class="skel skel-title"></div>
        <div class="skel skel-sub"></div>
      </div>
    `);
  }

  setTimeout(() => {
    $('.skeleton-card').remove();
    $cards.removeClass('card-hidden');
    runFilter();        // apply any pre-existing hash filters
    revealCards();      // 10. scroll reveal
  }, 800);


  /* ─────────────────────────────────────────────
     6. WISHLIST — heart button per card
  ───────────────────────────────────────────── */
  $cards.each(function () {
    const id   = $(this).data('id');
    const inWL = state.wishlist.includes(id);
    $(this).find('.card-img-wrap').append(
      `<button class="heart-btn ${inWL ? 'hearted' : ''}"
               data-id="${id}" aria-label="Wishlist">
         ${inWL ? '♥' : '♡'}
       </button>`
    );
  });

  updateWishlistBadge();

  $(document).on('click', '.heart-btn', function (e) {
    e.preventDefault();
    e.stopPropagation();
    const $btn = $(this);
    const id   = $btn.data('id');
    const idx  = state.wishlist.indexOf(id);

    if (idx === -1) {
      state.wishlist.push(id);
      $btn.addClass('hearted').html('♥');
      showToast(' Added to wishlist!', 'success');
    } else {
      state.wishlist.splice(idx, 1);
      $btn.removeClass('hearted').html('♡');
      showToast(' Removed from wishlist', 'info');
    }
    localStorage.setItem('rentit-wishlist', JSON.stringify(state.wishlist));
    updateWishlistBadge();
    $btn.addClass('heart-pop');
    setTimeout(() => $btn.removeClass('heart-pop'), 300);
  });

  function updateWishlistBadge() {
    const count = state.wishlist.length;
    if (count > 0) {
      if ($('#wl-badge').length) {
        $('#wl-badge').text(count);
      } else {
        $('.nav-links').prepend(`<span id="wl-badge">${count}</span>`);
      }
    } else {
      $('#wl-badge').remove();
    }
  }


  /* ─────────────────────────────────────────────
     1. LIVE SEARCH FILTERING
  ───────────────────────────────────────────── */
  $('.search-pill input').on('input', function () {
    state.query = $(this).val().toLowerCase().trim();
    runFilter();
    updateActiveTags();
  });

  $('.search-btn').on('click', function () {
    state.query = $('.search-pill input').val().toLowerCase().trim();
    runFilter();
  });


  /* ─────────────────────────────────────────────
     2. CATEGORY CHECKBOX FILTERING
  ───────────────────────────────────────────── */
  // "All Items" checkbox logic
  $('[data-filter="all"]').on('change', function () {
    if ($(this).is(':checked')) {
      state.categories = [];
      $('[data-filter]').not('[data-filter="all"]').prop('checked', false);
    }
    runFilter();
    updateActiveTags();
  });

  $('[data-filter]').not('[data-filter="all"]').on('change', function () {
    const cat = $(this).data('filter');
    if ($(this).is(':checked')) {
      $('[data-filter="all"]').prop('checked', false);
      state.categories.push(cat);
    } else {
      state.categories = state.categories.filter(c => c !== cat);
      if (state.categories.length === 0) $('[data-filter="all"]').prop('checked', true);
    }
    runFilter();
    updateActiveTags();
  });


  /* ─────────────────────────────────────────────
     3. SORT DROPDOWN
  ───────────────────────────────────────────── */
  $('#sort-select').on('change', function () {
    state.sort = $(this).val();
    runFilter();
  });


  /* ─────────────────────────────────────────────
     8. PRICE RANGE SLIDER
  ───────────────────────────────────────────── */
  $('#price-range').on('input', function () {
    state.maxPrice = parseInt($(this).val(), 10);
    $('#price-label').text('Rs. ' + state.maxPrice + '/d');
    runFilter();
    updateActiveTags();
  });


  /* ─────────────────────────────────────────────
     CORE FILTER + SORT + RENDER ENGINE
  ───────────────────────────────────────────── */
  function runFilter() {
    let $all = $cards.toArray();

    // Filter
    let visible = $all.filter(el => {
      const $el     = $(el);
      const name    = $el.data('name').toLowerCase();
      const cat     = $el.data('category');
      const price   = parseInt($el.data('price'), 10);

      const matchQ   = !state.query || name.includes(state.query);
      const matchCat = state.categories.length === 0 || state.categories.includes(cat);
      const matchP   = price <= state.maxPrice;

      return matchQ && matchCat && matchP;
    });

    // Sort
    if (state.sort === 'price-asc') {
      visible.sort((a, b) => parseInt($(a).data('price')) - parseInt($(b).data('price')));
    } else if (state.sort === 'price-desc') {
      visible.sort((a, b) => parseInt($(b).data('price')) - parseInt($(a).data('price')));
    } else if (state.sort === 'name') {
      visible.sort((a, b) => $(a).data('name').localeCompare($(b).data('name')));
    }

    // Re-render
    $cards.hide();
    $(visible).show();

    // Re-order in DOM to match sort
    visible.forEach(el => $grid.append(el));

    // 9. Empty state
    if (visible.length === 0) {
      if (!$('#empty-state').length) {
        $grid.after(`
          <div id="empty-state">
            <div class="empty-icon">🔍</div>
            <h3>No items found</h3>
            <p>Try adjusting your filters or search term</p>
            <button id="reset-filters" class="btn btn-primary">Reset Filters</button>
          </div>
        `);
      }
    } else {
      $('#empty-state').remove();
    }

    // Re-count results
    $('#result-count').text(`${visible.length} item${visible.length !== 1 ? 's' : ''} found`);
  }


  /* ─────────────────────────────────────────────
     13. ACTIVE FILTER TAGS
  ───────────────────────────────────────────── */
  function updateActiveTags() {
    const $container = $('#active-tags');
    $container.empty();

    if (state.query) {
      $container.append(makeTag(`Search: "${state.query}"`, () => {
        state.query = '';
        $('.search-pill input').val('');
        runFilter();
        updateActiveTags();
      }));
    }

    state.categories.forEach(cat => {
      $container.append(makeTag(cat, () => {
        state.categories = state.categories.filter(c => c !== cat);
        $(`[data-filter="${cat}"]`).prop('checked', false);
        if (state.categories.length === 0) $('[data-filter="all"]').prop('checked', true);
        runFilter();
        updateActiveTags();
      }));
    });

    if (state.maxPrice < 10000) {
      $container.append(makeTag(`Max Rs. ${state.maxPrice}/d`, () => {
        state.maxPrice = 10000;
        $('#price-range').val(10000);
        $('#price-label').text('Rs. 10000/d');
        runFilter();
        updateActiveTags();
      }));
    }
  }

  function makeTag(label, onRemove) {
    const $tag = $(`<span class="filter-tag">${label} <button aria-label="Remove filter">✕</button></span>`);
    $tag.find('button').on('click', onRemove);
    return $tag;
  }


  /* ─────────────────────────────────────────────
     9. RESET FILTERS (empty state button)
  ───────────────────────────────────────────── */
  $(document).on('click', '#reset-filters', function () {
    state.query      = '';
    state.categories = [];
    state.maxPrice   = 10000;
    state.sort       = 'default';
    $('.search-pill input').val('');
    $('[data-filter="all"]').prop('checked', true);
    $('[data-filter]').not('[data-filter="all"]').prop('checked', false);
    $('#price-range').val(10000);
    $('#price-label').text('Rs. 10000/d');
    $('#sort-select').val('default');
    runFilter();
    updateActiveTags();
  });


  /* ─────────────────────────────────────────────
     10. SCROLL REVEAL
  ───────────────────────────────────────────── */
  function revealCards() {
    if (!('IntersectionObserver' in window)) {
      $cards.addClass('card-revealed');
      return;
    }
    const obs = new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          $(e.target).addClass('card-revealed');
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.1 });
    $cards.each(function () { obs.observe(this); });
  }


  /* ─────────────────────────────────────────────
     4. STICKY NAVBAR
  ───────────────────────────────────────────── */
  const $nav = $('.navbar');
  $(window).on('scroll.nav', function () {
    $nav.toggleClass('nav-scrolled', $(this).scrollTop() > 40);
  });


  /* Theme handled by theme.js */


  /* ─────────────────────────────────────────────
     11. MOBILE HAMBURGER
  ───────────────────────────────────────────── */
  $('#hamburger').on('click', function () {
    $(this).toggleClass('open');
    $('#mobile-menu').toggleClass('menu-open');
    $('body').toggleClass('no-scroll');
  });
  $('#mobile-menu a').on('click', function () {
    $('#hamburger').removeClass('open');
    $('#mobile-menu').removeClass('menu-open');
    $('body').removeClass('no-scroll');
  });
  $(document).on('click', function (e) {
    if (!$(e.target).closest('#hamburger, #mobile-menu').length) {
      $('#hamburger').removeClass('open');
      $('#mobile-menu').removeClass('menu-open');
      $('body').removeClass('no-scroll');
    }
  });


  /* ─────────────────────────────────────────────
     12. TOAST
  ───────────────────────────────────────────── */
  function showToast(msg, type = 'info', duration = 2500) {
    const $t = $(`<div class="toast toast-${type}">${msg}</div>`);
    $('#toast-container').append($t);
    setTimeout(() => $t.addClass('toast-visible'), 10);
    setTimeout(() => {
      $t.removeClass('toast-visible');
      setTimeout(() => $t.remove(), 400);
    }, duration);
  }

});