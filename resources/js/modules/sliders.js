import Swiper from "swiper";
import { Autoplay } from "swiper/modules";
import "swiper/css";
import "swiper/css/effect-coverflow";

export function initSwipers() {
    initMobileSwiper();
    initDesktopSwiperWithProgress();
}

function initMobileSwiper() {
    document.querySelectorAll("[mobile-swiper-slider]").forEach((el) => {
        new Swiper(el, {
            modules: [Autoplay],

            loop: true,
            centeredSlides: true,
            slidesPerView: "auto",
            spaceBetween: 2,
            speed: 650,

            effect: "coverflow",
            grabCursor: true,
            watchSlidesProgress: true,
            coverflowEffect: {
                rotate: 0,
                stretch: 0,
                depth: 0,
                modifier: 2.2,
                slideShadows: false,
            },

            autoplay: {
                delay: 3500,
                disableOnInteraction: false,
                pauseOnMouseEnter: true,
            },
        });
    });
}

function initDesktopSwiperWithProgress() {
    document.querySelectorAll("[desktop-swiper-slider]").forEach((el) => {
        const progressRoot = el.querySelector("[data-progress]");

        const swiper = new Swiper(el, {
            modules: [Autoplay],
            spaceBetween: 30,
            centeredSlides: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            on: {
                init(s) {
                    buildBars(s, progressRoot);
                    updateBars(s, 0); // старт: текущая полоса пустая
                },
                autoplayTimeLeft(s, time, progress) {
                    updateBars(s, 1 - progress);
                },
                slideChange(s) {
                    updateBars(s, 0);
                },
                update(s) {
                    buildBars(s, progressRoot);
                    updateBars(s, 0);
                },
            },
        });
    });
}

function buildBars(swiper, root) {
    if (!root) return;

    const count = swiper.slides
        ? swiper.slides.filter((slide) => !slide.classList.contains("swiper-slide-duplicate")).length
        : swiper.slides.length;

    if (root.children.length === count) return;

    root.innerHTML = "";
    for (let i = 0; i < count; i++) {
        const bar = document.createElement("div");
        bar.className = "bar";
        bar.innerHTML = "<i></i>";
        root.appendChild(bar);
    }
}

function updateBars(swiper, activeFill) {
    const root = swiper.el.querySelector("[data-progress]");
    if (!root) return;

    const bars = [...root.querySelectorAll(".bar > i")];
    const activeIndex = swiper.realIndex ?? swiper.activeIndex;

    const fill = Math.max(0, Math.min(1, activeFill)) * 100;

    bars.forEach((fillEl, i) => {
        if (i < activeIndex) fillEl.style.width = "100%";
        else if (i === activeIndex) fillEl.style.width = `${fill}%`;
        else fillEl.style.width = "0%";
    });
}
