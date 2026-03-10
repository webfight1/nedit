document.addEventListener('DOMContentLoaded', function () {
  const ajaxUrl = (window.NaileditSettings && window.NaileditSettings.ajaxUrl) || '';

	// Simple global toast helper for success/error messages
	function naileditShowToast(message, type) {
		try {
			let container = document.getElementById('nailedit-toast-container');
			if (!container) {
				container = document.createElement('div');
				container.id = 'nailedit-toast-container';
				container.style.position = 'fixed';
				container.style.bottom = '1rem';
				container.style.right = '1rem';
				container.style.zIndex = '9999';
				container.style.display = 'flex';
				container.style.flexDirection = 'column';
				container.style.gap = '0.5rem';
				document.body.appendChild(container);
			}

			const toast = document.createElement('div');
			toast.className = 'nailedit-toast';
			toast.style.padding = '0.5rem 0.75rem';
			toast.style.borderRadius = '9999px';
			toast.style.fontSize = '0.875rem';
			toast.style.color = '#0f172a';
			toast.style.boxShadow = '0 10px 15px -3px rgba(15,23,42,0.1), 0 4px 6px -4px rgba(15,23,42,0.1)';
			toast.style.backgroundColor = type === 'error' ? '#fee2e2' : '#bbf7d0';
			toast.textContent = message;

			container.appendChild(toast);

			setTimeout(function () {
				if (toast && toast.parentNode === container) {
					container.removeChild(toast);
				}
			}, 3000);
		} catch (e) {}
	}

	window.naileditShowToast = naileditShowToast;

  function getStoredCustomer() {
    try {
      const stored = localStorage.getItem('nailedit_customer');
      return stored ? JSON.parse(stored) : null;
    } catch (e) {
      return null;
    }
  }

  // HEADER LOGIN + DROPDOWN
  (function initHeaderLogin() {
    const form = document.getElementById('nailedit-login-form');
    const statusEl = document.getElementById('nailedit-login-status');
    const errorEl = document.getElementById('nailedit-login-error');
    const logoutBtn = document.getElementById('nailedit-logout-btn');
    const forgotForm = document.getElementById('nailedit-forgot-form');
    const forgotMsg = document.getElementById('nailedit-forgot-message');
    const forgotToggle = document.getElementById('nailedit-forgot-toggle');
    const forgotBack = document.getElementById('nailedit-forgot-back');
    const userMenuWrap = document.getElementById('nailedit-user-menu-wrap');
    const loginWrap = document.getElementById('nailedit-login-wrap');
    const registerLink = document.getElementById('nailedit-register-link');
    const forgotWrapper = document.getElementById('nailedit-forgot-wrapper');

    function setLoggedIn(user) {
      if (!statusEl || !form) return;
      const name = (user && (user.first_name || user.name)) ? (user.first_name || user.name) : '';
      statusEl.textContent = name ? ('Tere, ' + name) : 'Oled sisse logitud.';
      statusEl.style.display = 'block';
      form.style.display = 'none';
      if (logoutBtn) {
        logoutBtn.style.display = 'inline-block';
      }
      if (errorEl) {
        errorEl.textContent = '';
      }
      // Keep login wrapper visible so that logout button and status are shown
      if (loginWrap) {
        loginWrap.style.display = '';
      }
      if (registerLink) {
        registerLink.style.display = 'none';
      }
      if (forgotWrapper) {
        forgotWrapper.style.display = 'none';
      }
      if (userMenuWrap) {
        userMenuWrap.classList.remove('hidden');
      }
    }

    function setLoggedOut() {
      if (statusEl) {
        statusEl.style.display = 'none';
        statusEl.textContent = '';
      }
      if (form) {
        form.style.display = '';
      }
      if (logoutBtn) {
        logoutBtn.style.display = 'none';
      }
      if (userMenuWrap) {
        userMenuWrap.classList.add('hidden');
      }
      if (loginWrap) {
        loginWrap.style.display = '';
      }
      if (registerLink) {
        registerLink.style.display = '';
      }
      if (forgotWrapper) {
        forgotWrapper.style.display = '';
      }
    }

    // Init from localStorage and verify against API
    (function initCustomerStatus() {
      let storedUser = null;
      try {
        const stored = localStorage.getItem('nailedit_customer');
        if (stored) {
          storedUser = JSON.parse(stored);
          setLoggedIn(storedUser);
        }
      } catch (e) {}

      if (!ajaxUrl) {
        return;
      }

      let authToken = '';
      let storedCookie = '';

      try {
        authToken = localStorage.getItem('bagisto_auth_token') || '';
      } catch (e) {}

      try {
        storedCookie = localStorage.getItem('bagisto_auth_cookie') || '';
      } catch (e) {}

      if (!authToken && !storedCookie) {
        if (!storedUser) {
          setLoggedOut();
        }
        return;
      }

      const formData = new FormData();
      formData.append('action', 'nailedit_customer_get');
      if (authToken) {
        formData.append('auth_token', authToken);
      }
      if (storedCookie) {
        formData.append('stored_cookie', storedCookie);
      }

      fetch(ajaxUrl, {
        method: 'POST',
        body: formData,
      })
        .then((response) => response.json())
        .then((result) => {
          if (!result || !result.success) {
            try {
              localStorage.removeItem('nailedit_customer');
              localStorage.removeItem('bagisto_auth_token');
              localStorage.removeItem('bagisto_auth_cookie');
            } catch (e) {}
            setLoggedOut();
            return;
          }

          const payload = result.data || {};
          const user = payload.data || payload;

          try {
            localStorage.setItem('nailedit_customer', JSON.stringify(user));
          } catch (e) {}

          setLoggedIn(user);
        })
        .catch(() => {})
        .finally(() => {});
    })();

    if (logoutBtn) {
      logoutBtn.addEventListener('click', function () {
        if (!ajaxUrl) {
          try {
            localStorage.removeItem('nailedit_customer');
            localStorage.removeItem('bagisto_auth_token');
            localStorage.removeItem('bagisto_auth_cookie');
          } catch (e) {}
          setLoggedOut();
          return;
        }

        let authToken = '';
        let storedCookie = '';

        try {
          authToken = localStorage.getItem('bagisto_auth_token') || '';
        } catch (e) {}

        try {
          storedCookie = localStorage.getItem('bagisto_auth_cookie') || '';
        } catch (e) {}

        const formData = new FormData();
        formData.append('action', 'nailedit_customer_logout');
        if (authToken) {
          formData.append('auth_token', authToken);
        }
        if (storedCookie) {
          formData.append('stored_cookie', storedCookie);
        }

        fetch(ajaxUrl, {
          method: 'POST',
          body: formData,
        })
          .then(() => {
            // ignore body, just clear state client-side
          })
          .catch(() => {})
          .finally(() => {
            try {
              localStorage.removeItem('nailedit_customer');
              localStorage.removeItem('bagisto_auth_token');
              localStorage.removeItem('bagisto_auth_cookie');
            } catch (e) {}
            setLoggedOut();
          });
      });
    }

    if (forgotToggle && forgotForm) {
      forgotToggle.addEventListener('click', function () {
        const isHidden = forgotForm.classList.contains('hidden');

        // Toggle visibility of forgot form
        forgotForm.classList.toggle('hidden');

        // When showing forgot form, hide login fields and register link
        if (isHidden) {
          if (form) {
            form.style.display = 'none';
          }
          if (registerLink) {
            registerLink.style.display = 'none';
          }
          // Hide the "Unustasid parooli?" toggle while on forgot view
          if (forgotToggle) {
            forgotToggle.style.display = 'none';
          }
        } else {
          // When hiding forgot form, show login fields and register link again
          if (form) {
            form.style.display = '';
          }
          if (registerLink) {
            registerLink.style.display = '';
          }
          // Show the toggle again when returning to login view
          if (forgotToggle) {
            forgotToggle.style.display = 'inline-block';
          }
        }
      });
    }

    if (forgotBack && forgotForm) {
      forgotBack.addEventListener('click', function () {
        // Always hide forgot form and show login & register again
        if (!forgotForm.classList.contains('hidden')) {
          forgotForm.classList.add('hidden');
        }
        if (form) {
          form.style.display = '';
        }
        if (registerLink) {
          registerLink.style.display = '';
        }
        if (forgotToggle) {
          forgotToggle.style.display = 'inline-block';
        }
      });
    }

    if (forgotForm && forgotMsg && ajaxUrl) {
      forgotForm.addEventListener('submit', function (e) {
        e.preventDefault();

        forgotMsg.textContent = '';

        const formData = new FormData(forgotForm);
        formData.append('action', 'nailedit_customer_forgot_password');

        const submitBtn = forgotForm.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Saatmine...';
        }

        fetch(ajaxUrl, {
          method: 'POST',
          body: formData,
        })
          .then((response) => response.json())
          .then((result) => {
            const ok = result && result.success;
            const msg = (result && result.message)
              ? result.message
              : ok
              ? 'Kui email eksisteerib, saadeti parooli taastamise kiri.'
              : 'Parooli taastamine ebaõnnestus.';
            forgotMsg.textContent = msg;
          })
          .catch((err) => {
            console.error('Forgot password error:', err);
            forgotMsg.textContent = 'Tekkis ootamatu viga.';
          })
          .finally(() => {
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.textContent = 'Saada taastamise link';
            }
          });
      });
    }

    if (form && ajaxUrl) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (errorEl) {
          errorEl.textContent = '';
        }

        const formData = new FormData(form);
        formData.append('action', 'nailedit_customer_login');
        formData.append('accept_token', '1');

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Sisselogimine...';
        }

        fetch(ajaxUrl, {
          method: 'POST',
          body: formData,
        })
          .then((response) => response.json())
          .then((result) => {
            const ok = result && result.success;

            if (!ok) {
              const msg =
                (result && (result.message || (result.data && result.data.message))) ||
                'Invalid Email or Password';
              if (errorEl) {
                errorEl.textContent = msg;
              }
              throw new Error(msg);
            }

            const payload = result.data || {};
            const user = payload.data || payload;

            if (result.cookies) {
              const cookieStr = Array.isArray(result.cookies)
                ? result.cookies.join('; ')
                : result.cookies;
              if (cookieStr) {
                try {
                  localStorage.setItem('bagisto_auth_cookie', cookieStr);
                } catch (e) {}
              }
            }

            try {
              const token = payload.token || (payload.data && payload.data.token) || null;
              if (token) {
                localStorage.setItem('bagisto_auth_token', token);
              }
            } catch (e) {}

            try {
              localStorage.setItem('nailedit_customer', JSON.stringify(user));
            } catch (e) {}

            setLoggedIn(user);
          })
          .catch((err) => {
            console.error('Login error:', err);
          })
          .finally(() => {
            if (submitBtn) {
              submitBtn.disabled = false;
              submitBtn.textContent = 'Logi sisse';
            }
          });
      });
    }

    // User dropdown toggle
    const userToggle = document.getElementById('nailedit-user-toggle');
    const userDropdown = document.getElementById('nailedit-user-dropdown');

    if (userToggle && userDropdown) {
      userToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        userDropdown.classList.toggle('hidden');
      });

      userDropdown.addEventListener('click', function (e) {
        e.stopPropagation();
      });

      document.addEventListener('click', function () {
        if (!userDropdown.classList.contains('hidden')) {
          userDropdown.classList.add('hidden');
        }
      });
    }
  })();

  // HEADER LIVE PRODUCT SEARCH
  (function initHeaderSearch() {
    const toggle = document.getElementById('nailedit-search-toggle');
    const panel = document.getElementById('nailedit-search-panel');
    const input = document.getElementById('nailedit-search-input');
    const results = document.getElementById('nailedit-search-results');
    if (!toggle || !panel || !input || !results || !ajaxUrl) {
      return;
    }

    function openPanel() {
      panel.classList.remove('hidden');
      try {
        input.focus();
      } catch (e) {}
    }

    function closePanel() {
      panel.classList.add('hidden');
    }

    toggle.addEventListener('click', function (e) {
      e.stopPropagation();
      if (panel.classList.contains('hidden')) {
        openPanel();
      } else {
        closePanel();
      }
    });

    panel.addEventListener('click', function (e) {
      e.stopPropagation();
    });

    document.addEventListener('click', function () {
      if (!panel.classList.contains('hidden')) {
        closePanel();
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closePanel();
      }
    });

    let searchTimeout = null;

    function renderResults(items) {
      if (!items || !items.length) {
        results.innerHTML = '<p class="text-xs text-slate-500 px-2 py-1">Tulemusi ei leitud.</p>';
        return;
      }

      const parts = items.map(function (item) {
        const title = item.title || '';
        const url = item.url || '#';
        const price = item.price || '';
        const image = item.image || '';
        let imgHtml = '';
        if (image) {
          imgHtml =
            '<div class="w-10 h-10 rounded-lg overflow-hidden bg-slate-100 flex-shrink-0 mr-3">' +
            '<img src="' + image.replace(/"/g, '&quot;') + '" alt="" class="w-full h-full object-cover" />' +
            '</div>';
        }

        return (
          '<a href="' + url + '" class="flex items-center px-2 py-1.5 rounded-lg hover:bg-slate-100 transition">' +
          imgHtml +
          '<div class="min-w-0">' +
          '<div class="text-[13px] font-medium text-slate-900 truncate">' + title + '</div>' +
          (price ? '<div class="text-[12px] text-secondary mt-0.5">' + price + '</div>' : '') +
          '</div>' +
          '</a>'
        );
      });

      results.innerHTML = parts.join('');
    }

    function performSearch(term) {
      term = (term || '').trim();
      if (!term) {
        results.innerHTML = '';
        return;
      }
      if (!ajaxUrl) {
        return;
      }

      results.innerHTML = '<p class="text-xs text-slate-500 px-2 py-1">Otsin...</p>';

      const formData = new FormData();
      formData.append('action', 'nailedit_search_products');
      formData.append('q', term);

      fetch(ajaxUrl, {
        method: 'POST',
        body: formData,
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (result) {
          if (!result || !result.success || !result.data) {
            results.innerHTML = '<p class="text-xs text-red-500 px-2 py-1">Otsing ebaõnnestus.</p>';
            return;
          }
          renderResults(result.data.results || []);
        })
        .catch(function () {
          results.innerHTML = '<p class="text-xs text-red-500 px-2 py-1">Otsing ebaõnnestus.</p>';
        });
    }

    input.addEventListener('input', function () {
      const term = this.value || '';
      if (searchTimeout) {
        clearTimeout(searchTimeout);
      }
      if (term.trim().length < 2) {
        results.innerHTML = '';
        return;
      }
      searchTimeout = setTimeout(function () {
        performSearch(term);
      }, 300);
    });
  })();

  // CATEGORY FILTER FORM: build clean URL with single "price" param (price=min,max) and literal comma
  (function initCategoryFilters() {
    var filterForms = document.querySelectorAll('form.nailedit-filters');
    if (!filterForms.length) return;

    filterForms.forEach(function (form) {
      form.addEventListener('submit', function (e) {
        if (!form.action) {
          return;
        }
        e.preventDefault();

        var minInput = form.querySelector('input[name="price_min"]');
        var maxInput = form.querySelector('input[name="price_max"]');
        var brandInput = form.querySelector('input[name="brand"]');
        var colorInput = form.querySelector('input[name="color"]');
        var sizeInput  = form.querySelector('input[name="size"]');

        var parts = [];

        // price => single param price=min,max with 0 fallback (comma stays literal)
        var minVal = minInput && minInput.value !== '' ? minInput.value : '';
        var maxVal = maxInput && maxInput.value !== '' ? maxInput.value : '';
        if (minVal || maxVal) {
          var pMin = minVal || '0';
          var pMax = maxVal || '0';
          parts.push('price=' + encodeURIComponent(pMin) + ',' + encodeURIComponent(pMax));
        }

        // non-empty filters from hidden inputs (custom selects)
        if (brandInput && brandInput.value) {
          parts.push('brand=' + encodeURIComponent(brandInput.value));
        }
        if (colorInput && colorInput.value) {
          parts.push('color=' + encodeURIComponent(colorInput.value));
        }
        if (sizeInput && sizeInput.value) {
          parts.push('size=' + encodeURIComponent(sizeInput.value));
        }

        var url = form.action;
        if (parts.length) {
          url += (url.indexOf('?') === -1 ? '?' : '&') + parts.join('&');
        }

        window.location.href = url;
      });
    });
  })();

  // PRICE RANGE SLIDER (category filters)
  (function initPriceRange() {
    var wrap = document.getElementById('nailedit-price-range');
    if (!wrap) return;

    var minInput = document.getElementById('nailedit-range-min');
    var maxInput = document.getElementById('nailedit-range-max');
    var minLabel = document.getElementById('nailedit-price-min-label');
    var maxLabel = document.getElementById('nailedit-price-max-label');
    if (!minInput || !maxInput || !minLabel || !maxLabel) return;

    function syncLabels() {
      var minVal = parseInt(minInput.value || '0', 10);
      var maxVal = parseInt(maxInput.value || '0', 10);
      if (minVal > maxVal) {
        // keep sliders consistent: if crossing, snap them together
        if (this === minInput) {
          maxVal = minVal;
          maxInput.value = String(maxVal);
        } else {
          minVal = maxVal;
          minInput.value = String(minVal);
        }
      }
      minLabel.textContent = minVal.toFixed(2).replace('.', ',');
      maxLabel.textContent = maxVal.toFixed(2).replace('.', ',');
    }

    minInput.addEventListener('input', syncLabels);
    maxInput.addEventListener('input', syncLabels);
    syncLabels();
  })();

  // CUSTOM SELECTS (category filters)
  (function initNaileditCustomSelects() {
    var selects = document.querySelectorAll('.nailedit-custom-select');
    if (!selects.length) return;

    function closeAll(except) {
      selects.forEach(function (wrap) {
        if (wrap !== except) {
          var dd = wrap.querySelector('.nailedit-custom-select-dropdown');
          if (dd) {
            dd.classList.add('hidden');
          }
        }
      });
    }

    selects.forEach(function (wrap) {
      var button = wrap.querySelector('.nailedit-custom-select-trigger');
      var dropdown = wrap.querySelector('.nailedit-custom-select-dropdown');
      var label = wrap.querySelector('.nailedit-custom-select-label');
      var hiddenInput = wrap.querySelector('input[type="hidden"]');
      if (!button || !dropdown || !label || !hiddenInput) return;

      button.addEventListener('click', function (e) {
        e.stopPropagation();
        var isHidden = dropdown.classList.contains('hidden');
        closeAll(wrap);
        if (isHidden) {
          dropdown.classList.remove('hidden');
        } else {
          dropdown.classList.add('hidden');
        }
      });

      dropdown.querySelectorAll('button[data-value]').forEach(function (optBtn) {
        optBtn.addEventListener('click', function (e) {
          e.stopPropagation();
          var val = optBtn.getAttribute('data-value') || '';
          var text = optBtn.textContent || '';
          hiddenInput.value = val;
          label.textContent = text;
          dropdown.classList.add('hidden');
        });
      });
    });

    document.addEventListener('click', function () {
      closeAll(null);
    });
  })();

  // SCROLL TO TOP BUTTON
  (function initScrollTopButton() {
    var btn = document.getElementById('nailedit-scroll-top');
    if (!btn) return;

    function toggleVisibility() {
      if (window.scrollY > 300) {
        btn.classList.remove('hidden');
      } else {
        btn.classList.add('hidden');
      }
    }

    window.addEventListener('scroll', toggleVisibility, { passive: true });
    toggleVisibility();

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  })();

  // QUANTITY SELECTOR (+ / -)
  (function initQuantitySelector() {
    const input = document.querySelector('input.qty-input');
    const minus = document.querySelector('.qty-minus');
    const plus = document.querySelector('.qty-plus');

    if (!input || !minus || !plus) {
      console.log('Quantity selector elements not found');
      return;
    }

    console.log('Quantity selector initialized');

  
  	minus.addEventListener('click', function (e) {
      e.preventDefault();
      let val = parseInt(input.value || '1', 10);
      const min = parseInt(input.getAttribute('min') || '1', 10);
      if (val > min) {
        input.value = val - 1;
      }
    });

    plus.addEventListener('click', function (e) {
      e.preventDefault();
      let val = parseInt(input.value || '1', 10);
      const step = parseInt(input.getAttribute('step') || '1', 10);
      input.value = val + step;
    	});
  })();

	// PRODUCT REVIEW SECTION
	(function initReviewSection() {
		const reviewText = document.getElementById('nailedit-review-text');
		const reviewBtn = document.getElementById('nailedit-review-submit');
		const reviewMsg = document.getElementById('nailedit-review-message');
		const addToCartBtn = document.getElementById('add-to-cart-btn');
		const starRatingContainer = document.getElementById('nailedit-star-rating');
		const reviewList = document.getElementById('nailedit-review-list');

		if (!reviewText || !reviewBtn || !reviewMsg || !ajaxUrl) {
			return;
		}

		function getProductId() {
			if (addToCartBtn && addToCartBtn.dataset && addToCartBtn.dataset.productId) {
				return addToCartBtn.dataset.productId;
			}
			return '';
		}

		function renderReviews(productId, payload) {
			if (!reviewList || !payload || !payload.data) {
				return;
			}

			// Bagisto response (after WP proxy) looks like:
			// { data: { data: [ { id, name, title, comment, rating, ... }, ... ], links: {...}, meta: {...} }, status: 200, success: true }
			// Our PHP handler passes only the inner Bagisto JSON as payload, so here:
			// payload = { data: [ ... ], links: {...}, meta: {...} }
			// So the actual reviews array is payload.data
			const items = Array.isArray(payload.data) ? payload.data : [];

			console.log('REVIEW LIST DEBUG: renderReviews()', {
				productId: productId,
				itemsCount: items.length,
				items: items,
			});

			// Update small review summary (top-right on product card)
			(function updateSummary() {
				var summaryEl = document.getElementById('nailedit-review-summary');
				var starsEl = document.getElementById('nailedit-review-summary-stars');
				var textEl = document.getElementById('nailedit-review-summary-text');

				if (!summaryEl || !starsEl || !textEl) {
					return;
				}

				if (!items.length) {
					starsEl.textContent = '☆☆☆☆☆';
					textEl.textContent = (typeof NaileditSettings !== 'undefined' && NaileditSettings.translations && NaileditSettings.translations.no_ratings_yet) 
						? NaileditSettings.translations.no_ratings_yet 
						: 'Pole veel hinnanguid';
					return;
				}

				var total = 0;
				var count = 0;
				items.forEach(function (item) {
					var r = parseInt(item.rating, 10);
					if (!isNaN(r) && r > 0) {
						total += r;
						count += 1;
					}
				});

				if (!count) {
					starsEl.textContent = '☆☆☆☆☆';
					textEl.textContent = 'Pole veel hinnanguid';
					return;
				}

				var avg = total / count;
				var rounded = Math.round(avg * 10) / 10; // one decimal
				var fullStars = Math.max(0, Math.min(5, Math.round(avg)));
				var starStr = '';
				for (var i = 0; i < 5; i++) {
					starStr += i < fullStars ? '★' : '☆';
				}
				starsEl.textContent = starStr;
				textEl.textContent = rounded.toString().replace('.', ',') + ' (' + count + ' hinnangut)';
			})();

			if (!items.length) {
				var noReviewsText = (typeof NaileditSettings !== 'undefined' && NaileditSettings.translations && NaileditSettings.translations.no_reviews_yet) 
					? NaileditSettings.translations.no_reviews_yet 
					: 'Hetkel pole sellel tootel veel arvustusi.';
				reviewList.innerHTML = '<p>' + noReviewsText + '</p>';
				return;
			}

			const parts = items.map(function (item) {
				const name = item.name || '';
				const title = item.title || '';
				const comment = item.comment || '';
				const rating = item.rating || 0;
				const date = item.created_at || '';

				const fullStars = Math.max(0, Math.min(5, parseInt(rating, 10) || 0));
				let stars = '';
				for (let i = 0; i < 5; i++) {
					stars += i < fullStars ? '★' : '☆';
				}

				return (
					'<article class=" rounded-2xl p-3  ">' +
						'<div class="flex items-center justify-between mb-1">' +
							'<div class="font-semibold text-[13px] text-slate-900">' + (name ? name : '') + '</div>' +
							'<div class="text-[11px] text-slate-500">' + (date ? date : '') + '</div>' +
						'</div>' +
						'<div class="text-[#f5b300] text-[13px] mb-1">' + stars + '</div>' +
						(title ? '<div class="font-medium text-[13px] text-slate-900 mb-1">' + title + '</div>' : '') +
						(comment ? '<div class="text-[13px] text-slate-700">' + comment + '</div>' : '') +
					'</article>'
				);
			});

			reviewList.innerHTML = parts.join('');
		}

		function fetchReviews(productId) {
			if (!reviewList || !ajaxUrl || !productId) {
				return;
			}

			let authToken = '';
			let storedCookie = '';
			try {
				authToken = localStorage.getItem('bagisto_auth_token') || '';
			} catch (e) {}
			try {
				storedCookie = localStorage.getItem('bagisto_auth_cookie') || '';
			} catch (e) {}

			reviewList.innerHTML = '<p class="text-primary">Arvustusi laaditakse...</p>';
			console.log('REVIEW LIST DEBUG: fetchReviews() start', {
				productId: productId,
				ajaxUrl: ajaxUrl,
				authTokenPresent: !!authToken,
				storedCookiePresent: !!storedCookie,
			});

			const formData = new FormData();
			formData.append('action', 'nailedit_get_product_reviews');
			formData.append('product_id', String(productId));
			if (authToken) {
				formData.append('auth_token', authToken);
			}
			if (storedCookie) {
				formData.append('stored_cookie', storedCookie);
			}

			fetch(ajaxUrl, {
				method: 'POST',
				body: formData,
			})
				.then(function (response) {
					console.log('REVIEW LIST DEBUG: fetchReviews() HTTP status', response.status);
					return response.json();
				})
				.then(function (result) {
					console.log('REVIEW LIST DEBUG: fetchReviews() JSON result', result);
					if (!result || !result.success) {
						console.error('Get reviews error:', result);
						reviewList.innerHTML = '<p class="text-red-500 text-[13px]">Arvustusi ei õnnestunud laadida.</p>';
						return;
					}
					renderReviews(productId, result.data);
				})
				.catch(function (error) {
					console.error('Get reviews fetch error:', error);
					reviewList.innerHTML = '<p class="text-red-500 text-[13px]">Arvustusi ei õnnestunud laadida.</p>';
				});
		}

		// Initial fetch of existing reviews on page load
		(function initReviewsOnLoad() {
			const productId = getProductId();
			if (productId) {
				fetchReviews(productId);
			}
		})();

		// Star rating functionality
		if (starRatingContainer) {
			const stars = starRatingContainer.querySelectorAll('.nailedit-star');
			
			function updateStars(rating) {
				stars.forEach(function(star, index) {
					if (index < rating) {
						star.style.color = '#FFD700'; // gold
					} else {
						star.style.color = '#D1D5DB'; // gray
					}
				});
				starRatingContainer.setAttribute('data-rating', rating);
			}

			// Initialize with 5 stars
			updateStars(5);

			// Click handler
			stars.forEach(function(star) {
				star.addEventListener('click', function() {
					const value = parseInt(this.getAttribute('data-value'), 10);
					updateStars(value);
				});

				// Hover effect
				star.addEventListener('mouseenter', function() {
					const value = parseInt(this.getAttribute('data-value'), 10);
					stars.forEach(function(s, index) {
						if (index < value) {
							s.style.color = '#FDB022'; // lighter gold on hover
						} else {
							s.style.color = '#D1D5DB';
						}
					});
				});
			});

			// Reset to selected rating on mouse leave
			starRatingContainer.addEventListener('mouseleave', function() {
				const currentRating = parseInt(starRatingContainer.getAttribute('data-rating'), 10);
				updateStars(currentRating);
			});
		}

		reviewBtn.addEventListener('click', function () {
			if (reviewMsg) {
				reviewMsg.textContent = '';
				reviewMsg.style.color = '';
			}

			const text = (reviewText.value || '').trim();
			if (!text) {
				if (reviewMsg) {
					reviewMsg.textContent = 'Palun kirjuta enne arvustus.';
					reviewMsg.style.color = 'red';
				}
				return;
			}

			const customer = getStoredCustomer();
			if (!customer) {
				if (reviewMsg) {
					reviewMsg.textContent = 'Arvustuse jätmiseks palun logi sisse.';
					reviewMsg.style.color = 'red';
				}
				return;
			}

			const customerId = customer.id || customer.customer_id || '';
			const name = customer.first_name || customer.name || '';
			const productId = getProductId();

			if (!customerId || !productId) {
				if (reviewMsg) {
					reviewMsg.textContent = 'Arvustust ei saa hetkel saata.';
					reviewMsg.style.color = 'red';
				}
				return;
			}

			// Get selected rating
			let rating = 5;
			if (starRatingContainer) {
				rating = parseInt(starRatingContainer.getAttribute('data-rating'), 10) || 5;
			}

			let authToken = '';
			let storedCookie = '';
			try {
				authToken = localStorage.getItem('bagisto_auth_token') || '';
			} catch (e) {}
			try {
				storedCookie = localStorage.getItem('bagisto_auth_cookie') || '';
			} catch (e) {}

			console.log('=== REVIEW JS DEBUG ===');
			console.log('Customer ID:', customerId);
			console.log('Product ID:', productId);
			console.log('Review text:', text.substring(0, 50));
			console.log('Rating:', rating);
			console.log('Auth token present:', !!authToken);
			console.log('Stored cookie present:', !!storedCookie);
			if (authToken) {
				console.log('Auth token (first 20 chars):', authToken.substring(0, 20));
			}
			if (storedCookie) {
				console.log('Stored cookie (first 50 chars):', storedCookie.substring(0, 50));
			}

			const formData = new FormData();
			formData.append('action', 'nailedit_customer_review');
			formData.append('customer_id', String(customerId));
			formData.append('name', name);
			formData.append('status', 'pending');
			formData.append('review', text);
			formData.append('product_id', String(productId));
			formData.append('rating', String(rating));
			if (authToken) {
				formData.append('auth_token', authToken);
			}
			if (storedCookie) {
				formData.append('stored_cookie', storedCookie);
			}

			reviewBtn.disabled = true;
			reviewBtn.textContent = 'Saatmine...';

			fetch(ajaxUrl, {
				method: 'POST',
				body: formData,
			})
				.then(async function (response) {
					const status = response.status;
					const textBody = await response.text();
					let result = null;
					try {
						result = textBody ? JSON.parse(textBody) : null;
					} catch (e) {}

					if (!response.ok || !result || !result.success) {
						console.error('Review API error:', {
							status: status,
							body: textBody,
						});
						const msg =
							(result && (result.message || (result.data && result.data.message))) ||
							'Tekkis viga arvustuse salvestamisel.';
						throw new Error(msg);
					}

					if (reviewMsg) {
						reviewMsg.textContent = '';
						reviewMsg.style.color = '';
					}
					if (typeof window.naileditShowToast === 'function') {
						window.naileditShowToast('Aitäh, sinu arvustus on saadetud.', 'success');
					}
					reviewText.value = '';
					if (productId) {
						fetchReviews(productId);
					}
				})
				.catch(function (error) {
					if (reviewMsg) {
						reviewMsg.textContent = error.message || 'Tekkis ootamatu viga.';
						reviewMsg.style.color = 'red';
					}
				})
				.finally(function () {
					reviewBtn.disabled = false;
					reviewBtn.textContent = 'Saada arvustus';
				});
		});
	})();
});
