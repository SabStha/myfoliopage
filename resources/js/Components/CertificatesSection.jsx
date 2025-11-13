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

// Helper function to extract slug and type from URL (handles both relative and absolute URLs)
const extractContentFromUrl = (url) => {
  if (!url) return null;
  
  // Handle both relative (/book-pages/...) and absolute (http://localhost:8000/book-pages/...) URLs
  const urlObj = url.startsWith('http://') || url.startsWith('https://') 
    ? new URL(url) 
    : { pathname: url };
  
  const pathParts = urlObj.pathname.split('/').filter(p => p);
  
  if (pathParts.length >= 2) {
    const typePart = pathParts[0]; // book-pages, code-summaries, rooms
    const slug = pathParts[1];
    
    // Map URL path to modal type (for modal opening) and API path (for API calls)
    let type = null;
    let apiPath = null;
    
    if (typePart === 'book-pages') {
      type = 'book-page'; // For modal type
      apiPath = 'book-pages'; // For API path (plural)
    } else if (typePart === 'code-summaries') {
      type = 'code-summary'; // For modal type
      apiPath = 'code-summaries'; // For API path (plural)
    } else if (typePart === 'rooms') {
      type = 'room'; // For modal type
      apiPath = 'rooms'; // For API path (plural)
    }
    
    if (type && slug && apiPath) {
      return { type, slug, apiPath };
    }
  }
  
  return null;
};

// Helper function to handle card clicks - always opens modal, never navigates
const handleCardClick = (item, e) => {
  // Always prevent default navigation FIRST
  if (e) {
    if (typeof e.preventDefault === 'function') e.preventDefault();
    if (typeof e.stopPropagation === 'function') e.stopPropagation();
    // React SyntheticEvent doesn't have stopImmediatePropagation, use nativeEvent if needed
    if (e.nativeEvent && typeof e.nativeEvent.stopImmediatePropagation === 'function') {
      e.nativeEvent.stopImmediatePropagation();
    }
  }
  
  console.log('=== Card Click Handler ===');
  console.log('Item:', item);
  console.log('Item.isSection:', item.isSection);
  console.log('Item.id:', item.id);
  console.log('Event:', e);
  console.log('window.openContentModal exists?', typeof window.openContentModal);
  console.log('window.openSectionContentModal exists?', typeof window.openSectionContentModal);
  
  // Priority 0: If this is a CategoryItem (section), open section content modal to show ALL items
  // CategoryItems are identified by the isSection flag - they can have multiple content items
  // Even if they have a linked_model, we want to show ALL content items, not just the linked one
  if (item.isSection && item.id) {
    console.log('Priority 0: Detected section, checking for openSectionContentModal...');
    
    // Try to open section modal - with retry if function not immediately available
    const tryOpenModal = () => {
      if (window.openSectionContentModal) {
        console.log('Priority 0: Opening section content modal for section ID:', item.id);
        try {
          window.openSectionContentModal(item.id);
          console.log('Section content modal opened successfully');
        } catch (error) {
          console.error('Error opening section content modal:', error);
        }
      } else {
        console.warn('window.openSectionContentModal not available yet, retrying...');
        // Retry after a short delay (might be a timing issue)
        setTimeout(() => {
          if (window.openSectionContentModal) {
            window.openSectionContentModal(item.id);
          } else {
            console.error('window.openSectionContentModal still not available after retry');
            console.log('Available window functions:', Object.keys(window).filter(k => k.includes('Section') || k.includes('Modal') || k.includes('open')));
            // Fallback: try to dispatch events directly
            try {
              window.dispatchEvent(new CustomEvent('load-section-content-modal', { 
                detail: { sectionId: item.id } 
              }));
              window.dispatchEvent(new CustomEvent('open-modal', { detail: 'section-content-modal' }));
              console.log('Fallback: Dispatched events directly');
            } catch (err) {
              console.error('Fallback also failed:', err);
            }
          }
        }, 100);
      }
    };
    
    tryOpenModal();
    return;
  }
  
  // Priority 1: Use linked_model if available (for backward compatibility, but sections should use Priority 0)
  if (item.linked_model && item.linked_model.type) {
    console.log('Priority 1: Using linked_model:', item.linked_model);
    if (window.openContentModal) {
      window.openContentModal(
        item.linked_model.type,
        item.linked_model.id,
        item.linked_model.slug
      );
      console.log('Modal opened via linked_model');
      return;
    } else {
      console.error('window.openContentModal is not defined!');
      return;
    }
  }
  
  // Priority 2: Extract from URL if it's a content page (handles both relative and absolute URLs)
  if (item.url) {
    console.log('Priority 2: Checking URL:', item.url);
    const contentData = extractContentFromUrl(item.url);
    if (contentData) {
      console.log('Priority 2: Extracted content data:', contentData);
      if (window.openContentModal) {
        // Store apiPath for later use if needed
        window.openContentModal(contentData.type, null, contentData.slug);
        console.log('Modal opened via URL');
        return;
      } else {
        console.error('window.openContentModal is not defined!');
        return;
      }
    } else {
      // URL is not a content page - check if it's external
      if (item.url.startsWith('http://') || item.url.startsWith('https://')) {
        try {
          const urlObj = new URL(item.url);
          const currentHost = window.location.host;
          // Only open in new tab if it's truly external (different domain)
          if (urlObj.host !== currentHost && urlObj.host !== 'localhost:8000' && urlObj.host !== '127.0.0.1:8000') {
            console.log('Opening external URL in new tab:', item.url);
            window.open(item.url, '_blank');
            return;
          } else {
            // Same domain but not a content page - do nothing (prevent navigation)
            console.log('Same domain URL but not content page, preventing navigation:', item.url);
            return;
          }
        } catch (e) {
          console.warn('Invalid URL format:', item.url);
          // Invalid URL - do nothing, prevent navigation
          return;
        }
      } else {
        // Relative URL that's not a content page - do nothing (prevent navigation)
        console.log('Relative URL but not content page, preventing navigation:', item.url);
        return;
      }
    }
  }
  
  // Priority 3: Try view_url if it's a content page
  if (item.view_url) {
    console.log('Priority 3: Checking view_url:', item.view_url);
    const contentData = extractContentFromUrl(item.view_url);
    if (contentData) {
      console.log('Priority 3: Extracted content data from view_url:', contentData);
      if (window.openContentModal) {
        window.openContentModal(contentData.type, null, contentData.slug);
        console.log('Modal opened via view_url');
        return;
      } else {
        console.error('window.openContentModal is not defined!');
        return;
      }
    }
  }
  
  console.warn('=== No valid modal target found ===');
  console.warn('Item:', item);
  console.warn('This should not navigate - preventDefault was called');
};

// Helper function to extract string from translatable object
const extractString = (value, fallback = '') => {
  if (typeof value === 'string') {
    // Check if it's a JSON string and decode it
    try {
      const decoded = JSON.parse(value);
      if (decoded && typeof decoded === 'object') {
        const currentLang = window.currentLocale || 'en';
        return decoded[currentLang] || decoded.en || decoded.ja || fallback;
      }
    } catch (e) {
      // Not JSON, use as-is
    }
    return value;
  }
  if (value && typeof value === 'object') {
    const currentLang = window.currentLocale || 'en';
    return value[currentLang] || value.en || value.ja || fallback;
  }
  return String(value || fallback);
};

