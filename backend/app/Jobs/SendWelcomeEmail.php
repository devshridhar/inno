<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60; // 1 minute
    public int $tries = 3;

    public function __construct(
        private User $user
    ) {}

    public function handle(): void
    {
        Log::info('Sending welcome email', [
            'user_id' => $this->user->id,
            'email' => $this->user->email
        ]);

        try {
            // In a real application, you'd create a proper Mailable class
            // For now, we'll simulate sending a welcome email

            $this->sendWelcomeNotification();

            // Update user metadata to track welcome email sent
            $this->updateUserMetadata();

            Log::info('Welcome email sent successfully', [
                'user_id' => $this->user->id,
                'email' => $this->user->email
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $this->user->id,
                'email' => $this->user->email,
                'error' => $e->getMessage()
            ]);

            throw $e; // Re-throw to trigger retry mechanism
        }
    }

    private function sendWelcomeNotification(): void
    {
        // In production, you would use Laravel's Mail facade:
        // Mail::to($this->user->email)->send(new WelcomeMail($this->user));

        // For development/demo purposes, we'll simulate the email
        $emailContent = $this->generateWelcomeEmailContent();

        // Log the email content (in production, this would actually send)
        Log::info('Welcome email content generated', [
            'user_id' => $this->user->id,
            'subject' => $emailContent['subject'],
            'preview' => substr($emailContent['body'], 0, 100) . '...'
        ]);

        // Simulate email sending delay
        sleep(1);

        // In production, uncomment this and create the Mailable:
        // Mail::to($this->user->email)->send(new WelcomeMail($this->user));
    }

    private function generateWelcomeEmailContent(): array
    {
        $subject = "Welcome to News Aggregator, {$this->user->first_name}!";

        $body = "
        Dear {$this->user->full_name},

        Welcome to News Aggregator! We're excited to have you join our community of news enthusiasts.

        Here's what you can do with your new account:

        📰 Browse articles from top news sources
        🔍 Search and filter news by your interests  
        📚 Bookmark articles to read later
        ⚙️ Customize your news feed preferences
        📱 Access your personalized news feed

        Getting Started:
        1. Visit your profile to set up your news preferences
        2. Choose your favorite news sources and categories
        3. Start exploring the latest news tailored to your interests

        If you have any questions, feel free to reach out to our support team.

        Happy reading!
        The News Aggregator Team

        ---
        This email was sent to {$this->user->email}
        If you didn't create this account, please ignore this email.
        ";

        return [
            'subject' => $subject,
            'body' => $body
        ];
    }

    private function updateUserMetadata(): void
    {
        $preferences = $this->user->preferences ?? [];
        $preferences['welcome_email_sent_at'] = now()->toISOString();
        $preferences['onboarding_completed'] = false;

        $this->user->update(['preferences' => $preferences]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Welcome email job failed', [
            'user_id' => $this->user->id,
            'email' => $this->user->email,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        // Optionally, you could create a failed notification or retry mechanism
        // For example, add to a failed emails table for manual review
    }
}