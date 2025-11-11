import { useState, useEffect, useRef } from 'react';

// Progress data structure
const progressData = [
  {
    id: 1,
    category: 'TryHackMe',
    icon: '🔐',
    current: 45,
    total: 100,
    color: '#3b82f6', // blue-500
  },
  {
    id: 2,
    category: 'Udemy',
    icon: '🎓',
    current: 12,
    total: 20,
    color: '#a855f7', // purple-500
  },
  {
    id: 3,
    category: 'Books',
    icon: '📚',
    current: 8,
    total: 15,
    color: '#10b981', // green-500
  },
  {
    id: 4,
    category: 'Python',
    icon: '🐍',
    current: 75,
    total: 100,
    color: '#ffb400', // amber to match project
  },
  {
    id: 5,
    category: 'Java',
    icon: '☕',
    current: 60,
    total: 100,
    color: '#f97316', // orange-500
  },
];

const MyWorksSection = () => {
  const [isVisible, setIsVisible] = useState(false);
  const [hoveredIndex, setHoveredIndex] = useState(null);
  const sectionRef = useRef(null);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setIsVisible(true);
          }
        });
      },
      {
        threshold: 0.1,
      }
    );

    if (sectionRef.current) {
      observer.observe(sectionRef.current);
    }

    return () => {
      if (sectionRef.current) {
        observer.unobserve(sectionRef.current);
      }
    };
  }, []);

  const calculatePercentage = (current, total) => {
    return Math.min(100, (current / total) * 100);
  };

  return (
    <section
      ref={sectionRef}
      className="relative min-h-screen bg-[#f6f7fb] py-20 px-4 sm:px-6 lg:px-8"
      style={{
        backgroundImage: 'radial-gradient(circle, rgba(0,0,0,0.03) 1px, transparent 1px)',
        backgroundSize: '32px 32px',
      }}
    >
      <div className="max-w-6xl mx-auto">
        <h2 className="text-4xl sm:text-5xl font-extrabold mb-16 text-center text-gray-900">
          My Ongoing Progress
        </h2>

        <div className="space-y-8">
          {progressData.map((item, index) => {
            const percentage = calculatePercentage(item.current, item.total);
            const animationDelay = index * 150;

            return (
              <div
                key={item.id}
                className={`flex items-center justify-between gap-6 sm:gap-8 lg:gap-12 transition-all duration-700 ${
                  isVisible
                    ? 'opacity-100 translate-y-0'
                    : 'opacity-0 translate-y-10'
                }`}
                style={{
                  transitionDelay: `${animationDelay}ms`,
                }}
                onMouseEnter={() => setHoveredIndex(index)}
                onMouseLeave={() => setHoveredIndex(null)}
              >
                {/* Left side: Label and Icon - Always left-aligned */}
                <div className="flex items-center gap-4 flex-shrink-0">
                  <span className="text-4xl sm:text-5xl">{item.icon}</span>
                  <div className="flex flex-col">
                    <span className="text-xl sm:text-2xl font-bold text-gray-900">
                      {item.category}
                    </span>
                    {hoveredIndex === index && (
                      <span className="text-sm text-gray-500 mt-1">
                        {item.current} / {item.total}
                      </span>
                    )}
                  </div>
                </div>

                {/* Right side: Progress Bar - Always right-aligned */}
                <div className="flex-1 max-w-2xl relative">
                  <div className="relative">
                    {/* Background bar */}
                    <div
                      className="w-full rounded-full overflow-hidden shadow-inner"
                      style={{
                        height: '48px',
                        backgroundColor: '#e5e7eb',
                        border: '2px solid #d1d5db',
                      }}
                    >
                      {/* Animated progress bar */}
                      <div
                        className="h-full rounded-full transition-all duration-1000 ease-out relative overflow-hidden"
                        style={{
                          width: isVisible ? `${percentage}%` : '0%',
                          backgroundColor: item.color,
                          transitionDelay: `${animationDelay + 400}ms`,
                          boxShadow: `inset 0 2px 4px rgba(0,0,0,0.1), 0 2px 8px ${item.color}40`,
                        }}
                      >
                        {/* Shimmer effect */}
                        <div
                          className="absolute inset-0"
                          style={{
                            background: 'linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent)',
                            animation: 'shimmer 2s infinite',
                          }}
                        ></div>
                      </div>
                    </div>

                    {/* Percentage text on bar */}
                    <div
                      className="absolute inset-0 flex items-center pointer-events-none"
                      style={{ justifyContent: 'flex-end', paddingRight: '16px' }}
                    >
                      <span
                        className="font-bold drop-shadow-sm"
                        style={{
                          fontSize: '14px',
                          color: isVisible ? '#374151' : 'transparent',
                          transitionDelay: `${animationDelay + 600}ms`,
                        }}
                      >
                        {isVisible ? `${percentage.toFixed(0)}%` : ''}
                      </span>
                    </div>

                    {/* Hover tooltip */}
                    {hoveredIndex === index && (
                      <div
                        className="absolute z-10 rounded-lg shadow-xl whitespace-nowrap border"
                        style={{
                          top: '-56px',
                          right: '0',
                          backgroundColor: '#1f2937',
                          color: '#fff',
                          padding: '8px 16px',
                          fontSize: '14px',
                          borderColor: '#374151',
                        }}
                      >
                        <div style={{ textAlign: 'center' }}>
                          <div style={{ fontWeight: 'bold' }}>
                            {item.current} / {item.total}
                          </div>
                          <div style={{ fontSize: '12px', color: '#9ca3af', marginTop: '2px' }}>
                            {percentage.toFixed(1)}% Complete
                          </div>
                        </div>
                        {/* Tooltip arrow */}
                        <div
                          className="absolute"
                          style={{
                            bottom: '-8px',
                            left: '50%',
                            transform: 'translateX(-50%)',
                          }}
                        >
                          <div
                            style={{
                              width: 0,
                              height: 0,
                              borderLeft: '8px solid transparent',
                              borderRight: '8px solid transparent',
                              borderTop: '8px solid #1f2937',
                            }}
                          ></div>
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      </div>

      {/* Custom animation for shimmer */}
      <style>{`
        @keyframes shimmer {
          0% {
            transform: translateX(-100%);
          }
          100% {
            transform: translateX(100%);
          }
        }
      `}</style>
    </section>
  );
};

export default MyWorksSection;
