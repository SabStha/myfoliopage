import React from "react";

const ProgressRow = ({
  label,
  unit,
  value,
  goal,
  pct,
  link,
  eta,
  isVisible = true,
  index = 0,
}) => {
  const pctClamped = Math.max(0, Math.min(100, pct));
  
  return (
    <div
      className="grid items-center gap-10 py-4
                 grid-cols-[220px_1fr_200px]
                 border-b border-neutral-200 last:border-b-0
                 hover:bg-black/[.02] transition-colors duration-200"
      role="row"
    >
      {/* Left: Label + Unit stacked - Fixed width for alignment */}
      <div className="flex flex-col gap-1">
        <div className="font-medium text-neutral-900 text-2xl">{label}</div>
        <div className="text-base tracking-wide uppercase text-neutral-500">
          {unit}
        </div>
      </div>

      {/* Center: Progress Bar - Thicker and aligned */}
      <div className="flex items-center w-full">
        <div
          role="progressbar"
          aria-valuemin={0}
          aria-valuemax={goal}
          aria-valuenow={value}
          aria-label={`${label} progress: ${pctClamped.toFixed(0)}%`}
          className="relative w-full h-6 rounded-full bg-[#EDEEF0] overflow-visible ring-1 ring-inset ring-neutral-200 group/bar"
        >
          {/* Bar Fill - Yellow color like project */}
          <div
            className="absolute left-0 top-0 h-full rounded-full will-change-transform
                       transition-[width,filter] duration-300 ease-out
                       group-hover/bar:brightness-110"
            style={{
              width: isVisible ? `${pctClamped}%` : '0%',
              transitionDelay: isVisible ? `${index * 50}ms` : '0ms',
              backgroundColor: '#ffb400', // Yellow like project
              boxShadow: 'inset 0 1px 0 rgba(255,255,255,.2)',
            }}
          >
            {/* Inner highlight for depth */}
            <div className="absolute inset-0 rounded-full bg-gradient-to-b from-white/30 via-white/10 to-transparent pointer-events-none"></div>
          </div>
          {/* Percent pill - Larger text */}
          {isVisible && pctClamped > 0 && (
            <div
              className="absolute -translate-y-1/2 top-1/2 translate-x-1/2
                         px-3 py-1 rounded-full text-base font-medium
                         bg-white/90 border border-neutral-200 text-neutral-900
                         shadow-[inset_0_1px_0_rgba(255,255,255,.7)] backdrop-blur whitespace-nowrap z-10"
              style={{
                left: `max(20px, min(calc(${pctClamped}% - 12px), 98%))`,
              }}
              role="status"
              aria-label={`${pctClamped.toFixed(0)}% complete`}
            >
              {Math.round(pctClamped)}%
            </div>
          )}
        </div>
      </div>

      {/* Right: Value/Goal - Larger text, fixed width */}
      <div className="text-right text-base text-neutral-600 uppercase tabular-nums whitespace-nowrap">
        {(value ?? 0).toLocaleString()} / {(goal ?? 100).toLocaleString()}
        {eta && <span className="ml-3 text-neutral-400 normal-case text-sm">· ETA {eta}</span>}
      </div>
    </div>
  );
};

export default ProgressRow;
