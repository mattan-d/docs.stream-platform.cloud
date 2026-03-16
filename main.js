document.addEventListener("DOMContentLoaded", () => {
  const links = document.querySelectorAll(".sp-toc-list a[href^='#']");
  const sections = Array.from(
    document.querySelectorAll(".sp-section[id]")
  );
  const backToTop = document.querySelector(".sp-back-to-top");

  // Smooth scroll (extra control for older browsers)
  links.forEach((link) => {
    link.addEventListener("click", (e) => {
      e.preventDefault();
      const targetId = link.getAttribute("href")?.substring(1);
      if (!targetId) return;
      const targetEl = document.getElementById(targetId);
      if (!targetEl) return;

      const rect = targetEl.getBoundingClientRect();
      const offsetTop = window.scrollY + rect.top - 80; // header offset

      window.scrollTo({
        top: offsetTop,
        behavior: "smooth",
      });
    });
  });

  // Active link highlighting based on scroll position
  const onScroll = () => {
    const scrollPos = window.scrollY;
    const headerOffset = 120;

    let currentId = "";

    for (const section of sections) {
      const top = section.offsetTop - headerOffset;
      if (scrollPos >= top) {
        currentId = section.id;
      }
    }

    links.forEach((link) => {
      const id = link.getAttribute("href")?.substring(1);
      if (!id) return;
      link.classList.toggle("sp-toc-active", id === currentId);
    });

    // back to top visibility
    if (backToTop) {
      backToTop.style.display = scrollPos > 350 ? "flex" : "none";
    }
  };

  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  if (backToTop) {
    backToTop.addEventListener("click", () => {
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
  }
});

