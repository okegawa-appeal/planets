$(function () {
  // star
  let mainStars = {
    particles: {
      number: {
        value: 500, //この数値を変更すると星の数が増減できる
        density: {
          enable: true,
          value_area: 800,
        },
      },
      color: {
        value: "#ffffff",
      },
      shape: {
        type: "circle", //形状はcircleを指定
        stroke: {
          width: 0,
        },
      },
      opacity: {
        value: 1, //シェイプの透明度
        random: true, //シェイプの透明度をランダムにする
        anim: {
          enable: true, //シェイプの透明度をアニメーションさせる
          speed: 1, //シェイプの透明度をアニメーションさせる
          opacity_min: 0, //透明度の最小値０
          sync: false, //全てを同時にアニメーションさせない
        },
      },
      size: {
        value: 2,
        random: true,
        anim: {
          enable: false,
          speed: 4,
          size_min: 0.3,
          sync: false,
        },
      },
      line_linked: {
        enable: false,
      },
      move: {
        enable: true,
        speed: 20, //この数値を小さくするとゆっくりな動きになる
        direction: "none", //方向指定なし
        random: true, //動きはランダムに
        straight: true, //動きをとどめる
        out_mode: "out",
        bounce: false,
        attract: {
          enable: false,
          rotateX: 600,
          rotateY: 600,
        },
      },
    },
    interactivity: {
      detect_on: "canvas",
      events: {
        onhover: {
          enable: false,
        },
        onclick: {
          enable: false,
        },
        resize: true,
      },
    },
    retina_detect: true,
  };

  $(".start-award").on("click", (event) => {
    // 1
    $("#SkyInner").on("animationend webkitAnimationEnd", () => {
      $(".award--photo").fadeIn();
    });
    // 2
    $(".award--photo").on("animationend webkitAnimationEnd", () => {
      $(".award--badge").css({ display: "flex" });
      $(".award--infomation").show();
      $(".award--close").show();
      $(".award--badge").addClass("animate__bounceIn");

      $(".award--infomation").addClass("animate__bounceIn");
      $(".award--close").addClass("animate__bounceIn");
    });
    $(".snav").hide();
    $award = $(event.currentTarget).data("award");
    $image = $(event.currentTarget).data("image");
    $title = $(event.currentTarget).data("title");
    let content = `<p>${$award}<span>賞</span></p>`;
    let image = `<img src="${$image}" alt="" class="clip" />`;
    let title = `<p class="award--infomation__title">${$title}</p>`;
    $(".award--badge").append(content);
    $(".award--photo").append(image);
    $(".award--infomation").append(title);
    $(".award--badge").addClass(`award--${$award}`);
    $("#Award-box").fadeIn(() => {
      $(".js-hide-contents").hide();
    });
    $("#SkyInner").addClass("start");
    if ($award == "A") {
      mainStars.particles.color.value = [
        "#ff6570",
        "#fc65ff",
        "#4f86ff",
        "#03fff7",
        "#03ff54",
        "#fdff00",
        "#ff9b42",
      ];
      mainStars.particles.opacity.anim.speed = 6;
      $(".night").addClass("award-A");
    } else if ($award == "B") {
      mainStars.particles.color.value = ["#fff000", "#FF9393"];
      mainStars.particles.opacity.anim.speed = 3;
      $(".night").addClass("award-B");
    } else if ($award == "C") {
      mainStars.particles.color.value = ["#fff000", "#FF9393"];
      mainStars.particles.opacity.anim.speed = 3;
      $(".night").addClass("award-C");
    } else if ($award == "D") {
      mainStars.particles.color.value = ["#fff000", "#FF9393"];
      mainStars.particles.opacity.anim.speed = 3;
      $(".night").addClass("award-D");
    } else if ($award == "E") {
      mainStars.particles.color.value = ["#ffffff"];
      mainStars.particles.opacity.anim.speed = 1;
      $(".night").addClass("award-E");
    } else if ($award == "F") {
      mainStars.particles.color.value = ["#ffffff"];
      mainStars.particles.opacity.anim.speed = 1;
      $(".night").addClass("award-F");
    } else if ($award == "G") {
      mainStars.particles.color.value = ["#ffffff"];
      mainStars.particles.opacity.anim.speed = 1;
      $(".night").addClass("award-G");
    } else if ($award == "H") {
      mainStars.particles.color.value = ["#ffffff"];
      mainStars.particles.opacity.anim.speed = 1;
      $(".night").addClass("award-H");
    } else if ($award == "I") {
      mainStars.particles.color.value = ["#ffffff"];
      mainStars.particles.opacity.anim.speed = 1;
      $(".night").addClass("award-I");
    } else if ($award == "J") {
      mainStars.particles.color.value = ["#ffffff"];
      mainStars.particles.opacity.anim.speed = 1;
      $(".night").addClass("award-J");
    }
    particlesJS("Stars", mainStars);
  });
  particlesJS("MainVisual", mainStars);
});