const CertificatesSection = ({ 
  certificates = [],
  courses = [],
  rooms = [],
  badges = [],
  games = [],
  simulations = [],
  programs = [],
  brandTitle = "UDEMY",
  subtitle = "Certificates",
  className = "",
  animationStyle = 'list_alternating_cards', // 'grid_editorial_collage', 'list_alternating_cards', 'carousel_scroll_left', 'carousel_scroll_right'
  textAlignment = 'left', // 'left' or 'right' - controls which side text starts on
  subsectionConfigurations = {}, // Per-subsection animation/layout configs
  navLinks = [] // NavLinks from database - used to dynamically generate tabs
}) => {
  const sectionRef = useRef(null);
  const isVisible = useInViewOnce(sectionRef);
  
  const normalizeCategory = (category) => {
    if (!category) {
      return {
        id: null,
        name: 'Category',
        items: [],
      };
    }
    
    const normalizedName = extractString(category.name, category.slug || 'Category');
    const normalizedItems = (category.items || []).map((item) => {
      const normalizedTitle = extractString(item.title, item.slug || 'Untitled');
      const normalizedSummary = extractString(item.summary, '');
      return {
        ...item,
        title: normalizedTitle,
        summary: normalizedSummary,
      };
    });
    
    return {
      ...category,
      name: normalizedName,
      items: normalizedItems,
    };
  };
  
  const normalizeNavLink = (link) => {
    const imageUrl = getCertificateImage(link);
    const normalizedTitle = extractString(link.title, 'Untitled');
    const normalizedCategories = (link.categories || []).map(normalizeCategory);
    
    return {
      ...link,
      title: normalizedTitle,
      imageUrl,
      categories: normalizedCategories,
    };
  };
  
  const navLinksWithImages = navLinks.length > 0
    ? navLinks.map(normalizeNavLink)
    : [];
  
  // If NavLinks are provided, create tabs from individual NavLinks (sub-navs)
  // Each NavLink becomes a tab, and we'll show its categories inside
  console.log('CertificatesSection: Received navLinks prop:', navLinks);
  console.log('CertificatesSection: navLinks.length:', navLinks?.length || 0);
  console.log('CertificatesSection: textAlignment:', textAlignment);
  const tabs = navLinksWithImages.length > 0 
    ? navLinksWithImages.map(link => {
        // Debug: Log category data to verify animation_style and image_url are included
        if (link.categories && link.categories.length > 0) {
          console.log('CertificatesSection: NavLink', link.id, 'categories:', link.categories.map(c => ({
            id: c.id,
            name: c.name,
            animation_style: c.animation_style,
            image_url: c.image_url
          })));
        }
        return {
            id: String(link.id),
            title: link.title,
            key: `navlink-${link.id}`,
          categories: link.categories || [], // Store categories for this sub-nav
          navLink: link, // Store full navLink data
        };
      })
    : []; // Empty if no NavLinks - will show "no subsections" message
  console.log('CertificatesSection: Created tabs:', tabs.length, tabs);
    
  // State for selected sub-nav and category
  const [selectedSubNav, setSelectedSubNav] = useState(null); // ID of selected sub-nav
  const [selectedCategory, setSelectedCategory] = useState(null); // ID of selected category
    
  // Set initial active tab to first NavLink/category if available, otherwise fallback to certificates
  const [activeTab, setActiveTab] = useState(() => {
    const initialTabs = navLinksWithImages.length > 0 
      ? navLinksWithImages.map(link => ({
          id: String(link.id),
          title: link.title,
          key: `navlink-${link.id}`,
          categories: link.categories || [],
          navLink: link,
        }))
      : [];
    return initialTabs.length > 0 ? initialTabs[0].key : 'certificates';
  });
  
  // Update activeTab when navLinks change (e.g., when textAlignment changes and component re-renders)
  useEffect(() => {
    if (tabs.length > 0) {
      const currentTabExists = tabs.some(tab => tab.key === activeTab);
      if (!currentTabExists) {
        // If current tab doesn't exist in new tabs, switch to first tab
        console.log('CertificatesSection: activeTab not found in tabs, switching to first tab:', tabs[0].key);
        setActiveTab(tabs[0].key);
      }
    } else if (activeTab !== 'certificates') {
      // If no tabs available, fallback to certificates
      console.log('CertificatesSection: No tabs available, falling back to certificates');
      setActiveTab('certificates');
    }
  }, [tabs, activeTab, navLinks]);
  const [viewingImage, setViewingImage] = useState(null); // For modal view
  const [viewingCourse, setViewingCourse] = useState(null); // For course detail modal
  const [courseDetails, setCourseDetails] = useState(null); // Course detail data
  const [loadingCourse, setLoadingCourse] = useState(false); // Loading state for course
  const [viewingRoom, setViewingRoom] = useState(null); // For room summary modal
  const [roomDetails, setRoomDetails] = useState(null); // Room detail data

  // Refs for synchronized scrolling in Rooms style (CategoryItems)
  const row1Ref = useRef(null);
  const row2Ref = useRef(null);
  
  // Refs for synchronized scrolling in Rooms style (NavLinks fallback)
  const row1NavRef = useRef(null);
  const row2NavRef = useRef(null);

  // Synchronized scrolling effect for CategoryItems Rooms style with infinite loop
  useEffect(() => {
    const row1 = row1Ref.current;
    const row2 = row2Ref.current;
    if (!row1 || !row2) return;
    
    let isScrolling = false;
    
    const getSingleSetWidth = (element) => {
      // Calculate width of one complete set of cards
      const duplicateContainer = element.querySelector('.rooms-duplicate');
      if (!duplicateContainer) return 0;
      // Since we have duplicated items ([...firstRow, ...firstRow]), divide by 2
      const totalWidth = duplicateContainer.scrollWidth;
      return totalWidth / 2;
    };
    
    const createInfiniteLoop = (element) => {
      const singleSetWidth = getSingleSetWidth(element);
      if (singleSetWidth === 0) return;
      
      // When scrolled to or past the end of the first set (50% of total), jump back to start
      if (element.scrollLeft >= singleSetWidth) {
        element.scrollLeft = element.scrollLeft - singleSetWidth;
      }
      // When scrolled before start (shouldn't happen, but safety check)
      if (element.scrollLeft < 0) {
        element.scrollLeft = 0;
      }
    };
    
    const syncScroll = (source, target) => {
      if (isScrolling) return;
      isScrolling = true;
      
      // Create infinite loop for source
      createInfiniteLoop(source);
      
      // Sync target to same position (within first set)
      const sourceSingleSetWidth = getSingleSetWidth(source);
      const targetSingleSetWidth = getSingleSetWidth(target);
      
      if (sourceSingleSetWidth > 0 && targetSingleSetWidth > 0) {
        // Calculate scroll position within first set (0 to singleSetWidth)
        const sourceScroll = source.scrollLeft % sourceSingleSetWidth;
        // Map to target's first set
        const scrollPercent = sourceScroll / sourceSingleSetWidth;
        target.scrollLeft = scrollPercent * targetSingleSetWidth;
        
        // Create infinite loop for target as well
        createInfiniteLoop(target);
      }
      
      setTimeout(() => {
        isScrolling = false;
      }, 10);
    };
    
    const handleRow1Scroll = () => syncScroll(row1, row2);
    const handleRow2Scroll = () => syncScroll(row2, row1);
    
    row1.addEventListener('scroll', handleRow1Scroll);
    row2.addEventListener('scroll', handleRow2Scroll);
    
    return () => {
      row1.removeEventListener('scroll', handleRow1Scroll);
      row2.removeEventListener('scroll', handleRow2Scroll);
    };
  }, [selectedCategory]); // Re-run when category changes

  // Synchronized scrolling effect for NavLinks Rooms style with infinite loop
  useEffect(() => {
    const row1 = row1NavRef.current;
    const row2 = row2NavRef.current;
    if (!row1 || !row2) return;
    
    let isScrolling = false;
    
    const getSingleSetWidth = (element) => {
      // Calculate width of one complete set of cards
      const duplicateContainer = element.querySelector('.rooms-duplicate');
      if (!duplicateContainer) return 0;
      // Since we have duplicated items ([...firstRow, ...firstRow]), divide by 2
      const totalWidth = duplicateContainer.scrollWidth;
      return totalWidth / 2;
    };
    
    const createInfiniteLoop = (element) => {
      const singleSetWidth = getSingleSetWidth(element);
      if (singleSetWidth === 0) return;
      
      // When scrolled to or past the end of the first set (50% of total), jump back to start
      if (element.scrollLeft >= singleSetWidth) {
        element.scrollLeft = element.scrollLeft - singleSetWidth;
      }
      // When scrolled before start (shouldn't happen, but safety check)
      if (element.scrollLeft < 0) {
        element.scrollLeft = 0;
      }
    };
    
    const syncScroll = (source, target) => {
      if (isScrolling) return;
      isScrolling = true;
      
      // Create infinite loop for source
      createInfiniteLoop(source);
      
      // Sync target to same position (within first set)
      const sourceSingleSetWidth = getSingleSetWidth(source);
      const targetSingleSetWidth = getSingleSetWidth(target);
      
      if (sourceSingleSetWidth > 0 && targetSingleSetWidth > 0) {
        // Calculate scroll position within first set (0 to singleSetWidth)
        const sourceScroll = source.scrollLeft % sourceSingleSetWidth;
        // Map to target's first set
        const scrollPercent = sourceScroll / sourceSingleSetWidth;
        target.scrollLeft = scrollPercent * targetSingleSetWidth;
        
        // Create infinite loop for target as well
        createInfiniteLoop(target);
      }
      
      setTimeout(() => {
        isScrolling = false;
      }, 10);
    };
    
    const handleRow1Scroll = () => syncScroll(row1, row2);
    const handleRow2Scroll = () => syncScroll(row2, row1);
    
    row1.addEventListener('scroll', handleRow1Scroll);
    row2.addEventListener('scroll', handleRow2Scroll);
    
    return () => {
      row1.removeEventListener('scroll', handleRow1Scroll);
      row2.removeEventListener('scroll', handleRow2Scroll);
    };
  }, [selectedCategory]); // Re-run when category changes

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
    // If using NavLinks, filter by specific NavLink (sub-nav)
    if (navLinksWithImages.length > 0) {
      if (activeTab.startsWith('navlink-')) {
        // Filter to specific NavLink by ID - this is the selected sub-nav
        const linkId = activeTab.replace('navlink-', '');
        return navLinksWithImages.filter(link => String(link.id) === linkId);
      }
      // If activeTab doesn't match pattern, return all (shouldn't happen)
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

  // Map category name/slug to animation style
  // This allows categories to use the original hardcoded styles (certificates, courses, rooms)
  const getCategoryAnimationStyle = (category) => {
    if (!category) return 'simple_grid'; // Default fallback
    
    // PRIORITY 1: Check animation_style field from database (explicit selection from dropdown)
    if (category.animation_style) {
      const style = category.animation_style.toLowerCase();
      // Direct style matches (from dropdown selection)
      if (style === 'certificates') return 'certificates';
      if (style === 'courses') return 'courses';
      if (style === 'rooms') return 'rooms';
      // Legacy style mappings
      if (style === 'carousel_scroll_left' || style === 'grid_editorial_collage') return 'certificates';
      if (style === 'list_alternating_cards') return 'courses';
    }
    
    // PRIORITY 2: Check if category name/slug matches any of the original styles (automatic matching)
    const categoryName = extractString(category.name, category.slug || '').toLowerCase();
    const categorySlug = (category.slug || '').toLowerCase();
    
    // Certificates style (editorial grid collage with carousel)
    if (categoryName.includes('certificate') || categorySlug.includes('certificate')) {
      return 'certificates';
    }
    
    // Courses style (alternating cards)
    if (categoryName.includes('course') || categorySlug.includes('course')) {
      return 'courses';
    }
    
    // Rooms style (horizontal scrollable cards with date)
    if (categoryName.includes('room') || categorySlug.includes('room')) {
      return 'rooms';
    }
    
    // Default to simple grid if no match
    return 'simple_grid';
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
        @media (max-width: 768px) {
          section[aria-label*="${brandTitle}"] {
            height: auto !important;
            min-height: 100vh !important;
            max-height: none !important;
            overflow: visible !important;
            overflow-y: auto !important;
          }
          section[aria-label*="${brandTitle}"] > div {
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
          }
          section[aria-label*="${brandTitle}"] .carousel-wrapper {
            height: auto !important;
            min-height: 400px !important;
            max-height: none !important;
          }
          section[aria-label*="${brandTitle}"] > div > div {
            height: auto !important;
            overflow: visible !important;
          }
        }
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
        /* Continuous scrolling carousel animation */
        @keyframes scrollLeft {
          0% {
            transform: translateX(0);
          }
          100% {
            transform: translateX(-50%);
          }
        }
        .carousel-container {
          display: flex;
          animation: scrollLeft 60s linear infinite;
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
          overflow-y: auto;
          position: relative;
          height: 100%;
          scrollbar-width: thin;
          scroll-behavior: smooth;
          background-image: none !important;
          background: transparent !important;
          isolation: isolate;
        }
        @media (max-width: 768px) {
          .carousel-wrapper {
            overflow-x: visible;
            overflow-y: auto;
            height: auto;
            min-height: 100%;
          }
          .carousel-container {
            width: 100% !important;
            animation: none !important;
            flex-direction: column;
          }
          .carousel-container > div {
            width: 100% !important;
            height: auto !important;
          }
        }
        @media (min-width: 769px) {
          .carousel-container-animated {
            width: 200% !important;
          }
          .carousel-container-animated > div {
            width: 50% !important;
          }
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
          className={`w-full h-full flex flex-col md:flex-row items-start gap-6 md:gap-8 lg:gap-10 ${textAlignment === 'right' ? 'md:flex-row-reverse' : ''}`}
          style={{ 
            height: '100%',
            backgroundImage: 'none',
            background: 'transparent'
          }}
        >
          {/* LEFT SECTION: Brand Title & Subtitle + Images */}
          <div 
            className="w-full md:w-1/3 flex flex-col justify-center flex-shrink-0"
            style={{
              animation: isVisible ? 'fadeInUp 0.8s ease-out forwards' : 'none',
              opacity: isVisible ? 1 : 0,
              height: '100%',
              minWidth: '33.333333%',
              maxWidth: '33.333333%'
            }}
          >
            <div style={{ textAlign: textAlignment === 'right' ? 'right' : 'left' }}>
              <h2 
                className="text-[clamp(2.5rem,6vw,5rem)] font-black tracking-tight leading-[0.9] uppercase text-neutral-900 select-none"
                style={{
                  fontFamily: 'system-ui, -apple-system, sans-serif'
                }}
              >
                {brandTitle}
              </h2>
              {/* Dynamic tabs from NavLinks (subsections) with nested categories */}
              {tabs.length > 0 ? (
                tabs.map((tab) => {
                  const isActive = activeTab === tab.key;
                  const categories = tab.categories || [];
                  return (
                    <div key={tab.key} className="mt-1">
                      <p 
                        className={`text-2xl md:text-3xl font-serif cursor-pointer transition-all duration-300 ${
                          isActive
                        ? 'text-neutral-900 font-semibold' 
                        : 'text-neutral-500 hover:text-neutral-700'
                    }`}
                    style={{
                      fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
                    }}
                        onClick={() => {
                          setActiveTab(tab.key);
                          setSelectedCategory(null); // Reset category when switching sub-navs
                        }}
                  >
                    {tab.title}
                  </p>
                      {/* Show categories nested below the active sub-nav */}
                      {isActive && categories.length > 0 && (
                        <div className="ml-4 mt-2 space-y-1">
                          {categories.map((category) => (
                            <p
                              key={category.id || category.slug}
                              className={`text-base md:text-lg font-serif cursor-pointer transition-all duration-300 ${
                                selectedCategory === category.id
                                  ? 'text-neutral-900 font-semibold' 
                                  : 'text-neutral-500 hover:text-neutral-700'
                              }`}
                              style={{
                                fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
                              }}
                              onClick={(e) => {
                                e.stopPropagation();
                                setSelectedCategory(category.id);
                              }}
                            >
                              {extractString(category.name, category.slug || 'Category')}
                            </p>
                          ))}
                        </div>
                      )}
                    </div>
                  );
                })
              ) : (
                <p 
                  className="text-2xl md:text-3xl font-serif mt-1 text-neutral-500"
                  style={{
                    fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif'
                  }}
                >
                  {window.translations?.progress?.no_subsections || 'No subsections available'}
                </p>
              )}
                </div>
              </div>

               {/* RIGHT SECTION: Content based on active tab */}
               <div className="w-full md:w-2/3 flex-1 min-w-0" style={{ height: '100%', overflow: tabs.length === 0 ? 'hidden' : (tabs.some(t => t.key === activeTab && activeTab.startsWith('navlink-')) ? 'hidden' : 'auto') }}>
                 {tabs.length === 0 ? (
                   <div className="flex items-center justify-center h-full">
                     <p className="text-neutral-500 text-center">
                       {window.translations?.progress?.no_subsections_configured || 'No subsections configured. Please select subsections in the admin panel.'}
                     </p>
                   </div>
                 ) : activeTab.startsWith('navlink-') ? (
                   // Show categories for the selected NavLink (sub-nav), then items when category is clicked
                   (() => {
                     const activeTabId = activeTab.replace('navlink-', '');
                     const activeNavLink = tabs.find(tab => tab.id === activeTabId);
                     const activeNavLinkTitle = activeNavLink ? extractString(activeNavLink.title, 'Untitled') : '';
                     const categories = activeNavLink?.categories || [];
                     
                    // If a category is selected, show items in that category
                    if (selectedCategory) {
                       // Get the selected category object to determine animation style
                       const selectedCategoryObj = categories.find(c => String(c.id) === String(selectedCategory));
                       
                       // PRIORITY: Check for CategoryItems (items created via "Manage Items" with slugs like slug-1, slug-2)
                       const categoryItems = selectedCategoryObj?.items || [];
                       
                       // Only filter NavLinks if CategoryItems don't exist (fallback)
                       // navLinksWithImages already contains all NavLinks from the section's selected sub-navs
                       // CRITICAL: Exclude the active NavLink (sub-nav) itself - it's a container, not a content item
                       const activeNavLinkId = activeNavLink?.id ? String(activeNavLink.id) : null;
                       const categoryNavLinks = categoryItems.length === 0 ? navLinksWithImages.filter(link => {
                         // Always exclude the active NavLink (sub-nav) - it's the container, not content
                         if (activeNavLinkId && String(link.id) === activeNavLinkId) {
                           console.log('CertificatesSection: Excluding active NavLink from items:', link.title, link.id);
                           return false;
                         }
                         // Must have the selected category
                         return link.categories && link.categories.some(cat => String(cat.id) === String(selectedCategory));
                       }) : [];
                       
                       console.log('CertificatesSection: categoryNavLinks after filtering (excluding active NavLink):', categoryNavLinks.map(l => ({id: l.id, title: l.title})));
                       console.log('CertificatesSection: categoryItems count:', categoryItems.length);
                       
                       // Get the category animation style
                       const categoryStyle = getCategoryAnimationStyle(selectedCategoryObj);
                       console.log('CertificatesSection: Category style for items:', categoryStyle);
                       console.log('CertificatesSection: Selected category object:', selectedCategoryObj);
                       console.log('CertificatesSection: Category animation_style from object:', selectedCategoryObj?.animation_style);
                       
                       // If we have CategoryItems, use them instead of NavLinks and apply animation style
                       if (categoryItems.length > 0) {
                         console.log('CertificatesSection: Using CategoryItems:', categoryItems.length);
                         
                         // Convert CategoryItems to a format compatible with the existing rendering logic
                         // Map CategoryItems to NavLink-like structure for reuse of existing animation layouts
                        const itemsForDisplay = categoryItems.map(item => {
                          const itemTitle = extractString(item.title, item.slug || 'Untitled');
                          const itemSummary = extractString(item.summary, '');
                          return {
                            id: item.id,
                            title: itemTitle,
                            slug: item.slug,
                            imageUrl: item.image_url || null,
                            url: item.url,
                            summary: itemSummary,
                            download_url: item.download_url,
                            view_url: item.view_url,
                            visit_url: item.visit_url,
                            linked_model: item.linked_model || null, // Include linked_model for modal opening
                            isSection: true, // Flag to identify this as a CategoryItem (section)
                            // For image display
                            media: item.image_url ? [{
                              id: `category-item-${item.id}`,
                              type: 'image',
                              path: item.image_url.replace(/^.*\/storage\//, ''),
                              title: itemTitle
                            }] : []
                          };
                        });
                         
                         // Use the same rendering logic as categoryNavLinks but with CategoryItems
                         // Render based on category animation style
                         // Certificates style: Editorial grid collage with carousel
                         if (categoryStyle === 'certificates') {
                           return (
                             <div className="h-full overflow-y-auto">
                               <div className="mb-4 flex items-center gap-2">
                                 <button 
                                   onClick={() => setSelectedCategory(null)}
                                   className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                                 >
                                   <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                   </svg>
                                   Back to categories
                                 </button>
                                 <span className="text-sm text-neutral-400">•</span>
                                 <span className="text-sm text-neutral-600">
                                   {activeNavLinkTitle} → {extractString(selectedCategoryObj?.name, 'Category')}
                                 </span>
                               </div>
                               {/* Certificates carousel style */}
                               <div className="carousel-wrapper" style={{ height: 'calc(100% - 3rem)', paddingBottom: '0.5rem' }}>
                                 {/* Duplicate items for seamless infinite loop - create enough copies to fill multiple screens */}
                                 {(() => {
                                   // Create multiple copies of items for seamless looping
                                   const copiesNeeded = Math.max(3, Math.ceil(itemsForDisplay.length / 4) + 1);
                                   const duplicatedItems = Array.from({ length: copiesNeeded }, () => itemsForDisplay).flat();
                                   
                                   // Each "set" consists of 2 sections: one grid (4 items) and one list (remaining items)
                                   // We need enough sets to create a seamless loop
                                   const itemsPerGrid = 4;
                                   const itemsPerSet = itemsPerGrid * 2; // 2 sections per set
                                   const totalSets = Math.ceil(duplicatedItems.length / itemsPerSet);
                                   const totalSections = totalSets * 2; // Each set has 2 sections
                                   const containerWidth = `${totalSections * 100}%`;
                                   
                                   return (
                                     <div className="carousel-container md:carousel-container-animated" style={{ width: '100%', height: '100%' }}>
                                       {Array.from({ length: Math.min(2, totalSections) }).map((_, sectionIndex) => {
                                         const isFirstGrid = sectionIndex % 2 === 0;
                                         const setIndex = Math.floor(sectionIndex / 2);
                                         const startIndex = setIndex * itemsPerSet;
                                         
                                         let sectionItems;
                                         if (isFirstGrid) {
                                           // First grid: show first 4 items of this set
                                           sectionItems = duplicatedItems.slice(startIndex, startIndex + itemsPerGrid);
                                         } else {
                                           // Second grid: show next 4 items of this set
                                           sectionItems = duplicatedItems.slice(startIndex + itemsPerGrid, startIndex + itemsPerSet);
                                         }
                                         
                                         // Fill empty slots if needed
                                         if (sectionItems.length < itemsPerGrid) {
                                           const remaining = itemsPerGrid - sectionItems.length;
                                           sectionItems = [...sectionItems, ...duplicatedItems.slice(0, remaining)];
                                         }
                                         
                                         if (isFirstGrid) {
                                           // First grid: Responsive layout
                                           return (
                                             <div key={`grid-section-${sectionIndex}`} className="flex flex-col md:flex-row gap-3 sm:gap-4 w-full md:w-1/2" style={{ height: 'auto', background: '#fafafa', backgroundImage: 'none' }}>
                                               <div 
                                                 className="grid grid-cols-1 md:grid-cols-3 gap-3 flex-shrink-0 w-full"
                                                 style={{ 
                                                   gridTemplateRows: 'auto',
                                                   width: '100%',
                                                   height: 'auto',
                                                   maxHeight: 'none'
                                                 }}
                                               >
                                                 {sectionItems.map((item, index) => {
                                                   const gridConfig = getGridSpan(index, Math.min(4, sectionItems.length));
                                                   return (
                                                     <div
                                                       key={`category-item-${item.id}-${sectionIndex}-${index}`}
                                                       className={`relative overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer bg-white ${gridConfig.rotation} ${
                                                         index === 0 ? 'md:col-span-2 md:row-span-1' : 
                                                         index === 1 ? 'md:col-span-1 md:row-span-1' : 
                                                         index === 2 ? 'md:col-span-1 md:row-span-1' : 
                                                         'md:col-span-2 md:row-span-1'
                                                       }`}
                                                       style={{ 
                                                         animationDelay: `${index * 0.1}s`, 
                                                         pointerEvents: 'auto',
                                                         minHeight: '200px',
                                                         height: 'auto'
                                                       }}
                                                       onClick={(e) => {
                                                         console.log('Card onClick triggered');
                                                         handleCardClick(item, e);
                                                       }}
                                                       data-react-click-handled="true"
                                                       onMouseDown={(e) => {
                                                         if (e && typeof e.preventDefault === 'function') {
                                                           e.preventDefault();
                                                         }
                                                         if (e && typeof e.stopPropagation === 'function') {
                                                           e.stopPropagation();
                                                         }
                                                       }}
                                                       onContextMenu={(e) => {
                                                         if (e && typeof e.preventDefault === 'function') {
                                                           e.preventDefault();
                                                         }
                                                       }}
                                                       role="button"
                                                       tabIndex={0}
                                                       onKeyDown={(e) => {
                                                         if (e.key === 'Enter' || e.key === ' ') {
                                                           e.preventDefault();
                                                           handleCardClick(item, e);
                                                         }
                                                       }}
                                                     >
                                                       {item.imageUrl ? (
                                                         <>
                                                           <img 
                                                             src={item.imageUrl} 
                                                             alt={item.title}
                                                             className="w-full h-full object-cover"
                                                             style={{ 
                                                               position: 'absolute',
                                                               top: 0,
                                                               left: 0,
                                                               right: 0,
                                                               bottom: 0,
                                                               width: '100%',
                                                               height: '100%',
                                                               objectFit: 'cover'
                                                             }}
                                                             onError={(e) => {
                                                               e.target.style.display = 'none';
                                                             }}
                                                           />
                                                           <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/0 flex flex-col justify-between p-3 sm:p-4">
                                                             <div className="flex-1 flex items-start justify-end">
                                                               <div className="flex flex-wrap gap-2">
                                                                 {item.download_url && (
                                                                   <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-2 sm:px-3 py-1 sm:py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                                     Download
                                                                   </a>
                                                                 )}
                                                                 {item.view_url && (
                                                                   <button 
                                                                     className="px-2 sm:px-3 py-1 sm:py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors backdrop-blur-sm"
                                                                     onClick={(e) => {
                                                                       e.preventDefault();
                                                                       e.stopPropagation();
                                                                       handleCardClick(item, e);
                                                                     }}
                                                                   >
                                                                     View
                                                                   </button>
                                                                 )}
                                                                 {item.visit_url && (
                                                                   <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-2 sm:px-3 py-1 sm:py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                                     Visit
                                                                   </a>
                                                                 )}
                                                               </div>
                                                             </div>
                                                             <div className="text-white">
                                                               <p className="text-sm sm:text-base font-semibold drop-shadow-lg">{item.title}</p>
                                                               {item.slug && <p className="text-xs opacity-90 font-mono drop-shadow-md">{item.slug}</p>}
                                                               {item.summary && <p className="text-xs opacity-85 mt-1 line-clamp-2 drop-shadow-md">{item.summary}</p>}
                                                             </div>
                                                           </div>
                                                         </>
                                                       ) : (
                                                         <div className="w-full h-full bg-white flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 text-center" style={{ minHeight: '200px' }}>
                                                           <h3 className="text-lg sm:text-xl md:text-2xl font-bold text-neutral-900 mb-2">
                                                             {item.title}
                                                           </h3>
                                                           {item.slug && (
                                                             <p className="text-xs sm:text-sm text-neutral-500 font-mono mb-2">
                                                               {item.slug}
                                                             </p>
                                                           )}
                                                           {item.summary && (
                                                             <p className="text-xs sm:text-sm text-neutral-600">
                                                               {item.summary}
                                                             </p>
                                                           )}
                                                         </div>
                                                       )}
                                                     </div>
                                                   );
                                                 })}
                                               </div>
                                             </div>
                                           );
                                         } else {
                                           // Second grid: vertical list layout - Responsive
                                           return (
                                             <div key={`grid-section-${sectionIndex}`} className="flex flex-col gap-3 sm:gap-4 w-full md:w-1/2" style={{ height: 'auto', background: '#fafafa', backgroundImage: 'none' }}>
                                               <div className="grid grid-cols-1 gap-3 flex-shrink-0 w-full" style={{ width: '100%', height: 'auto' }}>
                                                 {sectionItems.map((item, index) => {
                                                   const actualIndex = index + 4;
                                                   return (
                                                     <div
                                                       key={`category-item-${item.id}-${sectionIndex}-${index}`}
                                                       className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer bg-white ${getGridSpan(actualIndex, sectionItems.length).rotation}`}
                                                       onClick={(e) => {
                                                         console.log('Card onClick triggered');
                                                         handleCardClick(item, e);
                                                       }}
                                                       data-react-click-handled="true"
                                                       onMouseDown={(e) => {
                                                         if (e && typeof e.preventDefault === 'function') {
                                                           e.preventDefault();
                                                         }
                                                         if (e && typeof e.stopPropagation === 'function') {
                                                           e.stopPropagation();
                                                         }
                                                       }}
                                                       onContextMenu={(e) => {
                                                         if (e && typeof e.preventDefault === 'function') {
                                                           e.preventDefault();
                                                         }
                                                       }}
                                                       role="button"
                                                       tabIndex={0}
                                                       onKeyDown={(e) => {
                                                         if (e.key === 'Enter' || e.key === ' ') {
                                                           e.preventDefault();
                                                           handleCardClick(item, e);
                                                         }
                                                       }}
                                                       style={{ pointerEvents: 'auto', minHeight: '200px', height: 'auto' }}
                                                     >
                                                       {item.imageUrl ? (
                                                         <>
                                                           <img 
                                                             src={item.imageUrl} 
                                                             alt={item.title}
                                                             className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                             style={{ 
                                                               position: 'absolute',
                                                               top: 0,
                                                               left: 0,
                                                               right: 0,
                                                               bottom: 0,
                                                               width: '100%',
                                                               height: '100%',
                                                               objectFit: 'cover'
                                                             }}
                                                             onError={(e) => {
                                                               e.target.style.display = 'none';
                                                             }}
                                                           />
                                                           <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/0 flex flex-col justify-between p-3 sm:p-4">
                                                             <div className="flex-1 flex items-start justify-end">
                                                               <div className="flex flex-wrap gap-2">
                                                                 {item.download_url && (
                                                                   <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-2 sm:px-3 py-1 sm:py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                                     Download
                                                                   </a>
                                                                 )}
                                                                 {item.view_url && (
                                                                   <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-2 sm:px-3 py-1 sm:py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                                     View
                                                                   </a>
                                                                 )}
                                                                 {item.visit_url && (
                                                                   <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-2 sm:px-3 py-1 sm:py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                                     Visit
                                                                   </a>
                                                                 )}
                                                               </div>
                                                             </div>
                                                             <div className="text-white">
                                                               <p className="text-sm sm:text-base font-semibold drop-shadow-lg">{item.title}</p>
                                                               {item.slug && <p className="text-xs opacity-90 font-mono drop-shadow-md">{item.slug}</p>}
                                                               {item.summary && <p className="text-xs opacity-85 mt-1 line-clamp-2 drop-shadow-md">{item.summary}</p>}
                                                             </div>
                                                           </div>
                                                         </>
                                                       ) : (
                                                         <div className="w-full h-full bg-white flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 text-center" style={{ minHeight: '200px' }}>
                                                           <h3 className="text-lg sm:text-xl md:text-2xl font-bold text-neutral-900 mb-2">
                                                             {item.title}
                                                           </h3>
                                                           {item.slug && (
                                                             <p className="text-xs sm:text-sm text-neutral-500 font-mono mb-2">
                                                               {item.slug}
                                                             </p>
                                                           )}
                                                           {item.summary && (
                                                             <p className="text-xs sm:text-sm text-neutral-600">
                                                               {item.summary}
                                                             </p>
                                                           )}
                                                         </div>
                                                       )}
                                                     </div>
                                                   );
                                                 })}
                                               </div>
                                             </div>
                                           );
                                         }
                                       })}
                                     </div>
                                   );
                                 })()}
                               </div>
                             </div>
                           );
                         }
                         
                         // Courses style: Alternating cards layout
                         if (categoryStyle === 'courses') {
                           return (
                             <div className="h-full overflow-y-auto">
                               <div className="mb-4 flex items-center gap-2">
                                 <button 
                                   onClick={() => setSelectedCategory(null)}
                                   className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                                 >
                                   <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                   </svg>
                                   Back to categories
                                 </button>
                                 <span className="text-sm text-neutral-400">•</span>
                                 <span className="text-sm text-neutral-600">
                                   {activeNavLinkTitle} → {extractString(selectedCategoryObj?.name, 'Category')}
                                 </span>
                               </div>
                               <div className="space-y-6 p-4">
                                 {itemsForDisplay.map((item, index) => (
                                   <div 
                                     key={`category-item-${item.id}-${index}`}
                                     className={`flex gap-6 items-center ${index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'} bg-white rounded-xl shadow-md hover:shadow-lg transition-all p-6 cursor-pointer`}
                                     onClick={(e) => handleCardClick(item, e)}
                                     data-react-click-handled="true"
                                     onMouseDown={(e) => e.preventDefault()}
                                     role="button"
                                     tabIndex={0}
                                     onKeyDown={(e) => {
                                       if (e.key === 'Enter' || e.key === ' ') {
                                         e.preventDefault();
                                         handleCardClick(item, e);
                                       }
                                     }}
                                   >
                                     <div className="flex-shrink-0" style={{ width: '333px' }}>
                                       {item.imageUrl ? (
                                         <img 
                                           src={item.imageUrl} 
                                           alt={item.title}
                                           className="w-full h-48 object-cover rounded-lg"
                                           onError={(e) => {
                                             e.target.style.display = 'none';
                                           }}
                                         />
                                       ) : (
                                         <div className="w-full h-48 bg-neutral-100 rounded-lg flex items-center justify-center">
                                           <span className="text-neutral-400">No image</span>
                                         </div>
                                       )}
                                     </div>
                                     <div className="flex-1 min-w-0 overflow-hidden">
                                       <h3 className="text-xl font-bold text-neutral-900 mb-2 truncate break-words" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>{item.title}</h3>
                                       {item.slug && <p className="text-sm text-neutral-500 font-mono mb-2 truncate break-all" style={{ wordBreak: 'break-all' }}>{item.slug}</p>}
                                       {item.summary && <p className="text-neutral-600 mb-4 break-words line-clamp-3" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>{item.summary}</p>}
                                       <div className="flex flex-wrap gap-2">
                                         {item.download_url && (
                                           <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-md transition-colors whitespace-nowrap" onClick={(e) => e.stopPropagation()}>
                                             Download
                                           </a>
                                         )}
                                           {item.view_url && (
                                             <button 
                                               className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors whitespace-nowrap"
                                               onClick={(e) => {
                                                 e.preventDefault();
                                                 e.stopPropagation();
                                                 handleCardClick(item, e);
                                               }}
                                             >
                                               View
                                             </button>
                                           )}
                                         {item.visit_url && (
                                           <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors whitespace-nowrap" onClick={(e) => e.stopPropagation()}>
                                             Visit
                                           </a>
                                         )}
                                       </div>
                                     </div>
                                   </div>
                                 ))}
                               </div>
                             </div>
                           );
                         }
                         
                         // Rooms style: Two rows of horizontal scrollable cards with left-to-right animation
                         if (categoryStyle === 'rooms') {
                           // Split items into two rows
                           const midPoint = Math.ceil(itemsForDisplay.length / 2);
                           const firstRow = itemsForDisplay.slice(0, midPoint);
                           const secondRow = itemsForDisplay.slice(midPoint);
                           
                          return (
                            <div className="h-full overflow-hidden flex flex-col">
                              <div className="mb-4 flex items-center gap-2 flex-shrink-0">
                                <button 
                                  onClick={() => setSelectedCategory(null)}
                                  className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                                >
                                  <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                  </svg>
                                  Back to categories
                                </button>
                                <span className="text-sm text-neutral-400">•</span>
                                <span className="text-sm text-neutral-600">
                                  {activeNavLinkTitle} → {extractString(selectedCategoryObj?.name, 'Category')}
                                </span>
                              </div>
                              <style>{`
                                @keyframes scrollLeft {
                                  0% {
                                    transform: translateX(0);
                                  }
                                  100% {
                                    transform: translateX(-50%);
                                  }
                                }
                                .rooms-scroll-container {
                                  display: flex;
                                  gap: 1.5rem;
                                  width: max-content;
                                  animation: scrollLeft 30s linear infinite;
                                  will-change: transform;
                                }
                                .rooms-scroll-container:hover {
                                  animation-play-state: paused;
                                }
                                .rooms-scroll-wrapper {
                                  overflow-x: auto;
                                  overflow-y: hidden;
                                  position: relative;
                                  width: 100%;
                                  flex-shrink: 0;
                                  isolation: isolate;
                                  scrollbar-width: thin;
                                }
                                .rooms-scroll-wrapper::-webkit-scrollbar {
                                  height: 8px;
                                  position: absolute;
                                  bottom: 0;
                                }
                                .rooms-scroll-wrapper::-webkit-scrollbar-track {
                                  background: transparent;
                                }
                                .rooms-scroll-wrapper::-webkit-scrollbar-thumb {
                                  background: rgba(0, 0, 0, 0.2);
                                  border-radius: 4px;
                                }
                                .rooms-scroll-wrapper::-webkit-scrollbar-thumb:hover {
                                  background: rgba(0, 0, 0, 0.3);
                                }
                                /* Hide scrollbar for first row, show only for last row */
                                .rooms-scroll-wrapper:not(:last-child) {
                                  scrollbar-width: none;
                                  -ms-overflow-style: none;
                                }
                                .rooms-scroll-wrapper:not(:last-child)::-webkit-scrollbar {
                                  display: none;
                                }
                                .rooms-duplicate {
                                  display: flex;
                                  gap: 1.5rem;
                                }
                                .rooms-card-item {
                                  flex-shrink: 0;
                                  width: 400px;
                                  margin: 0;
                                  box-sizing: border-box;
                                  display: flex;
                                  flex-direction: column;
                                  height: 100%;
                                }
                                .rooms-card-content {
                                  flex: 1;
                                  display: flex;
                                  flex-direction: column;
                                  overflow: hidden;
                                }
                              `}</style>
                              {/* Unified Two-Row Section with Synchronized Scrolling */}
                              <div className="flex flex-col h-full" style={{ gap: '0' }}>
                                {/* First Row */}
                                <div ref={row1Ref} className="rooms-scroll-wrapper" style={{ height: '50%', marginBottom: '0', borderBottom: '2px solid transparent' }}>
                                  <div className="rooms-scroll-container" style={{ height: '100%' }}>
                                    <div className="rooms-duplicate" style={{ height: '100%' }}>
                                      {[...firstRow, ...firstRow].map((item, index) => (
                                        <div 
                                          key={`room-row1-${item.id}-${index}`}
                                          className="rooms-card-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all cursor-pointer group"
                                          style={{ minWidth: '400px', maxWidth: '400px', margin: 0, height: '100%' }}
                                          onClick={(e) => handleCardClick(item, e)}
                                     data-react-click-handled="true"
                                     onMouseDown={(e) => e.preventDefault()}
                                     role="button"
                                     tabIndex={0}
                                     onKeyDown={(e) => {
                                       if (e.key === 'Enter' || e.key === ' ') {
                                         e.preventDefault();
                                         handleCardClick(item, e);
                                       }
                                     }}
                                        >
                                          <div className="rooms-card-content" style={{ height: '100%' }}>
                                            {item.imageUrl ? (
                                              <div className="relative" style={{ height: '100%' }}>
                                                <img 
                                                  src={item.imageUrl} 
                                                  alt={item.title}
                                                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                  style={{ objectFit: 'cover', height: '100%' }}
                                                  onError={(e) => {
                                                    e.target.style.display = 'none';
                                                  }}
                                                />
                                                <div className="absolute top-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm z-10">
                                                  {(index % firstRow.length) + 1}
                                                </div>
                                                {/* All content overlapping image */}
                                                <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/70 to-black/40 p-4 z-10 flex flex-col justify-between">
                                                  <div className="flex-1"></div>
                                                  <div>
                                                    <h3 className="text-lg font-bold text-white mb-2 truncate break-words drop-shadow-lg" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>{item.title}</h3>
                                                    {item.summary && <p className="text-sm text-white mb-3 line-clamp-2 break-words drop-shadow-md" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>{item.summary}</p>}
                                                    {/* Buttons overlapping image */}
                                                    <div className="flex flex-wrap gap-2">
                                                      {item.download_url && (
                                                        <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors whitespace-nowrap backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                          Download
                                                        </a>
                                                      )}
                                                      {item.view_url && (
                                                        <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors whitespace-nowrap backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                          View
                                                        </a>
                                                      )}
                                                      {item.visit_url && (
                                                        <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors whitespace-nowrap backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                          Visit
                                                        </a>
                                                      )}
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            ) : (
                                              <div className="flex-shrink-0 bg-neutral-100 flex items-center justify-center" style={{ height: '100%' }}>
                                                <span className="text-neutral-400">No image</span>
                                              </div>
                                            )}
                                          </div>
                                        </div>
                                      ))}
                                    </div>
                                  </div>
                                </div>
                                
                                {/* Second Row - synchronized scrolling */}
                                <div ref={row2Ref} className="rooms-scroll-wrapper" style={{ height: '50%', marginTop: '0', borderTop: 'none' }}>
                                  <div className="rooms-scroll-container" style={{ height: '100%' }}>
                                    <div className="rooms-duplicate" style={{ height: '100%' }}>
                                      {[...secondRow, ...secondRow].map((item, index) => (
                                        <div 
                                          key={`room-row2-${item.id}-${index}`}
                                          className="rooms-card-item bg-white rounded-lg shadow-md hover:shadow-lg transition-all cursor-pointer group"
                                          style={{ minWidth: '400px', maxWidth: '400px', margin: 0, height: '100%' }}
                                          onClick={(e) => handleCardClick(item, e)}
                                     data-react-click-handled="true"
                                     onMouseDown={(e) => e.preventDefault()}
                                     role="button"
                                     tabIndex={0}
                                     onKeyDown={(e) => {
                                       if (e.key === 'Enter' || e.key === ' ') {
                                         e.preventDefault();
                                         handleCardClick(item, e);
                                       }
                                     }}
                                        >
                                          <div className="rooms-card-content" style={{ height: '100%' }}>
                                            {item.imageUrl ? (
                                              <div className="relative" style={{ height: '100%' }}>
                                                <img 
                                                  src={item.imageUrl} 
                                                  alt={item.title}
                                                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                                  style={{ objectFit: 'cover', height: '100%' }}
                                                  onError={(e) => {
                                                    e.target.style.display = 'none';
                                                  }}
                                                />
                                                <div className="absolute top-4 right-4 bg-black/50 text-white px-3 py-1 rounded-full text-sm z-10">
                                                  {midPoint + (index % secondRow.length) + 1}
                                                </div>
                                                {/* All content overlapping image */}
                                                <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/70 to-black/40 p-4 z-10 flex flex-col justify-between">
                                                  <div className="flex-1"></div>
                                                  <div>
                                                    <h3 className="text-lg font-bold text-white mb-2 truncate break-words drop-shadow-lg" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>{item.title}</h3>
                                                    {item.summary && <p className="text-sm text-white mb-3 line-clamp-2 break-words drop-shadow-md" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>{item.summary}</p>}
                                                    {/* Buttons overlapping image */}
                                                    <div className="flex flex-wrap gap-2">
                                                      {item.download_url && (
                                                        <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors whitespace-nowrap backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                          Download
                                                        </a>
                                                      )}
                                                      {item.view_url && (
                                                        <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors whitespace-nowrap backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                          View
                                                        </a>
                                                      )}
                                                      {item.visit_url && (
                                                        <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors whitespace-nowrap backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                          Visit
                                                        </a>
                                                      )}
                                                    </div>
                                                  </div>
                                                </div>
                                              </div>
                                            ) : (
                                              <div className="flex-shrink-0 bg-neutral-100 flex items-center justify-center" style={{ height: '100%' }}>
                                                <span className="text-neutral-400">No image</span>
                                              </div>
                                            )}
                                          </div>
                                        </div>
                                      ))}
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          );
                         }
                         
                         // Default: Simple grid layout for other styles
                         return (
                           <div className="h-full overflow-y-auto">
                             <div className="mb-4 flex items-center gap-2">
                               <button 
                                 onClick={() => setSelectedCategory(null)}
                                 className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                               >
                                 <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                 </svg>
                                 Back to categories
                               </button>
                             </div>
                             <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4">
                               {itemsForDisplay.map((item) => {
                                 console.log('Rendering category item:', item.id, 'isSection:', item.isSection, 'Full item:', item);
                                 return (
                                 <div 
                                   key={`category-item-${item.id}`}
                                   className="bg-white rounded-lg border border-neutral-200 overflow-hidden hover:shadow-lg transition-all group cursor-pointer"
                                   style={{ pointerEvents: 'auto', position: 'relative', zIndex: 1 }}
                                   onClick={(e) => {
                                     console.log('=== CARD CLICKED ===', item.id, 'isSection:', item.isSection);
                                     console.log('Event:', e);
                                     e.preventDefault();
                                     e.stopPropagation();
                                     // React SyntheticEvent doesn't have stopImmediatePropagation
                                     if (e.nativeEvent && typeof e.nativeEvent.stopImmediatePropagation === 'function') {
                                       e.nativeEvent.stopImmediatePropagation();
                                     }
                                     handleCardClick(item, e);
                                   }}
                                   onMouseDown={(e) => {
                                     console.log('Card mousedown:', item.id);
                                     e.stopPropagation();
                                   }}
                                   data-react-click-handled="true"
                                   role="button"
                                   tabIndex={0}
                                   onKeyDown={(e) => {
                                     if (e.key === 'Enter' || e.key === ' ') {
                                       e.preventDefault();
                                       handleCardClick(item, e);
                                     }
                                   }}
                                 >
                                   {item.imageUrl ? (
                                     <div className="relative overflow-hidden">
                                       <img 
                                         src={item.imageUrl} 
                                         alt={item.title || item.slug}
                                         className="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-200"
                                         onError={(e) => {
                                           e.target.style.display = 'none';
                                         }}
                                       />
                                       <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-10 transition-opacity"></div>
                                     </div>
                                   ) : (
                                     <div className="w-full h-48 bg-neutral-100 flex items-center justify-center">
                                       <span className="text-neutral-400">No image</span>
                                     </div>
                                   )}
                                   <div className="p-4">
                                     <h4 className="font-semibold text-neutral-900 mb-1 text-sm">{item.title || item.slug || 'Untitled'}</h4>
                                     {item.slug && (
                                       <p className="text-xs text-neutral-500 font-mono mb-2">{item.slug}</p>
                                     )}
                                     {item.summary && (
                                       <p className="text-xs text-neutral-600 mb-2 line-clamp-2">{item.summary}</p>
                                     )}
                                     <div className="flex flex-wrap gap-1.5">
                                       {item.download_url && (
                                         <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-2 py-1 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors" onClick={(e) => e.stopPropagation()}>
                                           Download
                                         </a>
                                       )}
                                         {item.view_url && (
                                           <button 
                                             className="px-2 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors"
                                             onClick={(e) => {
                                               e.preventDefault();
                                               e.stopPropagation();
                                               handleCardClick(item, e);
                                             }}
                                           >
                                             View
                                           </button>
                                         )}
                                       {item.visit_url && (
                                         <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-2 py-1 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors" onClick={(e) => e.stopPropagation()}>
                                           Visit
                                         </a>
                                       )}
                                     </div>
                                   </div>
                                 </div>
                                 );
                               })}
                             </div>
                           </div>
                         );
                       }
                       
                       // If no CategoryItems but we have NavLinks, use NavLinks
                       if (categoryNavLinks.length === 0) {
                         // Only show category image if there are NO CategoryItems AND NO NavLinks
                         return (
                   <div className="flex items-center justify-center h-full">
                     <p className="text-neutral-500 text-center">
                               {window.translations?.progress?.no_items || 'No items found in this category.'}
                     </p>
                   </div>
                         );
                       }
                       console.log('CertificatesSection: Selected category object:', selectedCategoryObj);
                       console.log('CertificatesSection: Category animation_style from object:', selectedCategoryObj?.animation_style);
                       console.log('CertificatesSection: Category image_url from object:', selectedCategoryObj?.image_url);
                       console.log('CertificatesSection: All categories in this sub-nav:', categories);
                       // categoryStyle already declared above for CategoryItems, reuse it for NavLinks
                       console.log('CertificatesSection: Resolved category style:', categoryStyle);
                       
                       // Render based on category animation style
                       // Certificates style: Editorial grid collage with carousel
                       if (categoryStyle === 'certificates') {
                         return (
                           <div className="h-full overflow-y-auto">
                             <div className="mb-4 flex items-center gap-2">
                               <button 
                                 onClick={() => setSelectedCategory(null)}
                                 className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                               >
                                 <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                 </svg>
                                 Back to categories
                               </button>
                               <span className="text-sm text-neutral-400">•</span>
                               <span className="text-sm text-neutral-600">
                                 {activeNavLink?.title} → {selectedCategoryObj?.name || 'Category'}
                               </span>
                             </div>
                             {/* Use certificates carousel style - same as activeTab === 'certificates' */}
                             <div className="carousel-wrapper" style={{ height: 'calc(100% - 3rem)', paddingBottom: '0.5rem' }}>
                               <div className="carousel-container" style={{ width: '200%', height: '100%' }}>
                                 <div className="flex gap-4" style={{ width: '50%', height: '100%', background: '#fafafa', backgroundImage: 'none' }}>
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
                                     {categoryNavLinks.slice(0, 4).map((item, index) => {
                                       const gridConfig = getGridSpan(index, Math.min(4, categoryNavLinks.length));
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
                                           onClick={(e) => item.imageUrl && handleViewImage(item.imageUrl, e)}
                                         >
                                           {item.imageUrl ? (
                                             <img
                                               src={item.imageUrl}
                                               alt={item.title || `Item ${index + 1}`}
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
                                               }}
                                             />
                                           ) : (
                                             <div className="w-full h-full bg-neutral-100 flex items-center justify-center">
                                               <span className="text-neutral-400">No image</span>
                                             </div>
                                           )}
                                           <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/0 flex flex-col justify-between p-4 z-10">
                                             <div className="flex-1 flex items-start justify-end">
                                               <div className="flex flex-wrap gap-2">
                                                 {item.download_url && (
                                                   <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                     Download
                                                   </a>
                                                 )}
                                                 {item.view_url && (
                                                   <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                     View
                                                   </a>
                                                 )}
                                                 {item.visit_url && (
                                                   <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                     Visit
                                                   </a>
                                                 )}
                                                 {item.imageUrl && (
                                                   <>
                                                     <button
                                                       onClick={(e) => { handleViewImage(item.imageUrl, e); e.stopPropagation(); }}
                                                       className="px-3 py-1.5 bg-white/90 hover:bg-white text-neutral-900 text-xs rounded transition-colors backdrop-blur-sm shadow-lg flex items-center gap-1"
                                                       title="View full size"
                                                     >
                                                       <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                       </svg>
                                                       View
                                                     </button>
                                                   </>
                                                 )}
                                               </div>
                                             </div>
                                             <div className="text-white">
                                               <p className="text-sm font-semibold drop-shadow-lg">{item.title || `Item ${index + 1}`}</p>
                                               {item.slug && <p className="text-xs opacity-90 font-mono drop-shadow-md">{item.slug}</p>}
                                               {item.summary && <p className="text-xs opacity-85 mt-1 line-clamp-2 drop-shadow-md">{item.summary}</p>}
                                             </div>
                                           </div>
                                         </div>
                                       );
                                     })}
                                   </div>
                                   {categoryNavLinks.length > 4 && (
                                     <div className="flex gap-4 flex-shrink-0">
                                       {categoryNavLinks.slice(4).map((item, index) => {
                                         const actualIndex = 4 + index;
                                         const delay = actualIndex * 100;
                                         return (
                                           <div
                                             key={`extra-${item.id || actualIndex}`}
                                             className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${getGridSpan(actualIndex, categoryNavLinks.length).rotation}`}
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
                                             onClick={(e) => item.imageUrl && handleViewImage(item.imageUrl, e)}
                                           >
                                             {item.imageUrl ? (
                                               <img
                                                 src={item.imageUrl}
                                                 alt={item.title || `Item ${actualIndex + 1}`}
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
                                             ) : (
                                               <div className="w-full h-full bg-neutral-100 flex items-center justify-center">
                                                 <span className="text-neutral-400">No image</span>
                                               </div>
                                             )}
                                             <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/0 flex flex-col justify-between p-4 z-10">
                                               <div className="flex-1 flex items-start justify-end">
                                                 <div className="flex flex-wrap gap-2">
                                                   {item.download_url && (
                                                     <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                       Download
                                                     </a>
                                                   )}
                                                   {item.view_url && (
                                                     <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                       View
                                                     </a>
                                                   )}
                                                   {item.visit_url && (
                                                     <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                       Visit
                                                     </a>
                                                   )}
                                                   {item.imageUrl && (
                                                     <>
                                                       <button
                                                         onClick={(e) => { handleViewImage(item.imageUrl, e); e.stopPropagation(); }}
                                                         className="px-3 py-1.5 bg-white/90 hover:bg-white text-neutral-900 text-xs rounded transition-colors backdrop-blur-sm shadow-lg flex items-center gap-1"
                                                         title="View full size"
                                                       >
                                                         <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                         </svg>
                                                         View
                                                       </button>
                                                     </>
                                                   )}
                                                 </div>
                                               </div>
                                               <div className="text-white">
                                                 <p className="text-sm font-semibold drop-shadow-lg">{item.title || `Item ${actualIndex + 1}`}</p>
                                                 {item.slug && <p className="text-xs opacity-90 font-mono drop-shadow-md">{item.slug}</p>}
                                                 {item.summary && <p className="text-xs opacity-85 mt-1 line-clamp-2 drop-shadow-md">{item.summary}</p>}
                                               </div>
                                             </div>
                                           </div>
                                         );
                                       })}
                                     </div>
                                   )}
                                 </div>
                                 {/* Duplicated set for seamless loop */}
                                 <div className="flex gap-4" style={{ width: '50%', height: '100%', background: '#fafafa', backgroundImage: 'none' }}>
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
                                     {categoryNavLinks.slice(0, 4).map((item, index) => {
                                       const gridConfig = getGridSpan(index, Math.min(4, categoryNavLinks.length));
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
                                           onClick={(e) => item.imageUrl && handleViewImage(item.imageUrl, e)}
                                         >
                                           {item.imageUrl ? (
                                             <img
                                               src={item.imageUrl}
                                               alt={item.title || `Item ${index + 1}`}
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
                                           ) : (
                                             <div className="w-full h-full bg-neutral-100 flex items-center justify-center">
                                               <span className="text-neutral-400">No image</span>
                                             </div>
                                           )}
                                           <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/0 flex flex-col justify-between p-4 z-10">
                                             <div className="flex-1 flex items-start justify-end">
                                               <div className="flex flex-wrap gap-2">
                                                 {item.download_url && (
                                                   <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                     Download
                                                   </a>
                                                 )}
                                                 {item.view_url && (
                                                   <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                     View
                                                   </a>
                                                 )}
                                                 {item.visit_url && (
                                                   <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                     Visit
                                                   </a>
                                                 )}
                                                 {item.imageUrl && (
                                                   <>
                                                     <button
                                                       onClick={(e) => { handleViewImage(item.imageUrl, e); e.stopPropagation(); }}
                                                       className="px-3 py-1.5 bg-white/90 hover:bg-white text-neutral-900 text-xs rounded transition-colors backdrop-blur-sm shadow-lg flex items-center gap-1"
                                                       title="View full size"
                                                     >
                                                       <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                       </svg>
                                                       View
                                                     </button>
                                                   </>
                                                 )}
                                               </div>
                                             </div>
                                             <div className="text-white">
                                               <p className="text-sm font-semibold drop-shadow-lg">{item.title || `Item ${index + 1}`}</p>
                                               {item.slug && <p className="text-xs opacity-90 font-mono drop-shadow-md">{item.slug}</p>}
                                               {item.summary && <p className="text-xs opacity-85 mt-1 line-clamp-2 drop-shadow-md">{item.summary}</p>}
                                             </div>
                                           </div>
                                         </div>
                                       );
                                     })}
                                   </div>
                                   {categoryNavLinks.length > 4 && (
                                     <div className="flex gap-4 flex-shrink-0">
                                       {categoryNavLinks.slice(4).map((item, index) => {
                                         const actualIndex = 4 + index;
                                         const delay = actualIndex * 100;
                                         return (
                                           <div
                                             key={`duplicate-extra-${item.id || actualIndex}`}
                                             className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer ${getGridSpan(actualIndex, categoryNavLinks.length).rotation}`}
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
                                             onClick={(e) => item.imageUrl && handleViewImage(item.imageUrl, e)}
                                           >
                                             {item.imageUrl ? (
                                               <img
                                                 src={item.imageUrl}
                                                 alt={item.title || `Item ${actualIndex + 1}`}
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
                                             ) : (
                                               <div className="w-full h-full bg-neutral-100 flex items-center justify-center">
                                                 <span className="text-neutral-400">No image</span>
                                               </div>
                                             )}
                                             <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-black/0 flex flex-col justify-between p-4 z-10">
                                               <div className="flex-1 flex items-start justify-end">
                                                 <div className="flex flex-wrap gap-2">
                                                   {item.download_url && (
                                                     <a href={item.download_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                       Download
                                                     </a>
                                                   )}
                                                   {item.view_url && (
                                                     <a href={item.view_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                       View
                                                     </a>
                                                   )}
                                                   {item.visit_url && (
                                                     <a href={item.visit_url} target="_blank" rel="noopener noreferrer" className="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs rounded transition-colors backdrop-blur-sm" onClick={(e) => e.stopPropagation()}>
                                                       Visit
                                                     </a>
                                                   )}
                                                   {item.imageUrl && (
                                                     <>
                                                       <button
                                                         onClick={(e) => { handleViewImage(item.imageUrl, e); e.stopPropagation(); }}
                                                         className="px-3 py-1.5 bg-white/90 hover:bg-white text-neutral-900 text-xs rounded transition-colors backdrop-blur-sm shadow-lg flex items-center gap-1"
                                                         title="View full size"
                                                       >
                                                         <svg className="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                         </svg>
                                                         View
                                                       </button>
                                                     </>
                                                   )}
                                                 </div>
                                               </div>
                                               <div className="text-white">
                                                 <p className="text-sm font-semibold drop-shadow-lg">{item.title || `Item ${actualIndex + 1}`}</p>
                                                 {item.slug && <p className="text-xs opacity-90 font-mono drop-shadow-md">{item.slug}</p>}
                                                 {item.summary && <p className="text-xs opacity-85 mt-1 line-clamp-2 drop-shadow-md">{item.summary}</p>}
                                               </div>
                                             </div>
                                           </div>
                                         );
                                       })}
                                     </div>
                                   )}
                                 </div>
                               </div>
                             </div>
                           </div>
                         );
                       }
                       
                       // Courses style: Alternating cards
                       if (categoryStyle === 'courses') {
                         return (
                           <div className="h-full overflow-y-auto">
                             <div className="mb-4 flex items-center gap-2">
                               <button 
                                 onClick={() => setSelectedCategory(null)}
                                 className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                               >
                                 <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                   <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                 </svg>
                                 Back to categories
                               </button>
                               <span className="text-sm text-neutral-400">•</span>
                               <span className="text-sm text-neutral-600">
                                 {activeNavLink?.title} → {selectedCategoryObj?.name || 'Category'}
                               </span>
                             </div>
                             {/* Use courses alternating cards style */}
                             <div className="flex flex-col gap-6 h-full overflow-y-auto pr-2" style={{ paddingRight: '0.5rem' }}>
                               {categoryNavLinks.map((item, index) => {
                                 const delay = index * 100;
                                 const isRealItem = item.id && !String(item.id).startsWith('virtual');
                                 return (
                                   <div
                                     key={item.id || index}
                                     className={`cert-image-animate flex gap-6 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden ${index % 2 === 0 ? 'flex-row' : 'flex-row-reverse'}`}
                                     style={{
                                       animationDelay: isVisible ? `${delay}ms` : '0ms',
                                       minHeight: '200px'
                                     }}
                                   >
                                     <div className="flex-shrink-0" style={{ width: '333px' }}>
                                       {item.imageUrl ? (
                                         <img
                                           src={item.imageUrl}
                                           alt={item.title || `Item ${index + 1}`}
                                           className="w-full h-full object-cover"
                                           style={{ 
                                             display: 'block', 
                                             backgroundImage: 'none', 
                                             background: 'transparent',
                                             height: '100%',
                                             minHeight: '200px'
                                           }}
                                           loading="lazy"
                                           onError={(e) => {
                                             e.target.style.display = 'none';
                                           }}
                                         />
                                       ) : (
                                         <div className="w-full h-full bg-neutral-100 flex items-center justify-center" style={{ minHeight: '200px' }}>
                                           <span className="text-neutral-400">No image</span>
                                         </div>
                                       )}
                                     </div>
                                     <div className="flex-1 flex flex-col justify-center p-6 md:p-8 min-w-0 overflow-hidden">
                                       <h3 className="text-xl md:text-2xl font-bold text-neutral-900 mb-2 truncate break-words" style={{ wordBreak: 'break-word', overflowWrap: 'break-word' }}>
                                         {item.title || `Item ${index + 1}`}
                                       </h3>
                                       {item.url && (
                                         <a 
                                           href={item.url} 
                                           target="_blank" 
                                           rel="noopener noreferrer"
                                           className="inline-block mt-4 px-4 py-2 bg-neutral-900 text-white text-sm rounded-lg hover:bg-neutral-800 transition-colors duration-200 break-all truncate max-w-full overflow-hidden whitespace-nowrap"
                                           style={{ wordBreak: 'break-all' }}
                                         >
                                           {item.url}
                                         </a>
                                       )}
                                     </div>
                                   </div>
                                 );
                               })}
                             </div>
                           </div>
                         );
                       }
                       
                       // Rooms style: Two rows of horizontal scrollable cards with left-to-right animation
                       if (categoryStyle === 'rooms') {
                         // Split items into two rows
                         const midPoint = Math.ceil(categoryNavLinks.length / 2);
                         const firstRow = categoryNavLinks.slice(0, midPoint);
                         const secondRow = categoryNavLinks.slice(midPoint);
                         
                        return (
                          <div className="h-full overflow-hidden flex flex-col">
                            <div className="mb-4 flex items-center gap-2 flex-shrink-0">
                              <button 
                                onClick={() => setSelectedCategory(null)}
                                className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                              >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Back to categories
                              </button>
                              <span className="text-sm text-neutral-400">•</span>
                              <span className="text-sm text-neutral-600">
                                {activeNavLink?.title} → {selectedCategoryObj?.name || 'Category'}
                              </span>
                            </div>
                            <style>{`
                              @keyframes scrollLeft {
                                from {
                                  transform: translateX(0);
                                }
                                to {
                                  transform: translateX(-50%);
                                }
                              }
                              .rooms-scroll-container {
                                display: flex;
                                gap: 1.5rem;
                                width: max-content;
                                animation: scrollLeft 30s linear infinite;
                                will-change: transform;
                              }
                              .rooms-scroll-container:hover {
                                animation-play-state: paused;
                              }
                              .rooms-scroll-wrapper {
                                overflow-x: auto;
                                overflow-y: hidden;
                                position: relative;
                                width: 100%;
                                flex-shrink: 0;
                                isolation: isolate;
                                scrollbar-width: thin;
                              }
                              .rooms-scroll-wrapper::-webkit-scrollbar {
                                height: 8px;
                                position: absolute;
                                bottom: 0;
                              }
                              .rooms-scroll-wrapper::-webkit-scrollbar-track {
                                background: transparent;
                              }
                              .rooms-scroll-wrapper::-webkit-scrollbar-thumb {
                                background: rgba(0, 0, 0, 0.2);
                                border-radius: 4px;
                              }
                              .rooms-scroll-wrapper::-webkit-scrollbar-thumb:hover {
                                background: rgba(0, 0, 0, 0.3);
                              }
                              /* Hide scrollbar for first row, show only for last row */
                              .rooms-scroll-wrapper:not(:last-child) {
                                scrollbar-width: none;
                                -ms-overflow-style: none;
                              }
                              .rooms-scroll-wrapper:not(:last-child)::-webkit-scrollbar {
                                display: none;
                              }
                              .rooms-duplicate {
                                display: flex;
                                gap: 1.5rem;
                              }
                              .rooms-card-item {
                                flex-shrink: 0;
                                width: 400px;
                                margin: 0;
                                box-sizing: border-box;
                                display: flex;
                                flex-direction: column;
                                height: 100%;
                              }
                              .rooms-card-content {
                                flex: 1;
                                display: flex;
                                flex-direction: column;
                                overflow: hidden;
                              }
                            `}</style>
                            {/* Unified Two-Row Section with Synchronized Scrolling */}
                            <div className="flex flex-col h-full" style={{ gap: '0' }}>
                              {/* First Row */}
                              <div ref={row1NavRef} className="rooms-scroll-wrapper" style={{ height: '50%', marginBottom: '0', borderBottom: '2px solid transparent' }}>
                                <div className="rooms-scroll-container" style={{ height: '100%' }}>
                                  <div className="rooms-duplicate" style={{ height: '100%' }}>
                                    {[...firstRow, ...firstRow].map((item, index) => (
                                      <div 
                                        key={`room-row1-${item.id || index}-${index}`}
                                        className="rooms-card-item bg-white rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer group"
                                        style={{ minWidth: '400px', maxWidth: '400px', margin: 0, height: '100%' }}
                                        onClick={(e) => handleCardClick(item, e)}
                                     data-react-click-handled="true"
                                     onMouseDown={(e) => e.preventDefault()}
                                     role="button"
                                     tabIndex={0}
                                     onKeyDown={(e) => {
                                       if (e.key === 'Enter' || e.key === ' ') {
                                         e.preventDefault();
                                         handleCardClick(item, e);
                                       }
                                     }}
                                      >
                                        <div className="rooms-card-content" style={{ height: '100%' }}>
                                          {item.imageUrl ? (
                                            <div className="relative" style={{ height: '100%' }}>
                                              <img
                                                src={item.imageUrl}
                                                alt={item.title || `Item ${(index % firstRow.length) + 1}`}
                                                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                                style={{ objectFit: 'cover', height: '100%' }}
                                                loading="lazy"
                                                onError={(e) => {
                                                  e.target.style.display = 'none';
                                                }}
                                              />
                                              {/* All content overlapping image */}
                                              <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/70 to-black/40 p-4 z-10 flex flex-col justify-between">
                                                <div className="flex-1"></div>
                                                <div>
                                                  <h3 className="font-serif text-white leading-tight text-base mb-2 truncate break-words drop-shadow-lg" style={{ fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif', fontWeight: 'normal', lineHeight: '1.2', wordBreak: 'break-word', overflowWrap: 'break-word' }}>
                                                    {item.title || `Item ${(index % firstRow.length) + 1}`}
                                                  </h3>
                                                  {item.url && (
                                                    <a 
                                                      href={item.url} 
                                                      target="_blank" 
                                                      rel="noopener noreferrer"
                                                      className="text-xs text-white/90 font-mono mb-2 truncate break-all hover:text-white hover:underline inline-flex items-center gap-1 whitespace-nowrap drop-shadow-md"
                                                      style={{ wordBreak: 'break-all' }}
                                                      onClick={(e) => e.stopPropagation()}
                                                    >
                                                      <span className="truncate">{item.url}</span>
                                                    </a>
                                                  )}
                                                </div>
                                              </div>
                                            </div>
                                          ) : (
                                            <div className="flex-shrink-0 flex items-center justify-center bg-gradient-to-br from-purple-200 to-purple-300" style={{ height: '100%' }}>
                                              <svg className="w-32 h-32 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                              </svg>
                                            </div>
                                          )}
                                        </div>
                                      </div>
                                    ))}
                                  </div>
                                </div>
                              </div>
                              
                              {/* Second Row */}
                              <div ref={row2NavRef} className="rooms-scroll-wrapper" style={{ height: '50%', marginTop: '0', borderTop: 'none' }}>
                                <div className="rooms-scroll-container" style={{ animationDuration: '35s', height: '100%' }}>
                                  <div className="rooms-duplicate" style={{ height: '100%' }}>
                                    {[...secondRow, ...secondRow].map((item, index) => (
                                      <div 
                                        key={`room-row2-${item.id || (midPoint + index)}-${index}`}
                                        className="rooms-card-item bg-white rounded-xl shadow-md hover:shadow-lg transition-all cursor-pointer group"
                                        style={{ minWidth: '400px', maxWidth: '400px', margin: 0, height: '100%' }}
                                        onClick={(e) => handleCardClick(item, e)}
                                     data-react-click-handled="true"
                                     onMouseDown={(e) => e.preventDefault()}
                                     role="button"
                                     tabIndex={0}
                                     onKeyDown={(e) => {
                                       if (e.key === 'Enter' || e.key === ' ') {
                                         e.preventDefault();
                                         handleCardClick(item, e);
                                       }
                                     }}
                                      >
                                        <div className="rooms-card-content" style={{ height: '100%' }}>
                                          {item.imageUrl ? (
                                            <div className="relative" style={{ height: '100%' }}>
                                              <img
                                                src={item.imageUrl}
                                                alt={item.title || `Item ${midPoint + (index % secondRow.length) + 1}`}
                                                className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                                                style={{ objectFit: 'cover', height: '100%' }}
                                                loading="lazy"
                                                onError={(e) => {
                                                  e.target.style.display = 'none';
                                                }}
                                              />
                                              {/* All content overlapping image */}
                                              <div className="absolute inset-0 bg-gradient-to-t from-black/90 via-black/70 to-black/40 p-4 z-10 flex flex-col justify-between">
                                                <div className="flex-1"></div>
                                                <div>
                                                  <h3 className="font-serif text-white leading-tight text-base mb-2 truncate break-words drop-shadow-lg" style={{ fontFamily: 'Georgia, "Times New Roman", "Playfair Display", serif', fontWeight: 'normal', lineHeight: '1.2', wordBreak: 'break-word', overflowWrap: 'break-word' }}>
                                                    {item.title || `Item ${midPoint + (index % secondRow.length) + 1}`}
                                                  </h3>
                                                  {item.url && (
                                                    <a 
                                                      href={item.url} 
                                                      target="_blank" 
                                                      rel="noopener noreferrer"
                                                      className="text-xs text-white/90 font-mono mb-2 truncate break-all hover:text-white hover:underline inline-flex items-center gap-1 whitespace-nowrap drop-shadow-md"
                                                      style={{ wordBreak: 'break-all' }}
                                                      onClick={(e) => e.stopPropagation()}
                                                    >
                                                      <span className="truncate">{item.url}</span>
                                                    </a>
                                                  )}
                                                </div>
                                              </div>
                                            </div>
                                          ) : (
                                            <div className="flex-shrink-0 flex items-center justify-center bg-gradient-to-br from-purple-200 to-purple-300" style={{ height: '100%' }}>
                                              <svg className="w-32 h-32 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                              </svg>
                                            </div>
                                          )}
                                        </div>
                                      </div>
                                    ))}
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        );
                       }
                       
                       // Default: Simple grid layout
                       return (
                         <div className="h-full overflow-y-auto">
                           <div className="mb-4 flex items-center gap-2">
                             <button 
                               onClick={() => setSelectedCategory(null)}
                               className="text-sm text-neutral-600 hover:text-neutral-900 flex items-center gap-1"
                             >
                               <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                               </svg>
                               Back to categories
                             </button>
                             <span className="text-sm text-neutral-400">•</span>
                             <span className="text-sm text-neutral-600">
                               {activeNavLink?.title} → {selectedCategoryObj?.name || 'Category'}
                             </span>
                           </div>
                           <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                             {categoryNavLinks.map((item) => (
                               <div 
                                 key={item.id}
                                 className="bg-white rounded-lg overflow-hidden border border-neutral-200 hover:shadow-lg transition-all cursor-pointer"
                                 onClick={() => item.imageUrl && setViewingImage(item.imageUrl)}
                               >
                                 {item.imageUrl ? (
                                   <img 
                                     src={item.imageUrl} 
                                     alt={item.title} 
                                     className="w-full h-48 object-cover"
                                   />
                                 ) : (
                                   <div className="w-full h-48 bg-neutral-100 flex items-center justify-center">
                                     <span className="text-neutral-400">No image</span>
                                   </div>
                                 )}
                                 <div className="p-3">
                                   <h4 className="font-semibold text-neutral-900 text-sm mb-1">{item.title}</h4>
                                   {item.url && (
                                     <a 
                                       href={item.url} 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       className="text-xs text-blue-600 hover:underline"
                                       onClick={(e) => e.stopPropagation()}
                                     >
                                       View →                                     </a>
                                   )}
                                 </div>
                               </div>
                             ))}
                           </div>
                         </div>
                       );
                     }
                     
                     // Show empty state - categories are now displayed on the left side only
                     if (categories.length === 0) {
                       return (
                         <div className="flex items-center justify-center h-full">
                           <p className="text-neutral-500 text-center">
                             {window.translations?.progress?.no_categories || 'No categories assigned to this sub-navigation yet.'}
                           </p>
                         </div>
                       );
                     }
                     
                     // Display placeholder - categories should be selected from the left sidebar
                     return (
                       <div className="flex flex-col items-center justify-center h-full px-4 py-12">
                         <div className="relative mb-6">
                           {/* Animated icon */}
                           <div className="relative w-24 h-24 mx-auto">
                             <svg 
                               className="w-full h-full text-[#ffb400] animate-bounce-slow" 
                               fill="none" 
                               stroke="currentColor" 
                               viewBox="0 0 24 24"
                               style={{
                                 animation: 'float 3s ease-in-out infinite',
                               }}
                             >
                               <path 
                                 strokeLinecap="round" 
                                 strokeLinejoin="round" 
                                 strokeWidth={1.5} 
                                 d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" 
                               />
                             </svg>
                             {/* Decorative circles */}
                             <div className="absolute -top-2 -right-2 w-4 h-4 bg-yellow-400 rounded-full animate-ping opacity-75"></div>
                             <div className="absolute -bottom-2 -left-2 w-3 h-3 bg-yellow-300 rounded-full animate-pulse"></div>
                           </div>
                         </div>
                         <h3 className="text-2xl md:text-3xl font-bold text-gray-800 mb-3 bg-gradient-to-r from-[#ffb400] to-[#ff9500] bg-clip-text text-transparent">
                           {window.translations?.progress?.select_category_title || 'Ready to Explore?'}
                         </h3>
                         <p className="text-lg md:text-xl text-gray-600 text-center max-w-md mb-2 leading-relaxed">
                           {window.translations?.progress?.select_category || '👈 Pick a category from the left to discover amazing content!'}
                         </p>
                         <p className="text-sm text-gray-400 text-center max-w-sm">
                           {window.translations?.progress?.select_category_hint || 'Each category has something special waiting for you ✨'}
                         </p>
                         {/* Animated arrow pointing left */}
                         <div className="mt-6 flex items-center gap-2 text-[#ffb400] animate-pulse">
                           <svg 
                             className="w-6 h-6 animate-bounce-x" 
                             fill="none" 
                             stroke="currentColor" 
                             viewBox="0 0 24 24"
                             style={{
                               animation: 'slide-left 1.5s ease-in-out infinite',
                             }}
                           >
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                           </svg>
                           <span className="text-sm font-semibold">Categories</span>
                         </div>
                         <style>{`
                           @keyframes float {
                             0%, 100% { transform: translateY(0px); }
                             50% { transform: translateY(-10px); }
                           }
                           @keyframes slide-left {
                             0%, 100% { transform: translateX(0px); }
                             50% { transform: translateX(-8px); }
                           }
                           .animate-bounce-slow {
                             animation: float 3s ease-in-out infinite;
                           }
                           .animate-bounce-x {
                             animation: slide-left 1.5s ease-in-out infinite;
                           }
                         `}</style>
                       </div>
                     );
                   })()
                 ) : activeTab === 'certificates' ? (
              certificatesWithImages.length === 0 ? (
                <div className="flex items-center justify-center h-full">
                  <p className="text-neutral-500 text-center">
                    No certificates available. Add images to display them here.
                  </p>
                </div>
              ) : (
                <div className="carousel-wrapper" style={{ height: '100%', paddingBottom: '0.5rem' }}>
                  <div className="carousel-container md:carousel-container-animated" style={{ width: '100%', height: '100%' }}>
                    {/* First set of content */}
                    <div className="flex flex-col md:flex-row gap-3 sm:gap-4 w-full md:w-1/2" style={{ height: 'auto', background: '#fafafa', backgroundImage: 'none' }}>
                      {/* Main Grid - Responsive: Single column on mobile, 3-column grid on desktop */}
                      <div 
                        className="grid grid-cols-1 md:grid-cols-3 gap-3 flex-shrink-0 w-full"
                        style={{ 
                          gridTemplateRows: 'auto',
                          width: '100%',
                          height: 'auto',
                          maxHeight: 'none'
                        }}
                      >
                        {certificatesWithImages.slice(0, 4).map((item, index) => {
                          const gridConfig = getGridSpan(index, 4);
                          const delay = index * 100;
                          
                          return (
                            <div
                              key={item.id || index}
                              className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer bg-white ${gridConfig.rotation} ${
                                index === 0 ? 'md:col-span-2 md:row-span-1' : 
                                index === 1 ? 'md:col-span-1 md:row-span-1' : 
                                index === 2 ? 'md:col-span-1 md:row-span-1' : 
                                'md:col-span-2 md:row-span-1'
                              }`}
                              style={{
                                animationDelay: isVisible ? `${delay}ms` : '0ms',
                                width: '100%',
                                minHeight: '200px',
                                height: 'auto',
                                backgroundImage: 'none',
                                background: '#ffffff',
                                position: 'relative',
                                zIndex: 1,
                                isolation: 'isolate',
                                contain: 'layout style paint'
                              }}
                            >
                              {item.imageUrl ? (
                                <>
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
                                </>
                              ) : (
                                // Fallback content when no image
                                <div className="w-full h-full flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 text-center" style={{ minHeight: '200px' }}>
                                  <h3 className="text-lg sm:text-xl md:text-2xl font-bold text-neutral-900 mb-2">
                                    {item.title || `Certificate ${index + 1}`}
                                  </h3>
                                  {item.provider && (
                                    <p className="text-sm sm:text-base text-neutral-600 mb-2">
                                      {item.provider}
                                    </p>
                                  )}
                                  {item.credential_id && (
                                    <p className="text-xs sm:text-sm text-neutral-500">
                                      <span className="font-semibold">Credential ID:</span> {item.credential_id}
                                    </p>
                                  )}
                                </div>
                              )}
                              {/* Action buttons - appear on hover (only if image exists) */}
                              {item.imageUrl && (
                                <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
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
                              )}
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
                  <div className="flex flex-col md:flex-row gap-3 sm:gap-4 w-full md:w-1/2" style={{ height: 'auto', background: '#fafafa', backgroundImage: 'none' }}>
                    {/* Main Grid - Responsive: Single column on mobile, 3-column grid on desktop */}
                    <div 
                      className="grid grid-cols-1 md:grid-cols-3 gap-3 flex-shrink-0 w-full"
                      style={{ 
                        gridTemplateRows: 'auto',
                        width: '100%',
                        height: 'auto',
                        maxHeight: 'none'
                      }}
                    >
                      {certificatesWithImages.slice(0, 4).map((item, index) => {
                        const gridConfig = getGridSpan(index, 4);
                        const delay = index * 100;
                        
                        return (
                          <div
                            key={`duplicate-${item.id || index}`}
                            className={`cert-image-animate relative group overflow-hidden rounded-xl shadow-lg hover:shadow-xl transition-all duration-500 cursor-pointer bg-white ${gridConfig.rotation} ${
                              index === 0 ? 'md:col-span-2 md:row-span-1' : 
                              index === 1 ? 'md:col-span-1 md:row-span-1' : 
                              index === 2 ? 'md:col-span-1 md:row-span-1' : 
                              'md:col-span-2 md:row-span-1'
                            }`}
                            style={{
                              animationDelay: isVisible ? `${delay}ms` : '0ms',
                              width: '100%',
                              minHeight: '200px',
                              height: 'auto',
                              backgroundImage: 'none',
                              background: '#ffffff',
                              position: 'relative',
                              zIndex: 1,
                              isolation: 'isolate',
                              contain: 'layout style paint'
                            }}
                          >
                            {item.imageUrl ? (
                              <>
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
                                  }}
                                />
                                <div className="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-black/10 opacity-0 group-hover:opacity-100 transition-opacity duration-500" />
                              </>
                            ) : (
                              // Fallback content when no image
                              <div className="w-full h-full flex flex-col items-center justify-center p-4 sm:p-6 md:p-8 text-center" style={{ minHeight: '200px' }}>
                                <h3 className="text-lg sm:text-xl md:text-2xl font-bold text-neutral-900 mb-2">
                                  {item.title || `Certificate ${index + 1}`}
                                </h3>
                                {item.provider && (
                                  <p className="text-sm sm:text-base text-neutral-600 mb-2">
                                    {item.provider}
                                  </p>
                                )}
                                {item.credential_id && (
                                  <p className="text-xs sm:text-sm text-neutral-500">
                                    <span className="font-semibold">Credential ID:</span> {item.credential_id}
                                  </p>
                                )}
                              </div>
                            )}
                            {/* Action buttons - appear on hover (only if image exists) */}
                            {item.imageUrl && (
                              <div className="absolute bottom-4 right-4 flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10">
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
                            )}
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
        </div>
      </div>
    </section>
  );
};

export default CertificatesSection;

