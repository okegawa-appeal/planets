jQuery(function ($) {
  $(".js-on-more").on("click", function () {
    $(".description-accordion").toggleClass("description-accordion_open");
    $(this).toggleClass("on-more");
  });

  // スクロールアニメーション
  $(window).on("scroll", function () {
    fadeinImage();
  });
  fadeinImage();
  function fadeinImage() {
    $(".fadein").each(function () {
      let elementTopPositon = $(this).offset().top;
      let windowScrollTopPosition = $(window).scrollTop();
      let windowHeight = $(window).height();
      if (windowScrollTopPosition > elementTopPositon - windowHeight + 150) {
        $(this).addClass("scrollin");
      }
    });
  }
});
