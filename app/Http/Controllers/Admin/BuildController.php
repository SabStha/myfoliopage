<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class BuildController extends Controller
{
    public function build()
    {
        try {
            // Get the base path
            $basePath = base_path();
            
            // Determine the command based on OS
            $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
            
            if ($isWindows) {
                // For Windows, use cmd /c
                $process = new Process(['cmd', '/c', 'npm', 'run', 'build'], $basePath);
            } else {
                // For Unix-like systems
                $process = Process::fromShellCommandline('npm run build', $basePath);
            }
            
            $process->setTimeout(120); // 2 minutes timeout
            $process->setEnv([
                'PATH' => getenv('PATH'),
                'NODE_ENV' => 'production',
            ]);
            
            $process->run();
            
            if ($process->isSuccessful()) {
                $output = $process->getOutput();
                $outputSnippet = strlen($output) > 200 ? '...' . substr($output, -200) : $output;
                return redirect()->back()->with('status', '✅ Assets built successfully! Refresh your browser to see changes.');
            } else {
                $errorOutput = $process->getErrorOutput();
                $output = $process->getOutput();
                $fullError = $output . "\n" . $errorOutput;
                Log::error('Build failed', ['error' => $fullError]);
                
                $errorSnippet = strlen($fullError) > 300 ? '...' . substr($fullError, -300) : $fullError;
                return redirect()->back()->withErrors(['build' => 'Build failed. Check logs or try running "npm run build" manually. Error: ' . $errorSnippet]);
            }
        } catch (\Exception $e) {
            Log::error('Build exception', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return redirect()->back()->withErrors(['build' => 'Build error: ' . $e->getMessage()]);
        }
    }
}

