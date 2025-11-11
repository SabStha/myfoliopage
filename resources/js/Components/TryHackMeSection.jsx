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

// Helper to get image URL from certificate media
const getCertificateImage = (certificate) => {
  if (certificate.media && certificate.media.length > 0) {
    // Try to find image type media first
    let imageMedia = certificate.media.find(m => m.type === 'image');
    
    // If no image type found, try to find any media item that might be an image
    if (!imageMedia && certificate.media.length > 0) {
      // Check if path suggests it's an image (common image extensions)
      imageMedia = certificate.media.find(m => {
        const path = m.path || '';
        return /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(path);
      });
      
      // If still nothing, just use the first media item
      if (!imageMedia) {
        imageMedia = certificate.media[0];
      }
    }
    
    if (imageMedia) {
      // Laravel asset path - handle both full paths and relative paths
      const path = imageMedia.path || '';
      
      // If path already starts with /, use it directly (for public/certificates/)
      if (path.startsWith('/')) {
        return path;
      }
      // If path starts with http, use as-is
      if (path.startsWith('http')) {
        return path;
      }
      // Otherwise, assume it's in storage
      return `/storage/${path}`;
    }
  }
  return null;
};

// Grid layout configuration for asymmetric collage - Editorial Style
// Returns className for grid span based on index to create organic, fashion-moodboard layout
const getGridSpan = (index, total) => {
  // Editorial collage pattern - creates asymmetric, overlapping feel
  const layouts = [
    // Image 0: Wide horizontal (top-left area)
    { className: 'col-span-2 row-span-1', rotation: 'rotate-1' },
    // Image 1: Small square (top-right)
    { className: 'col-span-1 row-span-1', rotation: '-rotate-1' },
    // Image 2: Tall vertical (middle-left)
    { className: 'col-span-1 row-span-2', rotation: 'rotate-1' },
    // Image 3: Wide horizontal (middle-right)
    { className: 'col-span-2 row-span-1', rotation: '-rotate-1' },
    // Image 4: Small square (bottom-right)
    { className: 'col-span-1 row-span-1', rotation: 'rotate-1' },
    // Image 5: Large feature (if 6th exists)
    { className: 'col-span-2 row-span-2', rotation: '-rotate-1' },
  ];
  
  // For 4 images, use a refined pattern that matches the reference
  if (total === 4) {
    const fourImageLayout = [
      { className: 'col-span-2 row-span-1', rotation: 'rotate-1' },      // Image 1: Top wide (spans 2 cols)
      { className: 'col-span-1 row-span-1', rotation: '-rotate-1' },      // Image 2: Top right small
      { className: 'col-span-1 row-span-2', rotation: 'rotate-1' },      // Image 3: Left side tall (spans 2 rows)
      { className: 'col-span-2 row-span-1', rotation: '-rotate-1' },     // Image 4: Bottom wide (spans 2 cols)
    ];
    return fourImageLayout[index] || { className: 'col-span-1 row-span-1', rotation: '' };
  }
  
  return layouts[index % layouts.length] || { className: 'col-span-1 row-span-1', rotation: '' };
};

