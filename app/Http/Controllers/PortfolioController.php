<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PortfolioService;

class PortfolioController extends Controller
{
    protected $portfolioService;

    public function __construct(PortfolioService $portfolioService)
    {
        $this->portfolioService = $portfolioService;
    }

    /**
     * Display a user's public portfolio
     */
    public function show($username)
    {
        // Find user by username or slug
        $user = $this->portfolioService->getUser($username);

        // Load all portfolio data for this user
        $profile = $this->portfolioService->getProfile($user->id);
        $heroSection = $this->portfolioService->getHeroSection($user->id);
        $engagementSection = $this->portfolioService->getEngagementSection($user->id);
        $engagementVideo = $this->portfolioService->getEngagementVideo($engagementSection);
        
        $images = $this->portfolioService->getProfileImages($user->id, $heroSection, $profile);
        $heroProfileImages = $images['heroProfileImages'];
        $profileImages = $images['profileImages'];
        $finalProfileImages = $images['finalProfileImages'];
        
        $categoriesData = $this->portfolioService->getCategories($user->id);
        $categories = $categoriesData['categories'];
        $services = $categoriesData['services'];
        
        $homePageSections = $this->portfolioService->getHomePageSections($user->id);
        $progressItems = $this->portfolioService->getProgressItems($user->id);
        $certificatesData = $this->portfolioService->getCertificates($user->id);
        $coursesData = $this->portfolioService->getCourses($user->id);
        $blogs = $this->portfolioService->getBlogs($user->id);
        $roomsData = $this->portfolioService->getRooms($user->id);
        
        // Empty arrays for now
        $badgesData = [];
        $gamesData = [];
        $simulationsData = [];
        $programsData = [];
        
        return view('home', compact(
            'user',
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

