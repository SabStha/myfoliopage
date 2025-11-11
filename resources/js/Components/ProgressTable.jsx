import React, { useRef } from "react";

const fmt = (n) => n.toLocaleString();

const ProgressTable = ({
  items,
  className = "",
  isVisible = true,
}) => {
  const sectionRef = useRef(null);
  const tableHeaderRef = useRef(null);
  const footnoteRef = useRef(null);

  return (
    <section ref={sectionRef} className={`w-full h-screen relative pb-16 sm:pb-20 md:pb-24 pt-6 sm:pt-8 md:pt-10 lg:pt-12 ${className}`} style={{ minHeight: '85vh', height: 'auto', overflow: 'visible', paddingBottom: '4rem', paddingTop: '2rem' }}>
      <style>{`
        @keyframes fadeInLeft {
          from {
            opacity: 0;
            transform: translateX(-80px) scale(0.7) rotateY(-15deg);
            filter: blur(10px);
          }
          to {
            opacity: 1;
            transform: translateX(0) scale(1) rotateY(0deg);
            filter: blur(0px);
          }
        }
        @keyframes titleGlow {
          0%, 100% {
            text-shadow: 0 0 10px rgba(17, 24, 39, 0.3),
                         0 0 20px rgba(17, 24, 39, 0.2),
                         0 0 30px rgba(17, 24, 39, 0.1);
            transform: scale(1);
          }
          50% {
            text-shadow: 0 0 20px rgba(17, 24, 39, 0.5),
                         0 0 40px rgba(17, 24, 39, 0.3),
                         0 0 60px rgba(17, 24, 39, 0.2);
            transform: scale(1.02);
          }
        }
        @keyframes gradientShift {
          0%, 100% {
            background-position: 0% 50%;
          }
          50% {
            background-position: 100% 50%;
          }
        }
        .title-animated {
          animation: titleGlow 3s ease-in-out infinite;
        }
        .title-gradient {
          background: linear-gradient(90deg, #111827 0%, #374151 50%, #111827 100%);
          background-size: 200% auto;
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          animation: gradientShift 4s ease-in-out infinite;
        }
        @keyframes rowFadeIn {
          from {
            opacity: 0;
            transform: translateX(-20px);
          }
          to {
            opacity: 1;
            transform: translateX(0);
          }
        }
        .table-row {
          animation: rowFadeIn 0.5s ease-out forwards;
          opacity: 0;
        }
        .table-row:hover {
          transform: translateY(-2px);
          box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }
        .progress-bar-container:hover .progress-bar-fill {
          filter: brightness(1.15);
          box-shadow: inset 0 2px 4px rgba(0,0,0,.1), 0 2px 8px rgba(255, 180, 0, 0.3);
        }
        .progress-bar-container:hover {
          transform: scale(1.02);
        }
      `}</style>

      {/* Side-by-side layout: Title left, Table right */}
      <div 
        className="mx-auto px-3 sm:px-4 md:px-6 lg:px-10 flex flex-col lg:flex-row items-stretch lg:items-stretch gap-4 lg:gap-8"
        style={{ maxWidth: '140rem', width: '100%', overflow: 'visible' }}
      >
        {/* LEFT SECTION: Title Header */}
        <div className="flex items-center justify-start flex-shrink-0 lg:w-[300px] lg:pr-8">
          <h2
            className="font-black tracking-tight leading-none select-none title-animated"
            style={{ 
              fontSize: 'clamp(1.5rem, 5vw, 3.5rem)',
              display: 'flex',
              flexDirection: 'column',
              justifyContent: 'center',
              alignItems: 'flex-start',
              gap: '0.5rem',
              opacity: 1,
              visibility: 'visible',
              lineHeight: '1.1'
            }}
          >
            <span className="uppercase font-extrabold title-gradient" style={{ 
              opacity: 1,
              visibility: 'visible',
              display: 'block',
              animationDelay: '0s'
            }}>{window.translations?.progress?.my || 'My'}</span>
            <span className="uppercase font-extrabold title-gradient" style={{ 
              opacity: 1,
              visibility: 'visible',
              display: 'block',
              animationDelay: '0.2s'
            }}>{window.translations?.progress?.ongoing || 'On going'}</span>
            <span className="uppercase font-extrabold title-gradient" style={{ 
              opacity: 1,
              visibility: 'visible',
              display: 'block',
              animationDelay: '0.4s'
            }}>{window.translations?.progress?.progress || 'Progress'}</span>
          </h2>
        </div>

        {/* RIGHT SECTION: Table */}
        <div className="flex-1 min-w-0" style={{ overflow: 'hidden' }}>
          <div className="w-full rounded-lg sm:rounded-xl bg-white ring-1 ring-neutral-200 shadow-sm overflow-hidden flex flex-col" style={{ minHeight: '60vh', maxHeight: '85vh' }}>

        {/* HEADER (sticky) - Bold and responsive text sizes */}
        <div
          ref={tableHeaderRef}
          role="row"
          className="sticky top-0 z-10 flex flex-col sm:flex-row items-stretch sm:items-center px-3 py-2 sm:py-3 md:py-4 lg:py-5 gap-2 sm:gap-0
                     bg-neutral-50/90 backdrop-blur border-b border-neutral-200"
        >
          <div className="basis-auto sm:basis-[120px] md:basis-[160px] lg:basis-[220px] xl:basis-[300px] shrink-0 grow-0 flex-shrink-0 min-w-0 sm:min-w-[120px] md:min-w-[160px] lg:min-w-[220px] xl:min-w-[300px]">
            <span className="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl font-bold uppercase tracking-wide text-neutral-600">{window.translations?.progress?.category || 'Category'}</span>
          </div>
          <div className="flex-1 shrink-0 flex items-center justify-center sm:justify-center" style={{ paddingLeft: '4px', paddingRight: '4px', color: '#ffb400' }}>
            <span className="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl font-bold uppercase tracking-wide hidden sm:inline">{window.translations?.progress?.progress || 'Progress'}</span>
            <span className="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl font-bold uppercase tracking-wide sm:hidden">{window.translations?.progress?.progress?.substring(0, 4) || 'Prog'}</span>
          </div>
          <div className="basis-auto sm:basis-[100px] md:basis-[140px] lg:basis-[180px] xl:basis-[260px] shrink-0 grow-0 flex-shrink-0 text-left sm:text-right min-w-0 sm:min-w-[100px] md:min-w-[140px] lg:min-w-[180px] xl:min-w-[260px]" style={{ color: '#3b82f6' }}>
            <span className="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl font-bold uppercase tracking-wide">{window.translations?.progress?.status || 'Status'}</span>
          </div>
        </div>

        {/* BODY (flexible content, no scroll, fills remaining space) */}
        <div className="flex flex-col flex-1 min-h-0 overflow-y-auto">
          {items.map((it, idx) => {
            const pct = Math.max(0, Math.min(100, (it.value / it.goal) * 100));

            return (
              <div
                role="row"
                key={it.id}
                className="table-row flex flex-col sm:flex-row items-stretch sm:items-center px-3 sm:px-4 md:px-6 border-b border-neutral-100 last:border-b-0
                           hover:bg-neutral-50/80 cursor-pointer group
                           transition-all duration-300 ease-out relative overflow-hidden py-3 sm:py-2"
                style={{ 
                  flex: '1 1 0%', 
                  minHeight: 0,
                  animationDelay: `${idx * 80}ms`
                }}
              >
                {/* COL 1 – Category - Fixed width, no shrink, larger text */}
                <div className="basis-auto sm:basis-[160px] md:basis-[260px] lg:basis-[300px] shrink-0 grow-0 flex-shrink-0 min-w-0 sm:min-w-[160px] md:min-w-[260px] lg:min-w-[300px] transition-transform duration-300 group-hover:translate-x-1 mb-2 sm:mb-0">
                  <div className="font-semibold text-lg sm:text-xl md:text-2xl text-neutral-900 truncate group-hover:text-[#ffb400] transition-colors duration-300">
                    {it.label}
                  </div>
                  <div className="text-xs sm:text-sm md:text-base uppercase tracking-wide text-neutral-500 mt-0.5 sm:mt-1 group-hover:text-neutral-700 transition-colors duration-300">
                    {it.unit}
                  </div>
                </div>

                {/* COL 2 – Progress - Exact same padding on all rows for perfect alignment, larger bars */}
                <div 
                  className="flex-1 shrink-0 flex-shrink-0 mb-2 sm:mb-0" 
                  style={{ 
                    paddingLeft: '0', 
                    paddingRight: '0',
                    minWidth: 0,
                    position: 'relative',
                    overflow: 'hidden' // Prevent overflow
                  }}
                >
                  <div
                    role="progressbar"
                    aria-valuemin={0}
                    aria-valuemax={it.goal}
                    aria-valuenow={it.value}
                    aria-label={`${it.label} progress: ${pct.toFixed(0)}%`}
                    className="progress-bar-container relative h-8 sm:h-9 md:h-10 rounded-full bg-neutral-100
                               ring-1 ring-inset ring-neutral-200
                               transition-all duration-300 ease-out"
                    style={{ 
                      width: '100%',
                      maxWidth: '100%',
                      position: 'relative',
                      overflow: 'hidden'
                    }}
                  >
                    {/* Yellow progress bar fill */}
                    <div
                      className="progress-bar-fill absolute left-0 top-0 h-full rounded-full
                                 shadow-[inset_0_2px_4px_rgba(0,0,0,.1)]
                                 transition-all duration-500 ease-out will-change-transform"
                      style={{
                        width: isVisible ? `${pct}%` : "0%",
                        transitionDelay: isVisible ? `${idx * 70}ms` : "0ms",
                        backgroundColor: '#ffb400', // Yellow color
                        boxShadow: 'inset 0 2px 4px rgba(0,0,0,.1), 0 1px 2px rgba(0,0,0,.05)',
                      }}
                    >
                      {/* Inner shine effect */}
                      <div className="absolute inset-0 rounded-full bg-gradient-to-b from-white/40 via-white/10 to-transparent pointer-events-none"></div>
                    </div>
                    {/* Percent pill - positioned to always fit within container */}
                    {isVisible && pct >= 2 && (
                      <div
                        className="absolute top-1/2 -translate-y-1/2
                                   px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-semibold
                                   bg-white/90 border-2 border-neutral-200 text-neutral-900
                                   shadow-lg backdrop-blur whitespace-nowrap
                                   transition-all duration-300 group-hover:scale-110 group-hover:border-[#ffb400] group-hover:shadow-xl"
                        style={{
                          // Position badge within the progress bar, ensuring it never overflows
                          // For high percentages, position it near the right edge but keep it inside
                          left: pct >= 90 
                            ? `calc(${Math.min(pct, 92)}% - 28px)` // Keep badge inside, shift left for high percentages
                            : `max(4px, min(calc(${pct}% - 28px), calc(100% - 56px)))`, // Normal positioning with margin
                          zIndex: 20,
                          pointerEvents: 'none' // Don't block interactions
                        }}
                        role="status"
                        aria-label={`${pct.toFixed(0)}% complete`}
                      >
                        {Math.round(pct)}%
                      </div>
                    )}
                  </div>
                </div>

                {/* COL 3 – Status - Fixed width, no shrink, larger text */}
                <div className="basis-auto sm:basis-[140px] md:basis-[220px] lg:basis-[260px] shrink-0 grow-0 flex-shrink-0 text-left sm:text-right tabular-nums whitespace-nowrap min-w-0 sm:min-w-[140px] md:min-w-[220px] lg:min-w-[260px] transition-transform duration-300 group-hover:-translate-x-1">
                  <div className="text-base sm:text-lg font-semibold text-neutral-700 group-hover:text-[#ffb400] transition-colors duration-300">
                    {fmt(it.value)} / {fmt(it.goal)}
                  </div>
                  {it.eta && (
                    <div className="text-xs sm:text-sm text-neutral-500 normal-case font-normal mt-1 sm:mt-2 group-hover:text-neutral-700 transition-colors duration-300">
                      ETA {it.eta}
                    </div>
                  )}
                </div>
              </div>
            );
          })}
          </div>
          </div>

          {/* Footnote */}
          <div ref={footnoteRef} className="mt-4 sm:mt-6 px-3 sm:px-0">
            <p className="text-[10px] sm:text-xs text-neutral-400 leading-relaxed">
              {window.translations?.progress?.footnote || 'LoC counts from public repos, last 30 days. TryHackMe rooms verified via public profile. All metrics updated automatically.'}
            </p>
          </div>
        </div>
      </div>
    </section>
  );
};

export default ProgressTable;
