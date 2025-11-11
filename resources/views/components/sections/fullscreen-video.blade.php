@props([
  'src' => null,
  'poster' => null,
  'headline' => null,
])

<section class="relative h-screen w-full overflow-hidden bg-black">
  <video
    class="absolute inset-0 w-full h-full object-cover"
    autoplay
    muted
    loop
    playsinline
    preload="metadata"
    @if($poster) poster="{{ $poster }}" @endif
  >
    <source src="{{ $src }}" type="video/mp4">
  </video>

  <div class="absolute inset-0 bg-black/20 pointer-events-none"></div>

  @if($headline)
  <div class="absolute inset-0 z-10 flex items-center justify-center px-6">
    <h2 class="text-white text-center font-bold tracking-tight
               text-4xl sm:text-5xl md:text-6xl lg:text-7xl drop-shadow">
      {{ $headline }}
    </h2>
  </div>
  @endif
</section>



