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
            //            $(".award--close").show();
            $(".award--badge").addClass("animate__bounceIn");

            $(".award--infomation").addClass("animate__bounceIn");
            //            $(".award--close").addClass("animate__bounceIn");
        });
        $(".snav").hide();
        var id = $(event.currentTarget).data("index");
        var order_id = $(event.currentTarget).data("order");
        console.log(order_id);
        var hostUrl = 'https://' + location.host + '/wp-json/raffle/v1/open/'
        console.log(hostUrl);
        $.ajax({
            url: hostUrl,
            type: 'POST',
            dataType: 'json',
            data: { id: id, o: order_id },
            timeout: 3000,
        })

        $award = $(event.currentTarget).data("award");
        $image = $(event.currentTarget).data("image");
        $description = $(event.currentTarget).data("description");
        let content = `<p>${$award}<span>賞</span></p>`;
        let contentimage = `<a href=""><img src="${$image}" alt="" class="clip" width="300px"/></a>`;
        let contentdescription = `<p class="award--infomation__title">${$description}</p>`;
        $(".award--badge").append(content);
        $(".award--photo").append(contentimage);
        $(".award--infomation").append(contentdescription);
        $(".award--badge").addClass(`award--${$award}`);
        $("#Award-box").fadeIn(() => {
            $(".js-hide-contents").hide();
        });
        $("#SkyInner").addClass("start");
        particlesJS("Stars", mainStars);
    });
    particlesJS("MainVisual", mainStars);
});
