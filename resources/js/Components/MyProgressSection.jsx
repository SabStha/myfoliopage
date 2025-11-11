import React, { useRef, useState, useEffect } from 'react';
import ProgressTable from './ProgressTable.jsx';
import CertificatesSection from './CertificatesSection.jsx';
import TryHackMeSection from './TryHackMeSection.jsx';

// Custom hook for one-time animation on scroll into view
const useInViewOnce = (ref) => {
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            setIsVisible(true);
            // Unobserve after first trigger
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

const MyProgressSection = ({ className = '', items = null, certificates = [], courses = [], rooms = [], badges = [], games = [], simulations = [], programs = [] }) => {
  // Get progress items from window if available (from database via NavItems)
  // Only use database data - no fallback to static data
  const progressItemsFromWindow = typeof window !== 'undefined' && window.progressItemsData ? window.progressItemsData : null;
  const finalItems = items || progressItemsFromWindow || [];
  const sectionRef = useRef(null);
  const isVisible = useInViewOnce(sectionRef);

  // Get certificates data from global variable or props
  const [certificatesData, setCertificatesData] = useState(certificates);
  const [coursesData, setCoursesData] = useState(courses);
  const [roomsData, setRoomsData] = useState(rooms);
  const [badgesData, setBadgesData] = useState(badges);
  const [gamesData, setGamesData] = useState(games);
  const [simulationsData, setSimulationsData] = useState(simulations);
  const [programsData, setProgramsData] = useState(programs);
  const [homePageSections, setHomePageSections] = useState([]);

  useEffect(() => {
    // Try to get certificates from global window variable if not provided as props
    if (typeof window !== 'undefined' && window.certificatesData) {
      console.log('CertificatesSection: Found certificatesData in window:', window.certificatesData);
      setCertificatesData(window.certificatesData);
    } else if (certificates && certificates.length > 0) {
      console.log('CertificatesSection: Using certificates from props:', certificates);
      setCertificatesData(certificates);
    } else {
      console.log('CertificatesSection: No certificates data found');
    }

    // Try to get courses from global window variable if not provided as props
    if (typeof window !== 'undefined' && window.coursesData) {
      setCoursesData(window.coursesData);
    } else if (courses && courses.length > 0) {
      setCoursesData(courses);
    }

    // Try to get rooms from global window variable if not provided as props
    if (typeof window !== 'undefined' && window.roomsData) {
      setRoomsData(window.roomsData);
    } else if (rooms && rooms.length > 0) {
      setRoomsData(rooms);
    }

    // Try to get badges, games, simulations, programs from global window variable
    if (typeof window !== 'undefined' && window.badgesData) {
      setBadgesData(window.badgesData);
    } else if (badges && badges.length > 0) {
      setBadgesData(badges);
    }

    if (typeof window !== 'undefined' && window.gamesData) {
      setGamesData(window.gamesData);
    } else if (games && games.length > 0) {
      setGamesData(games);
    }

    if (typeof window !== 'undefined' && window.simulationsData) {
      setSimulationsData(window.simulationsData);
    } else if (simulations && simulations.length > 0) {
      setSimulationsData(simulations);
    }

    if (typeof window !== 'undefined' && window.programsData) {
      setProgramsData(window.programsData);
    } else if (programs && programs.length > 0) {
      setProgramsData(programs);
    }

    // Get home page sections from window
    if (typeof window !== 'undefined' && window.homePageSections) {
      console.log('MyProgressSection: Found homePageSections in window:', window.homePageSections);
      console.log('MyProgressSection: Number of sections:', window.homePageSections.length);
      
      // Debug: Log detailed category data for each section
      window.homePageSections.forEach((section, idx) => {
        console.log(`MyProgressSection: Section ${idx} nav_links count:`, section.nav_links?.length || 0);
        if (section.nav_links && section.nav_links.length > 0) {
          section.nav_links.forEach((link, linkIdx) => {
            console.log(`MyProgressSection: Section ${idx}, NavLink ${linkIdx} (ID: ${link.id}, Title: ${link.title}):`, {
              categories_count: link.categories?.length || 0,
              categories: link.categories?.map(c => ({
                id: c.id,
                name: c.name,
                slug: c.slug,
                animation_style: c.animation_style,
                image_url: c.image_url,
                image_path: c.image_path
              })) || []
            });
          });
        }
      });
      
      setHomePageSections(window.homePageSections);
    } else {
      console.log('MyProgressSection: No homePageSections found in window');
      console.log('MyProgressSection: window object:', typeof window !== 'undefined' ? 'exists' : 'undefined');
      console.log('MyProgressSection: window.homePageSections:', typeof window !== 'undefined' ? window.homePageSections : 'N/A');
    }
  }, [certificates, courses, rooms, badges, games, simulations, programs]);

  return (
    <div
      ref={sectionRef}
      className={`w-full ${className}`}
      aria-label="My Ongoing Progress"
    >
      <ProgressTable items={finalItems} isVisible={isVisible} className={className} />
      
      {/* Render sections dynamically from database configuration */}
      {homePageSections && homePageSections.length > 0 ? (
        homePageSections.map((sectionConfig, index) => {
          console.log(`Rendering section ${index}:`, sectionConfig);
          const navLabel = (sectionConfig.nav_item_label || '').toLowerCase();
          const isTryHackMe = navLabel.includes('tryhackme') || navLabel.includes('thm');
          
          // Use NavLinks as the data source - no need to filter old arrays
          // Get NavLinks (subsections) for this section - use from database, not hardcoded
          const navLinks = sectionConfig.nav_links || [];
          
          // Debug: Log what categories data is being passed
          console.log(`MyProgressSection: Section ${index} - navLinks count:`, navLinks.length);
          navLinks.forEach((link, linkIdx) => {
            if (link.categories && link.categories.length > 0) {
              console.log(`MyProgressSection: NavLink ${linkIdx} (${link.title}) has ${link.categories.length} categories:`, 
                link.categories.map(c => ({id: c.id, name: c.name, animation_style: c.animation_style, image_url: c.image_url})));
            } else {
              console.log(`MyProgressSection: NavLink ${linkIdx} (${link.title}) has NO categories`);
            }
          });
          
          // Determine which component to render
          // TryHackMeSection for TryHackMe, CertificatesSection for everything else (it supports multiple tabs)
          const SectionComponent = isTryHackMe ? TryHackMeSection : CertificatesSection;
          
          // Get subsection configurations for this section
          const subsectionConfigs = sectionConfig.subsection_configurations || {};
          
          // Ensure title and subtitle are strings (defensive check)
          const brandTitle = typeof sectionConfig.title === 'string' 
            ? sectionConfig.title 
            : (sectionConfig.title?.en || sectionConfig.title?.ja || sectionConfig.nav_item_label || '');
          const subtitle = typeof sectionConfig.subtitle === 'string' 
            ? sectionConfig.subtitle 
            : (sectionConfig.subtitle?.en || sectionConfig.subtitle?.ja || '');
          
          return (
            <div key={sectionConfig.id || index} className="mt-16 md:mt-20 lg:mt-24">
              <SectionComponent 
                certificates={[]} // Deprecated - data now comes from navLinks
                courses={[]} // Deprecated - data now comes from navLinks
                rooms={[]} // Deprecated - data now comes from navLinks
                badges={[]} // Deprecated - data now comes from navLinks
                games={[]} // Deprecated - data now comes from navLinks
                simulations={[]} // Deprecated - data now comes from navLinks
                programs={[]} // Deprecated - data now comes from navLinks
                brandTitle={brandTitle.toUpperCase()}
                subtitle={subtitle}
                animationStyle={sectionConfig.animation_style || 'list_alternating_cards'}
                textAlignment={sectionConfig.text_alignment || 'left'}
                subsectionConfigurations={subsectionConfigs}
                navLinks={navLinks} // Pass NavLinks to render dynamic tabs - THIS IS THE REAL DATA
              />
            </div>
          );
        })
      ) : (
        // Fallback to original hardcoded sections if no database configuration
        certificatesData && Array.isArray(certificatesData) && certificatesData.length > 0 && (
          <>
            <div className="mt-16 md:mt-20 lg:mt-24">
              <CertificatesSection 
                certificates={certificatesData} 
                courses={coursesData || []} 
                rooms={roomsData || []}
                badges={badgesData || []}
                games={gamesData || []}
                simulations={simulationsData || []}
                programs={programsData || []}
              />
            </div>
            <TryHackMeSection 
              certificates={certificatesData} 
              courses={coursesData || []} 
              rooms={roomsData || []}
              badges={badgesData || []}
              games={gamesData || []}
              simulations={simulationsData || []}
              programs={programsData || []}
            />
          </>
        )
      )}
    </div>
  );
};

export default MyProgressSection;
