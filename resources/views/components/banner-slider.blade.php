<div class="mobile-swiper-slider px-4 py-3 overflow-x-hidden lg:hidden">
    <div class="swiper " mobile-swiper-slider>
        <div class="swiper-wrapper">
            @foreach($slides as $slide)
                <div class="swiper-slide !w-[280px] sm:!w-[520px]">
                    <x-banner-slide
                        :link="$slide->link"
                        :name="$slide->name"
                        :image="$slide->image_desktop"
                    />
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="px-4 py-3 overflow-x-hidden hidden lg:block rounded-lg">
    <div class="swiper desktop-swiper-slider rounded-lg" desktop-swiper-slider>
        <div class="swiper-wrapper rounded-lg">
            @foreach($slides as $slide)
                <div class="swiper-slide rounded-lg">
                    <x-banner-slide
                        :link="$slide->link"
                        :name="$slide->name"
                        :image="$slide->image_desktop"
                    />
                </div>
            @endforeach
        </div>
        <div class="slider-progress" data-progress></div>
    </div>
</div>
