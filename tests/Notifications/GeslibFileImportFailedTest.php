<?php

namespace NumaxLab\Lunar\Geslib\Tests\Notifications;

use Illuminate\Support\Facades\Config;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Notifications\GeslibFileImportFailed;
use NumaxLab\Lunar\Geslib\Tests\TestCase; // Ensure this is your package's TestCase

class GeslibFileImportFailedTest extends TestCase
{
    /** @test */
    public function mail_content_is_correct()
    {
        $file = new GeslibInterFile([ // Not saving to DB, just using for data
            'id' => 123,
            'name' => 'test_import_file.txt',
        ]);
        $errorMessage = 'Failed due to critical error XYZ.';

        $notification = new GeslibFileImportFailed($file, $errorMessage);

        // Mock a notifiable entity
        $notifiable = new \Illuminate\Notifications\AnonymousNotifiable;
        $notifiable->route('mail', 'test@example.com');

        $mailData = $notification->toMail($notifiable);
        $renderedMail = $mailData->render(); // Render the Mailable to HTML/Text

        $this->assertEquals('Geslib File Import Failed: test_import_file.txt', $mailData->subject);
        $this->assertStringContainsString('Geslib File Import Failure', $renderedMail);
        $this->assertStringContainsString('Filename: test_import_file.txt', $renderedMail);
        $this->assertStringContainsString('Error Message: ' . $errorMessage, $renderedMail);
        $this->assertStringContainsString('View Import Logs', $renderedMail); // Action button

        // Test link generation (optional, depends on test environment setup for routes)
        Config::set('lunar.admin.route_prefix', 'adminhub'); // Ensure route prefix is set for test
        $expectedLink = route('adminhub.geslib.file-import-log', ['q_filename' => $file->name]);
        $this->assertStringContainsString(htmlspecialchars($expectedLink), $renderedMail);
    }

    /** @test */
    public function mail_content_handles_missing_route()
    {
        $file = new GeslibInterFile(['id' => 456, 'name' => 'another_file.txt']);
        $errorMessage = 'Another error.';

        $notification = new GeslibFileImportFailed($file, $errorMessage);
        $notifiable = new \Illuminate\Notifications\AnonymousNotifiable;
        $notifiable->route('mail', 'test@example.com');

        // Simulate route not being available by not setting the prefix or a specific route name
        // This is tricky to fully simulate without manipulating router,
        // but the notification has fallback logic.
        // For now, assume the fallback text is present if action is not.
        // A more robust way would be to mock Route::has or similar if possible.

        Config::set('lunar.admin.route_prefix', null); // Simulate missing config for route generation

        $mailData = $notification->toMail($notifiable);
        $renderedMail = $mailData->render();

        $this->assertStringNotContainsString('View Import Logs', $mailData->render()); // Action button should not be there or be different
        $this->assertStringContainsString('Please check the Geslib File Import Logs in your admin panel', $renderedMail);
    }
}
