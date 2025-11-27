<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortfolioService;

class LandingController extends Controller
{
    protected $portfolioService;

    public function __construct(PortfolioService $portfolioService)
    {
        $this->portfolioService = $portfolioService;
    }

    /**
     * Display the landing page
     */
    public function index()
    {
        // Get a sample user's portfolio for the template preview
        $sampleUser = $this->portfolioService->getSampleUser();

        // If no sample user, show basic landing page
        if (!$sampleUser) {
            return view('landing', compact('sampleUser'));
        }

        // Load portfolio data for preview
        $profile = $this->portfolioService->getProfile($sampleUser->id);
        $heroSection = $this->portfolioService->getHeroSection($sampleUser->id);
        $engagementSection = $this->portfolioService->getEngagementSection($sampleUser->id);
        $engagementVideo = $this->portfolioService->getEngagementVideo($engagementSection);
        
        $images = $this->portfolioService->getProfileImages($sampleUser->id, $heroSection, $profile);
        $heroProfileImages = $images['heroProfileImages'];
        $profileImages = $images['profileImages'];
        $finalProfileImages = $images['finalProfileImages'];
        
        $categoriesData = $this->portfolioService->getCategories($sampleUser->id);
        $categories = $categoriesData['categories'];
        $services = $categoriesData['services'];
        
        $homePageSections = $this->portfolioService->getHomePageSections($sampleUser->id);
        $progressItems = $this->portfolioService->getProgressItems($sampleUser->id);
        $certificatesData = $this->portfolioService->getCertificates($sampleUser->id);
        $coursesData = $this->portfolioService->getCourses($sampleUser->id);
        $blogs = $this->portfolioService->getBlogs($sampleUser->id);
        $roomsData = $this->portfolioService->getRooms($sampleUser->id);
        
        // Empty arrays for now as they weren't implemented in the original controller
        $badgesData = [];
        $gamesData = [];
        $simulationsData = [];
        $programsData = [];
        
        return view('landing', compact(
            'sampleUser',
            'profile',
            'heroSection',
            'engagementSection',
            'engagementVideo',
            'heroProfileImages',
            'profileImages',
            'finalProfileImages',
            'categories',
            'services',
            'homePageSections',
            'progressItems',
            'certificatesData',
            'coursesData',
            'roomsData',
            'badgesData',
            'gamesData',
            'simulationsData',
            'programsData',
            'blogs'
        ));
    }
}