const TryHackMeSection = ({ 
  certificates = [],
  courses = [],
  rooms = [],
  badges = [],
  games = [],
  simulations = [],
  programs = [],
  brandTitle = "TRY HACK ME",
  subtitle = "Certificates",
  className = "",
  animationStyle = 'list_alternating_cards', // 'grid_editorial_collage', 'list_alternating_cards', 'carousel_scroll_left', 'carousel_scroll_right'
  navLinks = [], // NavLinks from database - used to dynamically generate tabs
  subsectionConfigurations = {}
}) => {
  const sectionRef = useRef(null);
  const isVisible = useInViewOnce(sectionRef);
  
  // If NavLinks are provided, group them by category to create tabs
  // Otherwise, use individual NavLinks as tabs
  const tabs = navLinks.length > 0 
    ? (() => {
        // Extract ALL unique category names from ALL navLinks' categories arrays
        const allCategoryNames = navLinks.flatMap(link => 
          (link.categories || []).map(cat => cat.name).filter(Boolean)
        );
        const uniqueCategories = [...new Set(allCategoryNames)];
        
        if (uniqueCategories.length > 1) {
          // Group by category - create one tab per category
          return uniqueCategories.map(catName => ({
            id: catName.toLowerCase().replace(/\s+/g, '-'),
            title: catName,
            key: `category-${catName.toLowerCase().replace(/\s+/g, '-')}`,
          }));
        } else {
          // No categories or single category - create tabs from individual NavLinks
          return navLinks.map(link => ({
            id: String(link.id),
            title: link.title || 'Untitled',
            key: `navlink-${link.id}`,
          }));
        }
      })()
    : [];
    
  // Set initial active tab to first NavLink/category if available, otherwise fallback to certificates
  const [activeTab, setActiveTab] = useState(navLinks.length > 0 ? (tabs[0]?.key || 'navlink-1') : 'certificates');
  const [viewingImage, setViewingImage] = useState(null); // For modal view
  const [viewingCourse, setViewingCourse] = useState(null); // For course detail modal
  const [courseDetails, setCourseDetails] = useState(null); // Course detail data
  const [loadingCourse, setLoadingCourse] = useState(false); // Loading state for course
  const [viewingRoom, setViewingRoom] = useState(null); // For room summary modal
  const [roomDetails, setRoomDetails] = useState(null); // Room detail data

  // If NavLinks are provided, use them as the data source
  // Otherwise, use the legacy arrays (for backward compatibility)
  const navLinksWithImages = navLinks.length > 0
    ? navLinks.map(link => {
        const imageUrl = getCertificateImage(link);
        return {
          ...link,
          imageUrl: imageUrl
        };
      })
    : [];

  // Format date for rooms/badges/simulations/programs (e.g., "MAR 2024")
  const formatRoomDate = (dateString) => {
    if (!dateString) return null;
    const date = new Date(dateString);
    const month = date.toLocaleDateString('en-US', { month: 'short' }).toUpperCase();
    const year = date.getFullYear();
    return `${month} ${year}`;
  };

  // Use active tab data - prioritize NavLinks if available
  const getActiveData = () => {
    // If using NavLinks, filter by category if needed
    if (navLinksWithImages.length > 0) {
      // Check if we're using category-based tabs
      if (activeTab.startsWith('category-')) {
        const categorySlug = activeTab.replace('category-', '');
        // Filter NavLinks by matching category slug from categories array
        return navLinksWithImages.filter(link => {
          if (!link.categories || link.categories.length === 0) return false;
          // Check if any of the link's categories match the active tab slug
          return link.categories.some(cat => {
            const linkSlug = cat.slug || cat.name.toLowerCase().replace(/\s+/g, '-');
            return linkSlug === categorySlug;
          });
        });
      } else if (activeTab.startsWith('navlink-')) {
        // Filter to specific NavLink by ID
        const linkId = activeTab.replace('navlink-', '');
        return navLinksWithImages.filter(link => String(link.id) === linkId);
      }
      // If activeTab doesn't match any pattern, return all
      return navLinksWithImages;
    }
    
    // Legacy fallback to old arrays
    switch(activeTab) {
      case 'certificates': {
        const filtered = certificates.map(cert => ({ ...cert, imageUrl: getCertificateImage(cert) })).filter(cert => cert.imageUrl);
        return filtered;
      }
      case 'courses': {
        const filtered = courses.map(course => ({ ...course, imageUrl: getCertificateImage(course) })).filter(course => course.imageUrl);
        return filtered;
      }
      case 'rooms': {
        const filtered = rooms.map(room => ({ ...room, imageUrl: getCertificateImage(room) }));
        return filtered;
      }
      case 'badges': {
        const filtered = badges.map(badge => ({ ...badge, imageUrl: getCertificateImage(badge) }));
        return filtered;
      }
      case 'games': {
        const filtered = games.map(game => ({ ...game, imageUrl: getCertificateImage(game) })).filter(game => game.imageUrl);
        return filtered;
      }
      case 'simulations': {
        const filtered = simulations.map(sim => ({ ...sim, imageUrl: getCertificateImage(sim) }));
        return filtered;
      }
      case 'programs': {
        const filtered = programs.map(prog => ({ ...prog, imageUrl: getCertificateImage(prog) }));
        return filtered;
      }
      default: {
        const filtered = certificates.map(cert => ({ ...cert, imageUrl: getCertificateImage(cert) })).filter(cert => cert.imageUrl);
        return filtered;
      }
    }
  };
  // Determine active subtitle based on NavLinks or legacy tabs
  const activeSubtitle = navLinks.length > 0
    ? (subtitle || 'Items')
    : (activeTab === 'certificates' ? 'Certificates' : 
        (activeTab === 'courses' ? 'Courses' : 
        (activeTab === 'rooms' ? 'Rooms' :
        (activeTab === 'badges' ? 'Badges' :
        (activeTab === 'games' ? 'Games' :
        (activeTab === 'simulations' ? 'Simulations' : 'Programs'))))));

  // Handle view image (open in modal/fullscreen)
  const handleViewImage = (imageUrl, e) => {
    e.stopPropagation();
    setViewingImage(imageUrl);
  };

  // Handle download image
  const handleDownloadImage = async (imageUrl, title, e) => {
    e.stopPropagation();
    try {
      const response = await fetch(imageUrl);
      const blob = await response.blob();
      const url = window.URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      const filename = title ? `${title.replace(/[^a-z0-9]/gi, '_')}.jpg` : 'image.jpg';
      link.download = filename;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (error) {
      console.error('Download failed:', error);
      // Fallback: open in new tab
      window.open(imageUrl, '_blank');
    }
  };

  // Handle course modal open
  const handleCourseClick = async (courseId, e) => {
    if (e.target.closest('a')) {
      e.stopPropagation();
      return;
    }
    
    if (!courseId) {
      alert(`This course doesn't have a detail page yet. Please create it in the admin panel first.`);
      return;
    }

    setLoadingCourse(true);
    setViewingCourse(courseId);
    
    try {
      const response = await fetch(`/api/courses/${courseId}`);
      if (response.ok) {
        const data = await response.json();
        setCourseDetails(data);
      } else {
        console.error('Failed to fetch course details');
        setViewingCourse(null);
      }
    } catch (error) {
      console.error('Error fetching course:', error);
      setViewingCourse(null);
    } finally {
      setLoadingCourse(false);
    }
  };

  // Close course modal
  const handleCloseCourseModal = () => {
    setViewingCourse(null);
    setCourseDetails(null);
  };

  // Handle room summary modal open
  const handleRoomSummaryClick = (room, e) => {
    e.stopPropagation();
    setViewingRoom(room);
    setRoomDetails(room);
  };

  // Close room modal
  const handleCloseRoomModal = () => {
    setViewingRoom(null);
    setRoomDetails(null);
  };

  // Show section even if no images - will show title/subtitle and empty state
  // if (certificatesWithImages.length === 0) {
  //   return null;
  // }

  return (
    <section 
      ref={sectionRef}
      className={`w-full bg-neutral-50 ${className}`}
      style={{ 
        height: '100vh', 
        minHeight: '100vh', 
        maxHeight: '100vh', 
        overflow: 'hidden', 
        paddingTop: '2rem',
        backgroundImage: 'none',
        background: '#fafafa'
      }}
      aria-label={`${brandTitle} ${subtitle}`}
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
        .cert-image-animate {
          animation: fadeInUp 0.6s ease-out forwards;
          opacity: 0;
        }
        /* Ensure images don't render as backgrounds - Very aggressive rules */
        .cert-image-animate img {
          background-image: none !important;
          background: none !important;
          display: block !important;
          position: relative !important;
          z-index: 10 !important;
          content: attr(src) !important;
        }
        .cert-image-animate {
          background-image: none !important;
          background: transparent !important;
          position: relative !important;
        }
        /* Prevent any parent containers from using backgrounds */
        .cert-image-animate * {
          background-image: none !important;
        }
        /* Force img to be foreground */
        img[src] {
          background-image: none !important;
          background: transparent !important;
        }
        /* Prevent images from appearing in grid gaps */
        .grid.gap-3::before,
        .grid.gap-3::after {
          display: none !important;
          content: none !important;
        }
        /* Ensure no pseudo-elements show images */
        .grid.gap-3 > *::before,
        .grid.gap-3 > *::after {
          background-image: none !important;
          background: transparent !important;
        }
        /* Custom scrollbar styling */
        .overflow-x-auto::-webkit-scrollbar {
          height: 8px;
        }
        .overflow-x-auto::-webkit-scrollbar-track {
          background: #f5f5f5;
          border-radius: 4px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb {
          background: #d4d4d4;
          border-radius: 4px;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
          background: #a3a3a3;
        }
        /* Modal overlay */
        .image-modal-overlay {
          position: fixed;
          inset: 0;
          background: rgba(0, 0, 0, 0.9);
          z-index: 9999;
          display: flex;
          align-items: center;
          justify-content: center;
          padding: 2rem;
          animation: fadeIn 0.3s ease-out;
        }
        .image-modal-content {
          max-width: 85vw;
          max-height: 85vh;
          width: auto;
          height: auto;
          position: relative;
          animation: scaleIn 0.3s ease-out;
        }
        .image-modal-content img {
          max-width: 100%;
          max-height: 85vh;
          width: auto;
          height: auto;
          object-fit: contain;
          border-radius: 0.5rem;
          display: block;
          background-image: none !important;
          background: none !important;
        }
        @keyframes fadeIn {
          from { opacity: 0; }
          to { opacity: 1; }
        }
        @keyframes scaleIn {
          from { transform: scale(0.9); opacity: 0; }
          to { transform: scale(1); opacity: 1; }
        }
        /* Continuous scrolling carousel animation - Right direction */
        @keyframes scrollRight {
          0% {
            transform: translateX(-50%);
          }
          100% {
            transform: translateX(0);
          }
        }
        .carousel-container {
          display: flex;
          animation: scrollRight 60s linear infinite;
          will-change: transform;
          background-image: none !important;
          background: transparent !important;
          isolation: isolate;
        }
        .carousel-container:hover {
          animation-play-state: paused;
        }
        .carousel-wrapper {
          overflow-x: auto;
          overflow-y: hidden;
          position: relative;
          height: 100%;
          scrollbar-width: thin;
          scroll-behavior: smooth;
          background-image: none !important;
          background: transparent !important;
          isolation: isolate;
        }
        .carousel-container > div {
          background-image: none !important;
          background: #fafafa !important;
          isolation: isolate;
          position: relative;
        }
        /* Ensure flex containers between grid and images don't show backgrounds */
        .carousel-container > div > .flex {
          background: #fafafa !important;
          background-image: none !important;
        }
        /* Ensure grid gaps are truly empty */
        .grid.gap-3 > * {
          position: relative;
          isolation: isolate;
          background: transparent !important;
          background-image: none !important;
          contain: layout style paint;
        }
        /* Grid container should not show images in gaps */
        .grid.gap-3 {
          background: #fafafa !important;
          background-image: none !important;
        }
        /* Ensure carousel container doesn't show images in gaps */
        .carousel-container > div {
          background: #fafafa !important;
          background-image: none !important;
        }
        .carousel-container > div > .grid {
          background: #fafafa !important;
          background-image: none !important;
        }
        /* Prevent overflow into gaps */
        .cert-image-animate {
          contain: layout style paint;
          will-change: transform;
        }
        .carousel-wrapper::-webkit-scrollbar {
          height: 8px;
        }
        .carousel-wrapper::-webkit-scrollbar-track {
          background: #f5f5f5;
          border-radius: 4px;
        }
        .carousel-wrapper::-webkit-scrollbar-thumb {
          background: #d4d4d4;
          border-radius: 4px;
        }
        .carousel-wrapper::-webkit-scrollbar-thumb:hover {
          background: #a3a3a3;
        }
      `}</style>
      
          {/* Image View Modal */}
          {viewingImage && (
            <div 
              className="image-modal-overlay"
              onClick={() => setViewingImage(null)}
            >
              <div className="image-modal-content" onClick={(e) => e.stopPropagation()}>
                <img src={viewingImage} alt="Full size view" style={{ backgroundImage: 'none', background: 'none', display: 'block' }} />
                <button
                  onClick={() => setViewingImage(null)}
                  className="absolute top-4 right-4 bg-white/10 hover:bg-white/20 text-white p-2 rounded-full transition-colors duration-200"
                  title="Close"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>
          )}

          {/* Course Detail Modal */}
          {viewingCourse && (
            <div 
              className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto"
              onClick={handleCloseCourseModal}
              style={{ 
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
              }}
            >
              <div 
                className="bg-neutral-50 rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto relative"
                onClick={(e) => e.stopPropagation()}
                style={{ margin: 'auto' }}
              >
                {loadingCourse ? (
                  <div className="flex items-center justify-center p-20">
                    <div className="text-neutral-600">Loading course details...</div>
                  </div>
                ) : courseDetails ? (
                  <>
                    {/* Close Button */}
                    <button
                      onClick={handleCloseCourseModal}
                      className="absolute top-4 right-4 bg-white hover:bg-neutral-100 text-neutral-900 p-2 rounded-full shadow-lg transition-colors duration-200 z-10"
                      title="Close"
                    >
                      <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </button>

                    {/* Header Section */}
                    <div className="bg-gradient-to-r from-neutral-900 via-neutral-800 to-neutral-900 px-8 py-12 text-white">
                      <div className="flex flex-col md:flex-row items-start md:items-center gap-6">
                        {courseDetails.imageUrl && (
                          <div className="flex-shrink-0">
                            <img 
                              src={courseDetails.imageUrl} 
                              alt={courseDetails.title} 
                              className="w-48 h-32 object-cover rounded-xl shadow-xl"
                            />
                          </div>
                        )}
                        <div className="flex-1">
                          <h1 className="text-3xl md:text-4xl font-black mb-3 leading-tight">
                            {courseDetails.title}
                          </h1>
                          {courseDetails.provider && (
                            <p className="text-neutral-300 text-lg mb-2">
                              {courseDetails.provider}
                            </p>
                          )}
                          {courseDetails.credential_id && (
                            <p className="text-neutral-400 text-sm">
                              Credential ID: <span className="font-semibold">{courseDetails.credential_id}</span>
                            </p>
                          )}
                        </div>
                      </div>
                    </div>

                    {/* Content Sections */}
                    <div className="p-8 space-y-8">
                      {/* Overview */}
                      <section className="bg-white rounded-2xl shadow-md p-8">
                        <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                          <span className="w-1 h-8 bg-[#ffb400] rounded"></span>
                          Overview
                        </h2>
                        <p className="text-neutral-700 leading-relaxed text-lg">
                          {courseDetails.description || 'This course is designed to prepare learners for the AWS Certified Cloud Practitioner (CLF-C02) exam. It covers foundational cloud concepts, AWS core services, security and compliance, pricing and billing, and basic architectural practices.'}
                        </p>
                      </section>

                      {/* What You'll Learn */}
                      <section className="bg-white rounded-2xl shadow-md p-8">
                        <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                          <span className="w-1 h-8 bg-[#ffb400] rounded"></span>
                          What You'll Learn
                        </h2>
                        <div className="grid md:grid-cols-2 gap-4">
                          {[
                            'Fundamental cloud computing & AWS concepts: what cloud is, global infrastructure, benefits.',
                            'Core AWS services: compute (EC2, Lambda), storage (S3, EBS), networking (VPC, Route 53), databases.',
                            'Security, compliance, and shared-responsibility model.',
                            'AWS pricing models, cost optimisation, billing fundamentals.',
                            'How to relate AWS services to real-world business scenarios and cloud adoption frameworks.',
                            'Practice exams and labs (depending on edition) to reinforce learning and exam readiness.'
                          ].map((item, idx) => (
                            <div key={idx} className="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                              <svg className="w-6 h-6 text-[#ffb400] flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"></path>
                              </svg>
                              <p className="text-neutral-700">{item}</p>
                            </div>
                          ))}
                        </div>
                      </section>

                      {/* Skills You'll Gain */}
                      <section className="bg-white rounded-2xl shadow-md p-8">
                        <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                          <span className="w-1 h-8 bg-[#ffb400] rounded"></span>
                          Skills You'll Gain
                        </h2>
                        <div className="space-y-4">
                          {[
                            { title: 'Cloud Fundamentals', desc: 'Ability to explain what the cloud is, how AWS operates, and why organisations use cloud services.' },
                            { title: 'AWS Services Mastery', desc: 'Familiarity with AWS\'s major services and how they interconnect.' },
                            { title: 'Security & Governance', desc: 'Understanding of how AWS security and governance models function.' },
                            { title: 'Cost Management', desc: 'Insight into cost management and billing in AWS environments.' },
                            { title: 'Exam Preparedness', desc: 'Preparedness for the AWS Certified Cloud Practitioner exam.' }
                          ].map((skill, idx) => (
                            <div key={idx} className="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-[#ffb400]">
                              <div className="flex-shrink-0 w-10 h-10 rounded-full bg-[#ffb400]/10 flex items-center justify-center">
                                <svg className="w-5 h-5 text-[#ffb400]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                              </div>
                              <div>
                                <p className="font-semibold text-neutral-900 mb-1">{skill.title}</p>
                                <p className="text-neutral-600">{skill.desc}</p>
                              </div>
                            </div>
                          ))}
                        </div>
                      </section>

                      {/* Who This Course is For */}
                      <section className="bg-white rounded-2xl shadow-md p-8">
                        <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                          <span className="w-1 h-8 bg-[#ffb400] rounded"></span>
                          Who This Course is For
                        </h2>
                        <div className="grid md:grid-cols-2 gap-6">
                          {[
                            { title: 'Beginners', desc: 'Beginners with little or no AWS experience who want to gain a foundational credential.' },
                            { title: 'IT Professionals', desc: 'IT professionals transitioning toward cloud roles.' },
                            { title: 'Business Professionals', desc: 'Business professionals who want to understand cloud computing and AWS from a managerial or oversight vantage.' },
                            { title: 'Certification Seekers', desc: 'Anyone wanting a structured overview of AWS before diving into deeper certifications.' }
                          ].map((audience, idx) => (
                            <div key={idx} className="p-6 bg-neutral-50 rounded-xl hover:shadow-md transition-shadow">
                              <h3 className="font-bold text-neutral-900 text-lg mb-3">{audience.title}</h3>
                              <p className="text-neutral-600">{audience.desc}</p>
                            </div>
                          ))}
                        </div>
                      </section>

                      {/* Summary */}
                      <section className="bg-gradient-to-br from-neutral-900 to-neutral-800 rounded-2xl shadow-xl p-8 text-white">
                        <div className="flex items-start gap-4">
                          <div className="flex-shrink-0 w-12 h-12 rounded-full bg-[#ffb400] flex items-center justify-center">
                            <svg className="w-6 h-6 text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                          </div>
                          <div>
                            <h2 className="text-2xl font-bold mb-4">Summary</h2>
                            <p className="text-neutral-200 leading-relaxed text-lg">
                              In short: this Udemy course is a solid entry point into AWS. It balances theory, core services, business-relevance, and exam preparation. While it may not replace deeper hands-on practises, it provides the foundation and confidence to pass the Cloud Practitioner exam and move into more advanced AWS roles.
                            </p>
                          </div>
                        </div>
                      </section>

                      {/* Action Buttons */}
                      {courseDetails.verify_url && (
                        <div className="flex justify-center gap-4">
                          <a
                            href={courseDetails.verify_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center justify-center px-8 py-4 bg-[#ffb400] text-neutral-900 font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
                          >
                            <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Verify Certificate
                          </a>
                        </div>
                      )}
                    </div>
                  </>
                ) : (
                  <div className="flex items-center justify-center p-20">
                    <div className="text-neutral-600">Failed to load course details</div>
                  </div>
                )}
              </div>
            </div>
          )}

          {/* Room Summary Modal */}
          {viewingRoom && roomDetails && (
            <div 
              className="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto"
              onClick={handleCloseRoomModal}
              style={{ 
                position: 'fixed',
                top: 0,
                left: 0,
                right: 0,
                bottom: 0,
              }}
            >
              <div 
                className="bg-neutral-50 rounded-2xl shadow-2xl max-w-5xl w-full max-h-[90vh] overflow-y-auto relative"
                onClick={(e) => e.stopPropagation()}
                style={{ margin: 'auto' }}
              >
                {/* Close Button */}
                <button
                  onClick={handleCloseRoomModal}
                  className="absolute top-4 right-4 bg-white hover:bg-neutral-100 text-neutral-900 p-2 rounded-full shadow-lg transition-colors duration-200 z-10"
                  title="Close"
                >
                  <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>

                {/* Header Section */}
                <div className="bg-gradient-to-r from-purple-900 via-purple-800 to-purple-900 px-8 py-12 text-white">
                  <div className="flex flex-col md:flex-row items-start md:items-center gap-6">
                    {roomDetails.imageUrl && (
                      <div className="flex-shrink-0">
                        <img 
                          src={roomDetails.imageUrl} 
                          alt={roomDetails.title} 
                          className="w-48 h-32 object-cover rounded-xl shadow-xl"
                        />
                      </div>
                    )}
                    <div className="flex-1">
                      <h1 className="text-3xl md:text-4xl font-black mb-3 leading-tight">
                        {roomDetails.title || 'Room'}
                      </h1>
                      {roomDetails.platform && (
                        <p className="text-purple-300 text-lg mb-2">
                          Platform: {roomDetails.platform}
                        </p>
                      )}
                      {roomDetails.completed_at && (
                        <p className="text-purple-400 text-sm">
                          Date Completed: {new Date(roomDetails.completed_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' })}
                        </p>
                      )}
                    </div>
                  </div>
                </div>

                {/* Content Sections */}
                <div className="p-8 space-y-8">
                  {/* Summary Section */}
                  <section className="bg-white rounded-2xl shadow-md p-8">
                    <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                      <span className="w-1 h-8 bg-purple-600 rounded"></span>
                      Summary
                    </h2>
                    <p className="text-neutral-700 leading-relaxed text-lg">
                      {roomDetails.summary || `Successfully completed the ${roomDetails.title || 'Room'}, a ${roomDetails.platform || 'platform'} program designed to replicate real-world tasks and challenges. Gained practical insights and developed hands-on experience.`}
                    </p>
                  </section>

                  {/* Key Responsibilities & Achievements */}
                  <section className="bg-white rounded-2xl shadow-md p-8">
                    <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                      <span className="w-1 h-8 bg-purple-600 rounded"></span>
                      Key Responsibilities & Achievements
                    </h2>
                    <div className="space-y-4">
                      {[
                        'Identified and Reported Security Threats: Conducted analysis to identify potential security threats and vulnerabilities.',
                        'Analyzed Business Security Needs: Evaluated which areas within the business required enhanced security training to mitigate risks effectively.',
                        'Designed and Implemented Training Programs: Created tailored security awareness training courses and procedures to improve organizational security awareness.'
                      ].map((item, idx) => (
                        <div key={idx} className="flex items-start gap-3 p-4 bg-neutral-50 rounded-lg hover:bg-neutral-100 transition-colors">
                          <svg className="w-6 h-6 text-purple-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7"></path>
                          </svg>
                          <p className="text-neutral-700">{item}</p>
                        </div>
                      ))}
                    </div>
                  </section>

                  {/* Skills Developed */}
                  <section className="bg-white rounded-2xl shadow-md p-8">
                    <h2 className="text-2xl font-bold text-neutral-900 mb-6 flex items-center gap-3">
                      <span className="w-1 h-8 bg-purple-600 rounded"></span>
                      Skills Developed
                    </h2>
                    <div className="space-y-4">
                      {[
                        { title: 'Communication', desc: 'Conveying technical security concepts effectively to diverse audiences.' },
                        { title: 'Cybersecurity Expertise', desc: 'Understanding threats and implementing appropriate countermeasures.' },
                        { title: 'Data Analysis & Presentation', desc: 'Interpreting data to identify risks and presenting findings to stakeholders.' },
                        { title: 'Design Thinking', desc: 'Crafting innovative solutions for security challenges.' },
                        { title: 'Problem Solving', desc: 'Addressing cybersecurity threats with creative and effective solutions.' },
                        { title: 'Security Awareness Training', desc: 'Developing robust training initiatives to address organizational needs.' },
                        { title: 'Strategic Thinking', desc: 'Planning and executing security awareness strategies.' },
                        { title: 'Technical Security Awareness', desc: 'Acquiring a deeper understanding of technical security risks and solutions.' }
                      ].map((skill, idx) => (
                        <div key={idx} className="flex items-start gap-4 p-5 bg-gradient-to-r from-neutral-50 to-transparent rounded-xl border-l-4 border-purple-600">
                          <div className="flex-shrink-0 w-10 h-10 rounded-full bg-purple-600/10 flex items-center justify-center">
                            <svg className="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                          </div>
                          <div>
                            <p className="font-semibold text-neutral-900 mb-1">{skill.title}</p>
                            <p className="text-neutral-600">{skill.desc}</p>
                          </div>
                        </div>
                      ))}
                    </div>
                  </section>

                  {/* Testimonial/Quote Section */}
                  <section className="bg-gradient-to-br from-purple-900 to-purple-800 rounded-2xl shadow-xl p-8 text-white">
                    <div className="flex items-start gap-4">
                      <div className="flex-shrink-0 w-12 h-12 rounded-full bg-white/20 flex items-center justify-center">
                        <svg className="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <div>
                        <p className="text-neutral-100 leading-relaxed text-lg italic">
                          "I recently participated in {roomDetails.title || 'this program'}. It provided me with practical insights into the responsibilities and real-world challenges. During this program, I worked on identifying threats, creating tailored training courses, and analyzing data to enhance security protocols. This experience affirmed my interest in cybersecurity and my enthusiasm for contributing to company mission by applying my skills in strategy, problem-solving, and technical security awareness."
                        </p>
                      </div>
                    </div>
                  </section>

                  {/* Action Buttons */}
                  {roomDetails.room_url && (
                    <div className="flex justify-center gap-4">
                      <a
                        href={roomDetails.room_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center justify-center px-8 py-4 bg-purple-600 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:scale-105"
                      >
                        <svg className="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Visit the Room
                      </a>
                    </div>
                  )}
                </div>
              </div>
            </div>
          )}

      <div 
        className="w-full h-full px-6 md:px-10 lg:px-12 flex items-center" 
        style={{ 
          height: '100%',
          backgroundImage: 'none',
          background: 'transparent'
        }}
      >
        <div 
          className="w-full h-full flex flex-col md:flex-row items-start gap-6 md:gap-8 lg:gap-10" 
          style={{ 
            height: '100%',
            backgroundImage: 'none',
            background: 'transparent'
          }}
        >
          {/* LEFT SECTION: Content based on active tab */}
               <div className="w-full flex-1 min-w-0" style={{ height: '100%', width: '66.666667%', overflow: (activeTab === 'certificates' || activeTab === 'games' || activeTab === 'rooms' || activeTab === 'badges' || activeTab === 'simulations' || activeTab === 'programs') ? 'hidden' : 'auto' }}>
                 {activeTab === 'certificates' ? (
              certificatesWithImages.length === 0 ? (
                <div className="flex items-center justify-center h-full">
                  <p className="text-neutral-500 text-center">
                    No certificates available. Add images to display them here.
                  </p>
                </div>
              ) : (
                <div className="carousel-wrapper" style={{ height: '100%', paddingBottom: '0.5rem' }}>
                  <div className="carousel-container" style={{ width: '200%', height: '100%' }}>
                    {/* First set of content */}
                    <div className="flex gap-4" style={{ width: '50%', height: '100%', background: '#fafafa', backgroundImage: 'none' }}>
                      {/* Main Grid - First 4 Images */}
                      <div 
                        className="grid gap-3 flex-shrink-0"
                        style={{ 
                          gridTemplateColumns: 'repeat(3, 1fr)',
                          gridTemplateRows: 'repeat(2, 1fr)',
                          width: '100%',
                          minWidth: '600px',
                          height: '100%',
                          maxHeight: '100%'
                        }}
                      >
                        {certificatesWithImages.slice(0, 4).map((item, index) => {
                          const gridConfig = getGridSpan(index, 4);
                          const delay = index * 100;
                          
                          // Optimized 4-image layout: 2 rows, 3 columns
                          let gridColumn, gridRow;
                          
                          if (index === 0) {
                            gridColumn = '1 / 3';
                            gridRow = '1 / 2';
                          } else if (index === 1) {
                            gridColumn = '3 / 4';
                            gridRow = '1 / 2';
                          } else if (index === 2) {
                            gridColumn = '1 / 2';
                            gridRow = '2 / 3';
                          } else if (index === 3) {
                            gridColumn = '2 / 4';
                            gridRow = '2 / 3';
                          } else {
                            gridColumn = 'auto';
                            gridRow = 'auto';
                          }
                          
                          return (
                            <div
                              key={item.id || index}
                              className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${gridConfig.rotation}`}
                              style={{
                                animationDelay: isVisible ? `${delay}ms` : '0ms',
                                gridColumn: gridColumn,
                                gridRow: gridRow,
                                width: '100%',
                                height: '100%',
                                backgroundImage: 'none',
                                background: 'transparent',
                                position: 'relative',
                                zIndex: 1,
                                isolation: 'isolate',
                                contain: 'layout style paint'
                              }}
                            >
                              <img
                                src={item.imageUrl}
                                alt={item.title || `Certificate ${index + 1}`}
                                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                style={{ 
                                  display: 'block', 
                                  backgroundImage: 'none', 
                                  background: 'transparent',
                                  position: 'absolute',
                                  top: 0,
                                  left: 0,
                                  right: 0,
                                  bottom: 0,
                                  width: '100%',
                                  height: '100%',
                                  zIndex: 1,
                                  objectFit: 'cover',
                                  contain: 'layout style paint'
                                }}
                                loading="lazy"
                                onError={(e) => {
                                  e.target.style.display = 'none';
                                  console.error('Image failed to load:', item.imageUrl);
                                }}
                              />
                              <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                              {/* Action buttons - appear on hover */}
                              <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button
                                  onClick={(e) => handleViewImage(item.imageUrl, e)}
                                  className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                  title="View full size"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                  </svg>
                                  <span className="text-xs font-medium">View</span>
                                </button>
                                <button
                                  onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                  className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                  title="Download image"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                  </svg>
                                  <span className="text-xs font-medium">Download</span>
                                </button>
                              </div>
                            </div>
                          );
                        })}
                    </div>

                    {/* Additional Images - Scrollable to the right */}
                    {certificatesWithImages.length > 4 && (
                      <div className="flex gap-4 flex-shrink-0">
                        {certificatesWithImages.slice(4).map((item, index) => {
                          const actualIndex = 4 + index;
                          const delay = actualIndex * 100;
                          return (
                            <div
                              key={`first-${item.id || actualIndex}`}
                              className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${getGridSpan(actualIndex, certificatesWithImages.length).rotation}`}
                              style={{
                                animationDelay: isVisible ? `${delay}ms` : '0ms',
                                width: '300px',
                                height: '100%',
                                minHeight: '400px',
                                flexShrink: 0,
                                backgroundImage: 'none',
                                background: 'transparent',
                                position: 'relative',
                                zIndex: 1,
                                isolation: 'isolate'
                              }}
                            >
                              <img
                                src={item.imageUrl}
                                alt={item.title || `Certificate ${actualIndex + 1}`}
                                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                style={{ 
                                  display: 'block', 
                                  backgroundImage: 'none', 
                                  background: 'transparent',
                                  position: 'absolute',
                                  top: 0,
                                  left: 0,
                                  width: '100%',
                                  height: '100%',
                                  zIndex: 1
                                }}
                                loading="lazy"
                                onError={(e) => {
                                  e.target.style.display = 'none';
                                }}
                              />
                              <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                              {/* Action buttons - appear on hover */}
                              <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button
                                  onClick={(e) => handleViewImage(item.imageUrl, e)}
                                  className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                  title="View full size"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                  </svg>
                                  <span className="text-xs font-medium">View</span>
                                </button>
                                <button
                                  onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                  className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                  title="Download image"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                  </svg>
                                  <span className="text-xs font-medium">Download</span>
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    )}
                  </div>

                  {/* Duplicated set for seamless loop */}
                  <div className="flex gap-4" style={{ width: '50%', height: '100%', background: '#fafafa', backgroundImage: 'none' }}>
                    {/* Main Grid - First 4 Images (duplicated) */}
                    <div 
                      className="grid gap-3 flex-shrink-0"
                      style={{ 
                        gridTemplateColumns: 'repeat(3, 1fr)',
                        gridTemplateRows: 'repeat(2, 1fr)',
                        width: '100%',
                        minWidth: '600px',
                        height: '100%',
                        maxHeight: '100%'
                      }}
                    >
                      {certificatesWithImages.slice(0, 4).map((item, index) => {
                        const gridConfig = getGridSpan(index, 4);
                        const delay = index * 100;
                        
                        let gridColumn, gridRow;
                        
                        if (index === 0) {
                          gridColumn = '1 / 3';
                          gridRow = '1 / 2';
                        } else if (index === 1) {
                          gridColumn = '3 / 4';
                          gridRow = '1 / 2';
                        } else if (index === 2) {
                          gridColumn = '1 / 2';
                          gridRow = '2 / 3';
                        } else if (index === 3) {
                          gridColumn = '2 / 4';
                          gridRow = '2 / 3';
                        } else {
                          gridColumn = 'auto';
                          gridRow = 'auto';
                        }
                        
                        return (
                          <div
                            key={`duplicate-${item.id || index}`}
                            className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${gridConfig.rotation}`}
                            style={{
                              animationDelay: isVisible ? `${delay}ms` : '0ms',
                              gridColumn: gridColumn,
                              gridRow: gridRow,
                              width: '100%',
                              height: '100%',
                              backgroundImage: 'none',
                              background: 'transparent',
                              position: 'relative',
                              zIndex: 1,
                              isolation: 'isolate'
                            }}
                          >
                            <img
                              src={item.imageUrl}
                              alt={item.title || `Certificate ${index + 1}`}
                              className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                              style={{ 
                                display: 'block', 
                                backgroundImage: 'none', 
                                background: 'transparent',
                                position: 'absolute',
                                top: 0,
                                left: 0,
                                width: '100%',
                                height: '100%',
                                zIndex: 1
                              }}
                              loading="lazy"
                              onError={(e) => {
                                e.target.style.display = 'none';
                              }}
                            />
                            <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                            {/* Action buttons - appear on hover */}
                            <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                              <button
                                onClick={(e) => handleViewImage(item.imageUrl, e)}
                                className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                title="View full size"
                              >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <span className="text-xs font-medium">View</span>
                              </button>
                              <button
                                onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                title="Download image"
                              >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span className="text-xs font-medium">Download</span>
                              </button>
                            </div>
                          </div>
                        );
                      })}
                    </div>

                    {/* Additional Images (duplicated) */}
                    {certificatesWithImages.length > 4 && (
                      <div className="flex gap-4 flex-shrink-0">
                        {certificatesWithImages.slice(4).map((item, index) => {
                          const actualIndex = 4 + index;
                          const delay = actualIndex * 100;
                          return (
                            <div
                              key={`duplicate-extra-${item.id || actualIndex}`}
                              className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${getGridSpan(actualIndex, certificatesWithImages.length).rotation}`}
                              style={{
                                animationDelay: isVisible ? `${delay}ms` : '0ms',
                                width: '300px',
                                height: '100%',
                                minHeight: '400px',
                                flexShrink: 0,
                                backgroundImage: 'none',
                                background: 'transparent',
                                position: 'relative',
                                zIndex: 1,
                                isolation: 'isolate'
                              }}
                            >
                              <img
                                src={item.imageUrl}
                                alt={item.title || `Certificate ${actualIndex + 1}`}
                                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                style={{ 
                                  display: 'block', 
                                  backgroundImage: 'none', 
                                  background: 'transparent',
                                  position: 'absolute',
                                  top: 0,
                                  left: 0,
                                  width: '100%',
                                  height: '100%',
                                  zIndex: 1
                                }}
                                loading="lazy"
                                onError={(e) => {
                                  e.target.style.display = 'none';
                                }}
                              />
                              <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                              {/* Action buttons - appear on hover */}
                              <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <button
                                  onClick={(e) => handleViewImage(item.imageUrl, e)}
                                  className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                  title="View full size"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                  </svg>
                                  <span className="text-xs font-medium">View</span>
                                </button>
                                <button
                                  onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                  className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                  title="Download image"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                  </svg>
                                  <span className="text-xs font-medium">Download</span>
                                </button>
                              </div>
                            </div>
                          );
                        })}
                      </div>
                    )}
                  </div>
                  </div>
                </div>
              )
            ) : activeTab === 'courses' ? (
                   // COURSES: Image Left, Text Right Layout
                   coursesWithImages.length === 0 ? (
                     <div className="flex items-center justify-center h-full">
                       <p className="text-neutral-500 text-center">
                         No courses available. Add courses to display them here.
                       </p>
                     </div>
                   ) : (
                <div className="flex flex-col gap-4 sm:gap-5 md:gap-6 h-full overflow-y-auto px-2 sm:px-3 md:pr-2" style={{ paddingRight: '0.5rem' }}>
                  {coursesWithImages.map((course, index) => {
                    const delay = index * 100;
                    // Check if course has a real database ID (numeric, not virtual)
                    const isRealCourse = course.id && !String(course.id).startsWith('virtual');
                    const courseUrl = isRealCourse ? `/courses/${course.id}` : null;
                    
                    const handleCardClick = (e) => {
                      // Prevent click if clicking on the verify link
                      if (e.target.closest('a')) {
                        e.stopPropagation();
                        return;
                      }
                      if (isRealCourse && course.id) {
                        handleCourseClick(course.id, e);
                      } else {
                        alert(`This course "${course.title || 'Course'}" doesn't have a detail page yet. Please create it in the admin panel first.`);
                      }
                    };
                    
                    return (
                      <div
                        key={course.id || index}
                        className={`cert-image-animate flex flex-col md:flex-row gap-3 sm:gap-4 md:gap-6 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden ${index % 2 === 0 ? 'md:flex-row' : 'md:flex-row-reverse'} ${isRealCourse ? 'cursor-pointer' : ''}`}
                        style={{
                          animationDelay: isVisible ? `${delay}ms` : '0ms',
                          minHeight: '200px'
                        }}
                        onClick={handleCardClick}
                        role={isRealCourse ? "button" : undefined}
                        tabIndex={isRealCourse ? 0 : undefined}
                        onKeyDown={isRealCourse ? (e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            handleCardClick(e);
                          }
                        } : undefined}
                      >
                        {/* Image Section - Full width on mobile, fixed width on desktop */}
                        <div className="flex-shrink-0 w-full md:w-1/3" style={{ pointerEvents: isRealCourse ? 'none' : 'auto' }}>
                          <img
                            src={course.imageUrl}
                            alt={course.title || `Course ${index + 1}`}
                            className="w-full h-full object-cover"
                            style={{ 
                              display: 'block', 
                              backgroundImage: 'none', 
                              background: 'transparent',
                              height: '200px',
                              minHeight: '200px',
                              maxHeight: '300px',
                              width: '100%',
                              objectFit: 'cover',
                              pointerEvents: 'none'
                            }}
                            loading="lazy"
                            onError={(e) => {
                              e.target.style.display = 'none';
                            }}
                          />
                        </div>
                        
                        {/* Text Section - Full width on mobile, flex-1 on desktop */}
                        <div className="flex-1 flex flex-col justify-center p-4 sm:p-5 md:p-6 lg:p-8">
                          <h3 className="text-lg sm:text-xl md:text-2xl font-bold text-neutral-900 mb-2">
                            {course.title || `Course ${index + 1}`}
                          </h3>
                          {course.provider && (
                            <p className="text-xs sm:text-sm md:text-base text-neutral-600 mb-2 sm:mb-3">
                              {course.provider}
                            </p>
                          )}
                          {course.credential_id && (
                            <p className="text-xs md:text-sm text-neutral-500 mb-1 sm:mb-2">
                              <span className="font-semibold">Credential ID:</span> {course.credential_id}
                            </p>
                          )}
                          {course.issued_at && (
                            <p className="text-xs md:text-sm text-neutral-500">
                              <span className="font-semibold">Completed:</span> {new Date(course.issued_at).toLocaleDateString()}
                            </p>
                          )}
                          {course.verify_url && (
                            <a
                              href={course.verify_url}
                              target="_blank"
                              rel="noopener noreferrer"
                              onClick={(e) => e.stopPropagation()}
                              className="inline-block mt-3 sm:mt-4 px-3 sm:px-4 py-2 bg-neutral-900 text-white text-xs sm:text-sm rounded-lg hover:bg-neutral-800 transition-colors duration-200 z-10 relative"
                            >
                              Verify Certificate
                            </a>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )
            ) : activeTab === 'rooms' ? (
               // ROOMS: Card Grid Layout with Date Box Overlay - Horizontal Scrollable
               roomsWithImages.length === 0 ? (
                 <div className="flex items-center justify-center h-full">
                   <p className="text-neutral-500 text-center">
                     No rooms available. Add rooms to display them here.
                   </p>
                 </div>
               ) : (
                 <div className="rooms-carousel-wrapper" style={{ height: '100%', paddingBottom: '1rem', overflowX: 'auto', overflowY: 'hidden', paddingRight: '0.5rem' }}>
                   <style>{`
                     @keyframes roomCardZoom {
                       0%, 100% { transform: scale(1); }
                       10%, 90% { transform: scale(1.05); }
                     }
                     .room-card-with-animation {
                       animation: roomCardZoom 8s ease-in-out infinite;
                     }
                     .rooms-carousel-wrapper {
                       scrollbar-width: thin;
                       scrollbar-color: #a3a3a3 #f5f5f5;
                     }
                     .rooms-carousel-wrapper::-webkit-scrollbar {
                       height: 8px;
                     }
                     .rooms-carousel-wrapper::-webkit-scrollbar-track {
                       background: #f5f5f5;
                       border-radius: 4px;
                     }
                     .rooms-carousel-wrapper::-webkit-scrollbar-thumb {
                       background: #a3a3a3;
                       border-radius: 4px;
                     }
                     .rooms-carousel-wrapper::-webkit-scrollbar-thumb:hover {
                       background: #808080;
                     }
                   `}</style>
                   <div className="flex gap-4" style={{ height: '100%', minWidth: 'fit-content', paddingRight: '1rem' }}>
                     {/* Duplicate rooms to show 8 cards total - arrange in columns with 2 cards each */}
                     {Array.from({ length: Math.ceil(8 / 2) }, (_, colIndex) => (
                       <div key={`column-${colIndex}`} className="flex flex-col gap-4 flex-shrink-0" style={{ width: '350px', height: '100%' }}>
                         {[...roomsWithImages, ...roomsWithImages.slice(0, Math.max(0, 8 - roomsWithImages.length))].slice(0, 8).slice(colIndex * 2, colIndex * 2 + 2).map((room, cardIndex) => {
                           const index = colIndex * 2 + cardIndex;
                           const delay = index * 100;
                           const dateFormatted = formatRoomDate(room.completed_at);
                           // Calculate animation delay for zoom effect (each card zooms at different time)
                           const animationDelay = index * 1; // Each card starts 1s after the previous
                           
                           return (
                             <div
                               key={`room-${room.id || index}-${Math.floor(index / roomsWithImages.length)}`}
                               className="room-card-with-animation relative group flex-shrink-0"
                               style={{
                                 animationDelay: `${animationDelay}s`,
                                 width: '100%',
                                 height: 'calc(50% - 0.5rem)', // 2 cards per column
                                 minHeight: '320px',
                                 maxHeight: 'calc(50% - 0.5rem)',
                                 marginBottom: '0',
                                 display: 'flex',
                                 flexDirection: 'column'
                               }}
                             >
                           <div className="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-full flex flex-col" style={{ display: 'flex', flexDirection: 'column' }}>
                             {/* Image Section */}
                             <div className="relative flex-shrink-0" style={{ flex: '0 0 65%', overflow: 'hidden', background: 'transparent', minHeight: '0' }}>
                               {room.imageUrl ? (
                                 <img
                                   src={room.imageUrl}
                                   alt={room.title || `Room ${index + 1}`}
                                   className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                   style={{ 
                                     display: 'block', 
                                     backgroundImage: 'none', 
                                     background: 'transparent',
                                     width: '100%',
                                     height: '100%'
                                   }}
                                   loading="lazy"
                                   onError={(e) => {
                                     e.target.style.display = 'none';
                                   }}
                                 />
                               ) : (
                                 <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-200 to-purple-300">
                                   <svg className="w-32 h-32 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                   </svg>
                                 </div>
                               )}
                               
                               {/* Date Box Overlay - Bottom Left */}
                               {dateFormatted && (
                                 <div 
                                   className="absolute bottom-3 left-3 bg-amber-50 px-3 py-1.5 rounded shadow-lg z-10"
                                   style={{
                                     backgroundColor: '#fefcf3', // Light beige/off-white
                                     boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)'
                                   }}
                                 >
                                   <p 
                                     className="text-neutral-900 font-serif text-sm"
                                     style={{
                                       fontFamily: 'Georgia, "Times New Roman", serif',
                                       fontWeight: 'normal',
                                       whiteSpace: 'nowrap'
                                     }}
                                   >
                                     {dateFormatted}
                                   </p>
                                 </div>
                               )}
                             </div>

                             {/* Title Section - Below Image */}
                             <div className="p-3 bg-white flex-shrink-0 flex flex-col justify-between" style={{ flex: '0 0 35%', minHeight: '90px', maxHeight: '35%' }}>
                               <div style={{ flexShrink: 0, marginBottom: '8px' }}>
                                 <h3 
                                   className="font-serif text-neutral-900 leading-tight text-base mb-1"
                                   style={{
                                     fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif',
                                     fontWeight: 'normal',
                                     color: '#7c3aed', // Dark purple/maroon color
                                     lineHeight: '1.2'
                                   }}
                                 >
                                   {room.title || `Room ${index + 1}`}
                                 </h3>
                                 {room.platform && (
                                   <p className="text-xs text-neutral-600" style={{ lineHeight: '1.2', marginTop: '2px' }}>
                                     {room.platform}
                                   </p>
                                 )}
                               </div>
                               {/* Action Buttons - Both on same line */}
                               <div className="flex gap-2" style={{ flexShrink: 0, marginTop: 'auto' }}>
                                 <a
                                   href={room.room_url || '#'}
                                   target={room.room_url ? "_blank" : "_self"}
                                   rel={room.room_url ? "noopener noreferrer" : undefined}
                                   onClick={(e) => {
                                     if (!room.room_url) {
                                       e.preventDefault();
                                       e.stopPropagation();
                                       alert('Room URL not available. Please add it in the admin panel.');
                                     } else {
                                       e.stopPropagation();
                                     }
                                   }}
                                   className={`flex-1 px-2 py-1.5 text-white text-xs font-semibold rounded-lg transition-colors duration-200 text-center ${
                                     room.room_url 
                                       ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer' 
                                       : 'bg-purple-400 hover:bg-purple-500 cursor-not-allowed opacity-75'
                                   }`}
                                   style={{ lineHeight: '1.2', minHeight: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                 >
                                   Visit the Room
                                 </a>
                                 <button
                                   onClick={(e) => handleRoomSummaryClick(room, e)}
                                   className="flex-1 px-2 py-1.5 bg-neutral-900 text-white text-xs font-semibold rounded-lg hover:bg-neutral-800 transition-colors duration-200"
                                   style={{ lineHeight: '1.2', minHeight: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                 >
                                   Summary of Room
                                 </button>
                               </div>
                             </div>
                           </div>
                         </div>
                       );
                         })}
                       </div>
                     ))}
                   </div>
                 </div>
               )
            ) : activeTab === 'games' ? (
               // GAMES: Certificate-style Grid Layout with View/Download buttons
               gamesWithImages.length === 0 ? (
                 <div className="flex items-center justify-center h-full">
                   <p className="text-neutral-500 text-center">
                     No games available. Add games to display them here.
                   </p>
                 </div>
               ) : (
                 <div className="carousel-wrapper" style={{ height: '100%', paddingBottom: '0.5rem' }}>
                   <div className="carousel-container" style={{ width: '200%', height: '100%' }}>
                     {/* First set of content */}
                     <div className="flex gap-4" style={{ width: '50%', height: '100%', background: '#fafafa', backgroundImage: 'none' }}>
                       {/* Main Grid - First 4 Images */}
                       <div 
                         className="grid gap-3 flex-shrink-0"
                         style={{ 
                           gridTemplateColumns: 'repeat(3, 1fr)',
                           gridTemplateRows: 'repeat(2, 1fr)',
                           width: '100%',
                           minWidth: '600px',
                           height: '100%',
                           maxHeight: '100%'
                         }}
                       >
                         {gamesWithImages.slice(0, 4).map((item, index) => {
                           const gridConfig = getGridSpan(index, 4);
                           const delay = index * 100;
                           
                           let gridColumn, gridRow;
                           
                           if (index === 0) {
                             gridColumn = '1 / 3';
                             gridRow = '1 / 2';
                           } else if (index === 1) {
                             gridColumn = '3 / 4';
                             gridRow = '1 / 2';
                           } else if (index === 2) {
                             gridColumn = '1 / 2';
                             gridRow = '2 / 3';
                           } else if (index === 3) {
                             gridColumn = '2 / 4';
                             gridRow = '2 / 3';
                           } else {
                             gridColumn = 'auto';
                             gridRow = 'auto';
                           }
                           
                           return (
                             <div
                               key={item.id || index}
                               className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${gridConfig.rotation}`}
                               style={{
                                 animationDelay: isVisible ? `${delay}ms` : '0ms',
                                 gridColumn: gridColumn,
                                 gridRow: gridRow,
                                 width: '100%',
                                 height: '100%',
                                 backgroundImage: 'none',
                                 background: 'transparent',
                                 position: 'relative',
                                 zIndex: 1,
                                 isolation: 'isolate',
                                 contain: 'layout style paint'
                               }}
                             >
                               <img
                                 src={item.imageUrl}
                                 alt={item.title || `Game ${index + 1}`}
                                 className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                 style={{ 
                                   display: 'block', 
                                   backgroundImage: 'none', 
                                   background: 'transparent',
                                   position: 'absolute',
                                   top: 0,
                                   left: 0,
                                   right: 0,
                                   bottom: 0,
                                   width: '100%',
                                   height: '100%',
                                   zIndex: 1,
                                   objectFit: 'cover',
                                   contain: 'layout style paint'
                                 }}
                                 loading="lazy"
                                 onError={(e) => {
                                   e.target.style.display = 'none';
                                   console.error('Image failed to load:', item.imageUrl);
                                 }}
                               />
                               <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                               {/* Action buttons - appear on hover */}
                               <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                 <button
                                   onClick={(e) => handleViewImage(item.imageUrl, e)}
                                   className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                   title="View full size"
                                 >
                                   <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                   </svg>
                                   <span className="text-xs font-medium">View</span>
                                 </button>
                                 <button
                                   onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                   className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                   title="Download image"
                                 >
                                   <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                   </svg>
                                   <span className="text-xs font-medium">Download</span>
                                 </button>
                               </div>
                             </div>
                           );
                         })}
                       </div>

                       {/* Additional Images - Scrollable to the right */}
                       {gamesWithImages.length > 4 && (
                         <div className="flex gap-4 flex-shrink-0">
                           {gamesWithImages.slice(4).map((item, index) => {
                             const actualIndex = 4 + index;
                             const delay = actualIndex * 100;
                             return (
                               <div
                                 key={`first-game-${item.id || actualIndex}`}
                                 className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${getGridSpan(actualIndex, gamesWithImages.length).rotation}`}
                                 style={{
                                   animationDelay: isVisible ? `${delay}ms` : '0ms',
                                   width: '300px',
                                   height: '100%',
                                   minHeight: '400px',
                                   flexShrink: 0,
                                   backgroundImage: 'none',
                                   background: 'transparent',
                                   position: 'relative',
                                   zIndex: 1,
                                   isolation: 'isolate'
                                 }}
                               >
                                 <img
                                   src={item.imageUrl}
                                   alt={item.title || `Game ${actualIndex + 1}`}
                                   className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                   style={{ 
                                     display: 'block', 
                                     backgroundImage: 'none', 
                                     background: 'transparent',
                                     position: 'absolute',
                                     top: 0,
                                     left: 0,
                                     width: '100%',
                                     height: '100%',
                                     zIndex: 1
                                   }}
                                   loading="lazy"
                                   onError={(e) => {
                                     e.target.style.display = 'none';
                                   }}
                                 />
                                 <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                                 {/* Action buttons - appear on hover */}
                                 <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                   <button
                                     onClick={(e) => handleViewImage(item.imageUrl, e)}
                                     className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                     title="View full size"
                                   >
                                     <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                     </svg>
                                     <span className="text-xs font-medium">View</span>
                                   </button>
                                   <button
                                     onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                     className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                     title="Download image"
                                   >
                                     <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                     </svg>
                                     <span className="text-xs font-medium">Download</span>
                                   </button>
                                 </div>
                               </div>
                             );
                           })}
                         </div>
                       )}
                     </div>

                     {/* Duplicated set for seamless loop */}
                     <div className="flex gap-4" style={{ width: '50%', height: '100%', background: '#fafafa', backgroundImage: 'none' }}>
                       {/* Main Grid - First 4 Images (duplicated) */}
                       <div 
                         className="grid gap-3 flex-shrink-0"
                         style={{ 
                           gridTemplateColumns: 'repeat(3, 1fr)',
                           gridTemplateRows: 'repeat(2, 1fr)',
                           width: '100%',
                           minWidth: '600px',
                           height: '100%',
                           maxHeight: '100%'
                         }}
                       >
                         {gamesWithImages.slice(0, 4).map((item, index) => {
                           const gridConfig = getGridSpan(index, 4);
                           const delay = index * 100;
                           
                           let gridColumn, gridRow;
                           
                           if (index === 0) {
                             gridColumn = '1 / 3';
                             gridRow = '1 / 2';
                           } else if (index === 1) {
                             gridColumn = '3 / 4';
                             gridRow = '1 / 2';
                           } else if (index === 2) {
                             gridColumn = '1 / 2';
                             gridRow = '2 / 3';
                           } else if (index === 3) {
                             gridColumn = '2 / 4';
                             gridRow = '2 / 3';
                           } else {
                             gridColumn = 'auto';
                             gridRow = 'auto';
                           }
                           
                           return (
                             <div
                               key={`duplicate-game-${item.id || index}`}
                               className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${gridConfig.rotation}`}
                               style={{
                                 animationDelay: isVisible ? `${delay}ms` : '0ms',
                                 gridColumn: gridColumn,
                                 gridRow: gridRow,
                                 width: '100%',
                                 height: '100%',
                                 backgroundImage: 'none',
                                 background: 'transparent',
                                 position: 'relative',
                                 zIndex: 1,
                                 isolation: 'isolate'
                               }}
                             >
                               <img
                                 src={item.imageUrl}
                                 alt={item.title || `Game ${index + 1}`}
                                 className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                 style={{ 
                                   display: 'block', 
                                   backgroundImage: 'none', 
                                   background: 'transparent',
                                   position: 'absolute',
                                   top: 0,
                                   left: 0,
                                   width: '100%',
                                   height: '100%',
                                   zIndex: 1
                                 }}
                                 loading="lazy"
                                 onError={(e) => {
                                   e.target.style.display = 'none';
                                 }}
                               />
                               <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                               {/* Action buttons - appear on hover */}
                               <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                 <button
                                   onClick={(e) => handleViewImage(item.imageUrl, e)}
                                   className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                   title="View full size"
                                 >
                                   <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                   </svg>
                                   <span className="text-xs font-medium">View</span>
                                 </button>
                                 <button
                                   onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                   className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                   title="Download image"
                                 >
                                   <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                   </svg>
                                   <span className="text-xs font-medium">Download</span>
                                 </button>
                               </div>
                             </div>
                           );
                         })}
                       </div>

                       {/* Additional Images (duplicated) */}
                       {gamesWithImages.length > 4 && (
                         <div className="flex gap-4 flex-shrink-0">
                           {gamesWithImages.slice(4).map((item, index) => {
                             const actualIndex = 4 + index;
                             const delay = actualIndex * 100;
                             return (
                               <div
                                 key={`duplicate-game-extra-${item.id || actualIndex}`}
                                 className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${getGridSpan(actualIndex, gamesWithImages.length).rotation}`}
                                 style={{
                                   animationDelay: isVisible ? `${delay}ms` : '0ms',
                                   width: '300px',
                                   height: '100%',
                                   minHeight: '400px',
                                   flexShrink: 0,
                                   backgroundImage: 'none',
                                   background: 'transparent',
                                   position: 'relative',
                                   zIndex: 1,
                                   isolation: 'isolate'
                                 }}
                               >
                                 <img
                                   src={item.imageUrl}
                                   alt={item.title || `Game ${actualIndex + 1}`}
                                   className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                   style={{ 
                                     display: 'block', 
                                     backgroundImage: 'none', 
                                     background: 'transparent',
                                     position: 'absolute',
                                     top: 0,
                                     left: 0,
                                     width: '100%',
                                     height: '100%',
                                     zIndex: 1
                                   }}
                                   loading="lazy"
                                   onError={(e) => {
                                     e.target.style.display = 'none';
                                   }}
                                 />
                                 <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                                 {/* Action buttons - appear on hover */}
                                 <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                   <button
                                     onClick={(e) => handleViewImage(item.imageUrl, e)}
                                     className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                     title="View full size"
                                   >
                                     <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                     </svg>
                                     <span className="text-xs font-medium">View</span>
                                   </button>
                                   <button
                                     onClick={(e) => handleDownloadImage(item.imageUrl, item.title, e)}
                                     className="bg-white/90 hover:bg-white text-neutral-900 px-3 py-2 rounded-lg shadow-lg flex items-center gap-2 transition-colors duration-200"
                                     title="Download image"
                                   >
                                     <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                     </svg>
                                     <span className="text-xs font-medium">Download</span>
                                   </button>
                                 </div>
                               </div>
                             );
                           })}
                         </div>
                       )}
                     </div>
                   </div>
                 </div>
               )
            ) : (activeTab === 'badges' || activeTab === 'simulations' || activeTab === 'programs') ? (
               // BADGES/SIMULATIONS/PROGRAMS: Room-style Card Layout
               (() => {
                 const activeData = activeTab === 'badges' ? badgesWithImages : 
                                   (activeTab === 'simulations' ? simulationsWithImages : programsWithImages);
                 
                 if (activeData.length === 0) {
                   return (
                     <div className="flex items-center justify-center h-full">
                       <p className="text-neutral-500 text-center">
                         No {activeSubtitle.toLowerCase()} available. Add {activeSubtitle.toLowerCase()} to display them here.
                       </p>
                     </div>
                   );
                 }
                 
                 return (
                   <div className="rooms-carousel-wrapper" style={{ height: '100%', paddingBottom: '1rem', overflowX: 'auto', overflowY: 'hidden', paddingRight: '0.5rem' }}>
                     <div className="flex gap-4" style={{ height: '100%', minWidth: 'fit-content', paddingRight: '1rem' }}>
                       {/* Arrange in columns with 2 cards each */}
                       {Array.from({ length: Math.ceil(8 / 2) }, (_, colIndex) => (
                         <div key={`column-${colIndex}`} className="flex flex-col gap-4 flex-shrink-0" style={{ width: '350px', height: '100%' }}>
                           {activeData.slice(0, 8).slice(colIndex * 2, colIndex * 2 + 2).map((item, cardIndex) => {
                             const index = colIndex * 2 + cardIndex;
                             const delay = index * 100;
                             const dateFormatted = formatRoomDate(item.completed_at || item.issued_at);
                             const animationDelay = index * 1;
                             
                             return (
                               <div
                                 key={`${activeTab}-${item.id || index}-${Math.floor(index / activeData.length)}`}
                                 className="room-card-with-animation relative group flex-shrink-0"
                                 style={{
                                   animationDelay: `${animationDelay}s`,
                                   width: '100%',
                                   height: 'calc(50% - 0.5rem)',
                                   minHeight: '320px',
                                   maxHeight: 'calc(50% - 0.5rem)',
                                   marginBottom: '0',
                                   display: 'flex',
                                   flexDirection: 'column'
                                 }}
                               >
                                 <div className="bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden h-full flex flex-col" style={{ display: 'flex', flexDirection: 'column' }}>
                                   {/* Image Section */}
                                   <div className="relative flex-shrink-0" style={{ flex: '0 0 65%', overflow: 'hidden', background: 'transparent', minHeight: '0' }}>
                                     {item.imageUrl ? (
                                       <img
                                         src={item.imageUrl}
                                         alt={item.title || `${activeSubtitle} ${index + 1}`}
                                         className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                         style={{ 
                                           display: 'block', 
                                           backgroundImage: 'none', 
                                           background: 'transparent',
                                           width: '100%',
                                           height: '100%'
                                         }}
                                         loading="lazy"
                                         onError={(e) => {
                                           e.target.style.display = 'none';
                                         }}
                                       />
                                     ) : (
                                       <div className="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-200 to-purple-300">
                                         <svg className="w-32 h-32 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                         </svg>
                                       </div>
                                     )}
                                     
                                     {/* Date Box Overlay - Bottom Left */}
                                     {dateFormatted && (
                                       <div 
                                         className="absolute bottom-3 left-3 bg-amber-50 px-3 py-1.5 rounded shadow-lg z-10"
                                         style={{
                                           backgroundColor: '#fefcf3',
                                           boxShadow: '0 4px 6px rgba(0, 0, 0, 0.1)'
                                         }}
                                       >
                                         <p 
                                           className="text-neutral-900 font-serif text-sm"
                                           style={{
                                             fontFamily: 'Georgia, "Times New Roman", serif',
                                             fontWeight: 'normal',
                                             whiteSpace: 'nowrap'
                                           }}
                                         >
                                           {dateFormatted}
                                         </p>
                                       </div>
                                     )}
                                   </div>

                                   {/* Title Section - Below Image */}
                                   <div className="p-3 bg-white flex-shrink-0 flex flex-col justify-between" style={{ flex: '0 0 35%', minHeight: '90px', maxHeight: '35%' }}>
                                     <div style={{ flexShrink: 0, marginBottom: '8px' }}>
                                       <h3 
                                         className="font-serif text-neutral-900 leading-tight text-base mb-1"
                                         style={{
                                           fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif',
                                           fontWeight: 'normal',
                                           color: '#7c3aed',
                                           lineHeight: '1.2'
                                         }}
                                       >
                                         {item.title || `${activeSubtitle} ${index + 1}`}
                                       </h3>
                                       {item.platform && (
                                         <p className="text-xs text-neutral-600" style={{ lineHeight: '1.2', marginTop: '2px' }}>
                                           {item.platform}
                                         </p>
                                       )}
                                     </div>
                                     {/* Action Buttons - Both on same line */}
                                     <div className="flex gap-2" style={{ flexShrink: 0, marginTop: 'auto' }}>
                                       <a
                                         href={item.room_url || item.verify_url || item.url || '#'}
                                         target={(item.room_url || item.verify_url || item.url) ? "_blank" : "_self"}
                                         rel={(item.room_url || item.verify_url || item.url) ? "noopener noreferrer" : undefined}
                                         onClick={(e) => {
                                           if (!(item.room_url || item.verify_url || item.url)) {
                                             e.preventDefault();
                                             e.stopPropagation();
                                             alert(`${activeSubtitle} URL not available. Please add it in the admin panel.`);
                                           } else {
                                             e.stopPropagation();
                                           }
                                         }}
                                         className={`flex-1 px-2 py-1.5 text-white text-xs font-semibold rounded-lg transition-colors duration-200 text-center ${
                                           (item.room_url || item.verify_url || item.url)
                                             ? 'bg-purple-600 hover:bg-purple-700 cursor-pointer' 
                                             : 'bg-purple-400 hover:bg-purple-500 cursor-not-allowed opacity-75'
                                         }`}
                                         style={{ lineHeight: '1.2', minHeight: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                       >
                                         {activeTab === 'badges' ? 'View Badge' : activeTab === 'simulations' ? 'Visit Simulation' : 'Visit Program'}
                                       </a>
                                       <button
                                         onClick={(e) => handleRoomSummaryClick(item, e)}
                                         className="flex-1 px-2 py-1.5 bg-neutral-900 text-white text-xs font-semibold rounded-lg hover:bg-neutral-800 transition-colors duration-200"
                                         style={{ lineHeight: '1.2', minHeight: '32px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}
                                       >
                                         Summary
                                       </button>
                                     </div>
                                   </div>
                                 </div>
                               </div>
                             );
                           })}
                         </div>
                       ))}
                     </div>
                   </div>
                 );
               })()
            ) : null}
          </div>

          {/* RIGHT SECTION: Brand Title & Subtitle + Tabs */}
          <div 
            className="w-full md:w-1/3 flex flex-col justify-center"
            style={{
              animation: isVisible ? 'fadeInUp 0.8s ease-out forwards' : 'none',
              opacity: isVisible ? 1 : 0,
              height: '100%'
            }}
          >
            <div>
              <h2 
                className="text-[clamp(2.5rem,6vw,5rem)] font-black tracking-tight leading-[0.9] uppercase text-neutral-900 select-none"
                style={{
                  fontFamily: 'system-ui, -apple-system, sans-serif'
                }}
              >
                {brandTitle}
              </h2>
{/* Dynamic tabs from NavLinks or fallback to hardcoded tabs */}
{tabs.length > 0 ? (
  tabs.map(tab => (
    <p
      key={tab.key}
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === tab.key
          ? 'text-neutral-900 font-semibold'
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab(tab.key)}
    >
      {tab.title}
    </p>
  ))
) : (
  <>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'certificates' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('certificates')}
    >
      Certificates
    </p>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'courses' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('courses')}
    >
      Courses
    </p>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'rooms' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('rooms')}
    >
      Rooms
    </p>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'badges' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('badges')}
    >
      Badges
    </p>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'games' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('games')}
    >
      Games
    </p>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'simulations' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('simulations')}
    >
      Simulation
    </p>
    <p 
      className={`text-2xl md:text-3xl font-serif mt-1 cursor-pointer transition-all duration-300 ${
        activeTab === 'programs' 
          ? 'text-neutral-900 font-semibold' 
          : 'text-neutral-500 hover:text-neutral-700'
      }`}
      style={{
        fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
      }}
      onClick={() => setActiveTab('programs')}
    >
      Programs
    </p>
  </>
)}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};

export default TryHackMeSection;

