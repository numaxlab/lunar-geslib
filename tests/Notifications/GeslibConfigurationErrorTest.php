<?php

namespace NumaxLab\Lunar\Geslib\Tests\Notifications;

use Illuminate\Support\Facades\Config;
use NumaxLab\Lunar\Geslib\Notifications\GeslibConfigurationError;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class GeslibConfigurationErrorTest extends TestCase
{
    /** @test */
    public function mail_content_is_correct()
    {
        $configKey = 'lunar.geslib.inter_files_disk';
        $details = 'The specified disk is not configured in filesystems.php.';

        $notification = new GeslibConfigurationError($configKey, $details);

        $notifiable = new \Illuminate\Notifications\AnonymousNotifiable;
        $notifiable->route('mail', 'test@example.com');

        $mailData = $notification->toMail($notifiable);
        $renderedMail = $mailData->render();

        $this->assertEquals('Geslib Configuration Error Detected', $mailData->subject);
        $this->assertStringContainsString('Geslib Configuration Error', $renderedMail);
        $this->assertStringContainsString('Configuration Key: ' . $configKey, $renderedMail);
        $this->assertStringContainsString('Details: ' . $details, $renderedMail);
        $this->assertStringContainsString('Go to Geslib Dashboard', $renderedMail); // Action button

        Config::set('lunar.admin.route_prefix', 'adminhub');
        $expectedLink = route('adminhub.geslib.dashboard');
        $this->assertStringContainsString(htmlspecialchars($expectedLink), $renderedMail);
    }

    /** @test */
    public function mail_content_handles_missing_route_for_dashboard_link()
    {
        $configKey = 'lunar.geslib.api_key';
        $details = 'API Key is missing.';
        $notification = new GeslibConfigurationError($configKey, $details);
        $notifiable = new \Illuminate\Notifications\AnonymousNotifiable;
        $notifiable->route('mail', 'test@example.com');

        Config::set('lunar.admin.route_prefix', null); // Simulate missing config for route

        $mailData = $notification->toMail($notifiable);
        $renderedMail = $mailData->render();

        $this->assertStringNotContainsString('Go to Geslib Dashboard', $mailData->render());
        $this->assertStringContainsString('Please check the Geslib Dashboard in your admin panel', $renderedMail);
    }
}
