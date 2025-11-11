@props([
  'title' => 'Discover our engagements',
  'video' => null,
  'poster' => null,
])

<section class="relative bg-[#f2f3f4] text-black min-h-screen flex flex-col justify-center">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 pt-6 sm:pt-8 md:pt-10 lg:pt-14 flex-1 flex flex-col justify-center">
    <div class="flex items-start justify-center mb-4 sm:mb-6 lg:mb-8">
      <div class="flex flex-col items-center">
        <span class="text-base sm:text-lg md:text-[20px] lg:text-[22px] font-medium tracking-tight leading-none text-center px-4">{{ $title }}</span>
        <span class="block w-32 sm:w-44 md:w-56 lg:w-64 h-[2px] bg-black mt-1"></span>
      </div>
    </div>

    <div class="flex justify-center flex-1 items-center px-2 sm:px-4" x-data="videoTeaser()" x-init="$nextTick(() => init($refs.card))">
      <div x-ref="card" data-video-card :style="style" class="relative w-full sm:w-[95%] md:w-[95%] lg:w-[98%] rounded-lg sm:rounded-xl overflow-hidden shadow-[0_20px_60px_-20px_rgba(0,0,0,0.35)] bg-black will-change-transform">
        @if($video)
          <video
            x-ref="video"
            class="w-full h-full object-cover"
            style="height: min(60vh, calc(100vw * 0.56));"
            autoplay
            muted
            loop
            playsinline
            preload="metadata"
            @if($poster) poster="{{ $poster }}" @endif
          >
            <source src="{{ $video }}" type="video/mp4">
          </video>
        @else
          <div class="w-full bg-neutral-900 grid place-items-center text-white/70 text-xs sm:text-sm px-4" style="height: min(60vh, calc(100vw * 0.56));">
            <span>Video placeholder — set the <code>$video</code> prop.</span>
          </div>
        @endif

        <button
          x-data="{ muted: true }"
          x-init="$el.previousElementSibling && ($el.previousElementSibling.muted = true)"
          @click="const v = $el.previousElementSibling; if(!v) return; muted = !muted; v.muted = muted;"
          class="absolute bottom-2 right-2 sm:bottom-4 sm:right-4 rounded-full bg-white/80 hover:bg-white text-black text-xs sm:text-sm px-2 py-1 sm:px-3 sm:py-1.5 backdrop-blur-sm"
        >
          <span x-text="muted ? '{{ __('app.common.unmute') }}' : '{{ __('app.common.mute') }}'"></span>
        </button>
      </div>
    </div>
  </div>
  <script>
    function videoTeaser(){
      return {
        scaleX: 0.595,
        scaleY: 0.90,
        style: 'transform: scale(0.595, 0.90); transition: transform .25s ease-out; transform-origin: center;',
        videoEl: null,
        init(card){
          if (!card) { card = this.$el.querySelector('[data-video-card]'); }
          if (!card) { return; }
          this.videoEl = card.querySelector('video');
          const update = () => {
            const rect = card.getBoundingClientRect();
            const vh = window.innerHeight || 1;
            const mid = rect.top + rect.height/2;
            // progress is 1 when the card center is at viewport center; fades when away
            const proximity = 1 - Math.min(1, Math.abs(mid - vh/2) / (vh * 0.6));
            const clamped = Math.max(0, proximity);
            this.scaleX = 0.595 + (0.605 * clamped); // 0.595 -> 1.2 width (starts 30% smaller, grows much bigger)
            this.scaleY = 0.90 + (0.30 * clamped); // 0.90 -> 1.2 height (grows bigger to fill)
            this.style = `transform: scale(${this.scaleX.toFixed(3)}, ${this.scaleY.toFixed(3)}); transition: transform .25s ease-out; transform-origin: center;`;
          };
          update();
          window.addEventListener('scroll', update, { passive: true });
          window.addEventListener('resize', update);

          // Ensure continuous playback even while scrolling/off-center
          const ensurePlay = () => {
            if (!this.videoEl) return;
            this.videoEl.muted = true; this.videoEl.loop = true; this.videoEl.playsInline = true;
            const p = this.videoEl.play(); if (p && p.catch) p.catch(()=>{});
          };
          ensurePlay();
          document.addEventListener('visibilitychange', () => { if (!document.hidden) ensurePlay(); });
          window.addEventListener('scroll', () => ensurePlay(), { passive: true });
          if ('IntersectionObserver' in window && this.videoEl) {
            const io = new IntersectionObserver((entries)=>{
              entries.forEach(e=>{ if (e.isIntersecting) ensurePlay(); });
            }, { threshold: [0, 0.25, 0.5, 0.75, 1] });
            io.observe(this.videoEl);
          }
        }
      }
    }
  </script>
</section>


