(function () {
  'use strict';

  // Optional: highlight current section in ToC on scroll (RTL-aware)
  var tocLinks = document.querySelectorAll('.toc-list a');
  var sections = [];
  tocLinks.forEach(function (link) {
    var id = link.getAttribute('href').slice(1);
    var section = document.getElementById(id);
    if (section) sections.push({ id: id, el: section, link: link });
  });

  function updateActiveLink() {
    var scrollTop = window.scrollY || document.documentElement.scrollTop;
    var viewportMid = scrollTop + window.innerHeight / 3;
    var current = null;
    sections.forEach(function (s) {
      var top = s.el.getBoundingClientRect().top + scrollTop;
      if (top <= viewportMid) current = s;
    });
    tocLinks.forEach(function (l) { l.classList.remove('is-active'); });
    if (current) current.link.classList.add('is-active');
  }

  if (sections.length && typeof window.requestAnimationFrame !== 'undefined') {
    window.addEventListener('scroll', function () {
      requestAnimationFrame(updateActiveLink);
    }, { passive: true });
    updateActiveLink();
  }
})();
