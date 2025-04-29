/**
 * SeoPost frontend interactions.
 * Add any JS here (e.g. AJAX “Get Price” handlers).
 */
document.addEventListener('DOMContentLoaded', function () {
    document
      .querySelectorAll('.seopost-wrapper .get-price-btn')
      .forEach(btn => {
        btn.addEventListener('click', function () {
          // Replace with real logic
          alert('Price request sent for post ID ' + this.dataset.postId);
        });
      });
  });
  