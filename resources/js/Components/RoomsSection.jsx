import React, { useRef, useState, useEffect } from 'react';

// Custom hook for scroll-triggered animations
const useInViewOnce = (ref) => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setIsVisible(true);
            if (ref.current) {
              observer.unobserve(ref.current);
            }
          }
        });
      },
      {
        threshold: 0.1,
      }
    );

    const currentRef = ref.current;
    if (currentRef) {
      observer.observe(currentRef);
    }

    return () => {
      if (currentRef) {
        observer.unobserve(currentRef);
      }
    };
  }, [ref]);

  return isVisible;
};

// Helper to get image URL from lab/room media
const getRoomImage = (room) => {
  if (room.media && room.media.length > 0) {
    let imageMedia = room.media.find(m => m.type === 'image');
    if (!imageMedia && room.media.length > 0) {
      imageMedia = room.media.find(m => {
        const path = m.path || '';
        return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(path);
      });
    }
    
    if (imageMedia && imageMedia.path) {
      const path = imageMedia.path;
      if (path.startsWith('http')) {
        return path;
      }
      if (path.startsWith('/')) {
        return path;
      }
      return '/storage/' + path;
    }
  }
  
  // Try to find image by room ID or slug
  const searchPaths = [
    `/storage/rooms/room-${room.id}.*`,
    `/storage/rooms/${room.slug}.*`,
    `/rooms/room-${room.id}.*`,
    `/rooms/${room.slug}.*`,
  ];
  
  return null;
};

const RoomsSection = ({ 
  rooms = [],
  brandTitle = "ROOMS",
  className = ""
}) => {
  const sectionRef = useRef(null);
  const isVisible = useInViewOnce(sectionRef);

  // Filter rooms that have images or add image URLs
  const roomsWithImages = rooms
    .map(room => {
      const imageUrl = getRoomImage(room);
      return {
        ...room,
        imageUrl: imageUrl
      };
    })
    .filter(room => room.imageUrl || true); // Show all rooms, even without images

  // Format date for display (e.g., "MAR 2024")
  const formatDate = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const month = date.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
    const year = date.getFullYear();
    return `${month} ${year}`;
  };

  return (
    <section 
      ref={sectionRef}
      className={`w-full bg-purple-50 ${className}`}
      style={{ 
        minHeight: '100vh',
        paddingTop: '2rem',
        paddingBottom: '2rem',
      }}
      aria-label={`${brandTitle} - Rooms`}
    >
      <style>{`
        @keyframes fadeInUp {
          from {
            opacity: 0;
            transform: translateY(30px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }
        .room-card-animate {
          animation: fadeInUp 0.6s ease-out forwards;
          opacity: 0;
        }
      `}</style>

      <div className="w-full px-6 md:px-10 lg:px-16 py-12">
        {/* Header */}
        <div 
          className="mb-12"
          style={{
            animation: isVisible ? 'fadeInUp 0.8s ease-out forwards' : 'none',
            opacity: isVisible ? 1 : 0,
          }}
        >
          <h2 
            className="text-[clamp(2.5rem,6vw,5rem)] font-black tracking-tight leading-[0.9] uppercase text-purple-900 select-none mb-4"
            style={{
              fontFamily: 'system-ui, -apple-system, sans-serif'
            }}
          >
            {brandTitle}
          </h2>
          <p 
            className="text-2xl md:text-3xl font-serif text-purple-700"
            style={{
              fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
            }}
          >
            Rooms
          </p>
        </div>

        {/* Rooms Grid */}
        {roomsWithImages.length === 0 ? (
          <div className="flex items-center justify-center h-64">
            <p className="text-purple-500 text-center">
              No rooms available. Add rooms to display them here.
            </p>
          </div>
        ) : (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            {roomsWithImages.map((room, index) => {
              const delay = index * 100;
              const dateFormatted = formatDate(room.completed_at);
              
              return (
                <div
                  key={room.id || index}
                  className="room-card-animate relative group cursor-pointer"
                  style={{
                    animationDelay: isVisible ? `${delay}ms` : '0ms',
                  }}
                >
                  <div className="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-500 overflow-hidden">
                    {/* Image Section */}
                    <div className="relative aspect-[4/3] overflow-hidden bg-purple-100">
                      {room.imageUrl ? (
                        <img
                          src={room.imageUrl}
                          alt={room.title || `Room ${index + 1}`}
                          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                          style={{ 
                            display: 'block', 
                            backgroundImage: 'none', 
                            background: 'transparent',
                          }}
                          loading="lazy"
                          onError={(e) => {
                            e.target.style.display = 'none';
                          }}
                        />
                      ) : (
                        <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-200 to-purple-300">
                          <svg className="w-24 h-24 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                          </svg>
                        </div>
                      )}
                      
                      {/* Date Box Overlay - Bottom Left */}
                      {dateFormatted && (
                        <div 
                          className="absolute bottom-4 left-4 bg-amber-50 px-4 py-2 rounded shadow-lg"
                          style={{
                            backgroundColor: '#fefcf3', // Light beige/off-white
                            boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)'
                          }}
                        >
                          <p 
                            className="text-neutral-900 font-serif text-sm md:text-base"
                            style={{
                              fontFamily: 'Georgia, "Times New Roman", serif',
                              fontWeight: 'normal'
                            }}
                          >
                            {dateFormatted}
                          </p>
                        </div>
                      )}
                    </div>

                    {/* Title Section - Below Image */}
                    <div className="p-4 bg-white">
                      <h3 
                        className="text-lg md:text-xl font-serif text-purple-900 leading-tight"
                        style={{
                          fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif',
                          fontWeight: 'normal',
                          color: '#7c3aed' // Dark red/maroon color (using purple as base)
                        }}
                      >
                        {room.title || `Room ${index + 1}`}
                      </h3>
                      {room.platform && (
                        <p className="text-sm text-purple-600 mt-1">
                          {room.platform}
                        </p>
                      )}
                    </div>
                  </div>
                  
                  {/* Room URL Link (if exists) */}
                  {room.room_url && (
                    <a
                      href={room.room_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="absolute inset-0 z-10"
                      onClick={(e) => e.stopPropagation()}
                      aria-label={`Open ${room.title} room`}
                    />
                  )}
                </div>
              );
            })}
          </div>
        )}
      </div>
    </section>
  );
};

export default RoomsSection;

