/**
 * FlexFit — Main JS
 * Portfolio
 */

(function () {
  'use strict';

  /* ── Navbar scroll behaviour ─────────────────────────────── */
  const navbar = document.getElementById('navbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 60);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ── Mobile nav ──────────────────────────────────────────── */
  const navToggle    = document.getElementById('navToggle');
  const mobileNav    = document.getElementById('mobileNav');
  const mobileClose  = document.getElementById('mobileNavClose');

  function openMobileNav() {
    mobileNav.classList.add('open');
    document.body.classList.add('no-scroll');
    document.documentElement.classList.add('no-scroll');
    navToggle?.setAttribute('aria-expanded', 'true');
  }
  function closeMobileNav() {
    mobileNav.classList.remove('open');
    document.body.classList.remove('no-scroll');
    document.documentElement.classList.remove('no-scroll');
    navToggle?.setAttribute('aria-expanded', 'false');
  }

  navToggle?.addEventListener('click', openMobileNav);
  mobileClose?.addEventListener('click', closeMobileNav);
  mobileNav?.querySelectorAll('a').forEach(link => link.addEventListener('click', closeMobileNav));
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMobileNav(); });

  /* ── Hero background parallax / loaded state ─────────────── */
  const heroBg = document.querySelector('.hero-bg');
  if (heroBg) {
    // Trigger the CSS scale animation
    requestAnimationFrame(() => heroBg.classList.add('loaded'));

    // Subtle parallax on scroll
    window.addEventListener('scroll', () => {
      const y = window.scrollY;
      if (y < window.innerHeight) {
        heroBg.style.transform = `scale(1) translateY(${y * 0.3}px)`;
      }
    }, { passive: true });
  }

  /* ── Scroll reveal ───────────────────────────────────────── */
  const revealEls = document.querySelectorAll('.reveal, .reveal-left, .reveal-right');
  if (revealEls.length > 0 && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealEls.forEach(el => observer.observe(el));
  } else {
    // Fallback: show all immediately
    revealEls.forEach(el => el.classList.add('visible'));
  }

  /* ── Floating CTA (appears after scrolling past hero) ────── */
  const floatingCta = document.getElementById('floatingCta');
  if (floatingCta) {
    const heroEl = document.querySelector('.hero');
    const checkFloating = () => {
      if (!heroEl) return;
      const heroBottom = heroEl.getBoundingClientRect().bottom;
      floatingCta.classList.toggle('visible', heroBottom < 0);
    };
    window.addEventListener('scroll', checkFloating, { passive: true });
    checkFloating();
  }

  /* ── Animated counters ───────────────────────────────────── */
  function animateCounter(el) {
    const target = parseFloat(el.dataset.target ?? el.textContent);
    const suffix = el.dataset.suffix ?? '';
    const prefix = el.dataset.prefix ?? '';
    const duration = 1600;
    const start = performance.now();

    const update = (now) => {
      const elapsed = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - elapsed, 3);
      const value = target * eased;
      el.textContent = prefix + (Number.isInteger(target) ? Math.round(value) : value.toFixed(1)) + suffix;
      if (elapsed < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
  }

  const statNumbers = document.querySelectorAll('.stat-number[data-target]');
  if (statNumbers.length > 0 && 'IntersectionObserver' in window) {
    const statObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          statObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });
    statNumbers.forEach(el => statObserver.observe(el));
  }

  /* ── Smooth scroll for anchor links ─────────────────────── */
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const id = link.getAttribute('href').slice(1);
      const target = document.getElementById(id);
      if (!target) return;
      e.preventDefault();
      const offset = 80; // navbar height
      const y = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top: y, behavior: 'smooth' });
    });
  });

  /* ── Contact form handler ────────────────────────────────── */
  const contactForm = document.getElementById('contactForm');
  if (contactForm) {
    contactForm.addEventListener('submit', async function (e) {
      e.preventDefault();
      const btn = this.querySelector('[type="submit"]');
      const originalText = btn.textContent;
      btn.textContent = 'Wird gesendet…';
      btn.disabled = true;

      try {
        const resp = await fetch('/api/contact.php', {
          method: 'POST',
          body: new FormData(this),
        });
        const data = await resp.json();
        if (data.success) {
          showNotification('Vielen Dank! Wir melden uns bald bei dir.', 'success');
          this.reset();
        } else {
          showNotification(data.message ?? 'Es ist ein Fehler aufgetreten.', 'error');
        }
      } catch {
        showNotification('Netzwerkfehler. Bitte versuche es erneut.', 'error');
      } finally {
        btn.textContent = originalText;
        btn.disabled = false;
      }
    });
  }

  /* ── Notification toast ──────────────────────────────────── */
  function showNotification(msg, type = 'success') {
    const existing = document.querySelector('.sf-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'sf-toast';
    toast.setAttribute('role', 'alert');
    toast.style.cssText = `
      position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%);
      z-index: 9999; padding: 14px 24px; border-radius: 8px;
      font-size: 0.9rem; font-weight: 600; font-family: inherit;
      background: ${type === 'success' ? '#111' : '#c0392b'};
      color: #fff; box-shadow: 0 8px 32px rgba(0,0,0,.3);
      transition: opacity 0.3s; white-space: nowrap;
    `;
    toast.textContent = msg;
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; }, 3500);
    setTimeout(() => toast.remove(), 4000);
  }

  /* ── Lazy load images ────────────────────────────────────── */
  if ('loading' in HTMLImageElement.prototype) {
    document.querySelectorAll('img[data-src]').forEach(img => {
      img.src = img.dataset.src;
    });
  } else if ('IntersectionObserver' in window) {
    const imgObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.src = img.dataset.src;
          imgObserver.unobserve(img);
        }
      });
    });
    document.querySelectorAll('img[data-src]').forEach(img => imgObserver.observe(img));
  }

  /* ── Testimonial read-more toggle ────────────────────────── */
  document.querySelectorAll('.testimonial-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const card = btn.closest('.testimonial-card');
      const text = card?.querySelector('.testimonial-text');
      if (!text) return;
      const expanded = text.classList.toggle('expanded');
      btn.textContent = expanded ? 'Weniger anzeigen' : 'Weiterlesen';
    });
  });

})();
